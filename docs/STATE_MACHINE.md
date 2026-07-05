# STATE_MACHINE.md — NURISK

**Versi Dokumen:** 1.0.0
**Tanggal:** 2026-06-16
**Proyek:** NURISK — Platform Kebencanaan NU Jawa Tengah
**Stack:** Laravel Monolith · MySQL InnoDB · Blade SSR · Bootstrap 5.3

---

## Prinsip Umum State Machine

Seluruh state machine pada NURISK mengikuti prinsip berikut:

1. **Persistensi State:** Setiap state tersimpan di kolom `status_*` atau `id_status` pada tabel terkait di database. State tidak boleh hanya ada di memori atau session.

2. **Pencatatan Transisi:** Setiap transisi state wajib dicatat ke tabel audit yang relevan:
   - Transisi insiden → `riwayat_status_insiden`
   - Transisi operasional → `operasi_jurnal`

3. **Penolakan di Level Policy/Service:** Validasi transisi yang tidak valid dilakukan di layer `Policy` atau `Service` Laravel, bukan hanya di UI/view. UI hanya menyembunyikan tombol yang tidak relevan, tetapi server-side tetap memvalidasi.

4. **Lock Rule Global:** Data yang sudah berada pada state final/selesai/ditandatangani bersifat **immutable**. Perubahan apapun pada data terkunci harus ditolak di level:
   - MySQL Trigger (lapisan pertama)
   - Laravel Policy (lapisan kedua)
   - Form/Controller validation (lapisan ketiga)

5. **Trigger sebagai Safety Net:** MySQL Trigger berfungsi sebagai jaring pengaman terakhir. Jika Logic aplikasi melewatkan validasi, trigger akan menolak operasi dengan `SIGNAL SQLSTATE`.

6. **Konsistensi Enum:** Nilai enum dalam kode PHP harus identik dengan nilai yang didefinisikan di kolom MySQL (case-sensitive).

---

## 1. STATE MACHINE: INSIDEN (`operasi_insiden`)

### Kolom State

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `status_insiden` | `enum('draft','terverifikasi','respon','pemulihan','selesai','dibatalkan')` | State utama insiden |
| `status_operasi` | `enum('monitoring','siaga','tanggap_darurat','pemulihan','selesai')` | Fase operasional lapangan |
| `is_locked` | `tinyint(1)` default `0` | Flag immutability, `1` = terkunci |

### Diagram State

```
                         ┌─────────────────────────────────────────────────────────┐
                         │               DIBATALKAN (terminal)                     │
                         │        hanya oleh pwnu, dengan alasan                   │
                         └────────────────────────────────────────────────────────-┘
                              ↑              ↑              ↑            ↑
  ┌──────────┐  verifikasi  ┌──────────────┐  pleno/     ┌──────────┐  kondisi   ┌──────────┐  klaster  ┌──────────┐
  │  DRAFT   │ ───────────► │ TERVERIFIKASI│  komandan  ►│  RESPON  │  terkendali►│PEMULIHAN │  selesai ►│ SELESAI  │
  │          │              │              │ ────────────►│          │ ──────────►│          │ ─────────►│ (LOCKED) │
  └──────────┘              └──────────────┘              └──────────┘            └──────────┘           └──────────┘
       │                                                                                                       │
       │                                                                                                       │
       └── FORBIDDEN: DRAFT → RESPON                                                    is_locked = 1 ─────────┘
       └── FORBIDDEN: DRAFT → SELESAI                                         tr_lock_incident_data aktif
```

### Definisi State

| State | Deskripsi | `status_operasi` Relevan |
|-------|-----------|--------------------------|
| `draft` | Insiden dilaporkan, belum diverifikasi | `monitoring` |
| `terverifikasi` | Insiden telah divalidasi oleh pcnu/pwnu | `siaga` |
| `respon` | Operasi tanggap darurat aktif | `tanggap_darurat` |
| `pemulihan` | Fase darurat terkendali, masuk pemulihan | `pemulihan` |
| `selesai` | Semua operasi klaster selesai, insiden ditutup | `selesai` |
| `dibatalkan` | Insiden dibatalkan (terminal, tidak dapat dilanjutkan) | — |

### Aturan Transisi

| Dari | Ke | Aktor | Prasyarat |
|------|----|-------|-----------|
| `draft` | `terverifikasi` | `pcnu`, `pwnu` | Data `laporan_kejadian` terkait terisi dan valid |
| `terverifikasi` | `respon` | `pcnu`, `pwnu`, komandan_insiden | Ada `operasi_aktivasi` aktif ATAU keputusan pleno |
| `respon` | `pemulihan` | `pwnu`, komandan_insiden | Kondisi darurat terkendali, diputuskan via pleno |
| `pemulihan` | `selesai` | `pwnu` | Semua `operasi_klaster` terkait berstatus selesai |
| `(semua)` | `dibatalkan` | `pwnu` | Kolom `alasan` pada `riwayat_status_insiden` wajib diisi |

### Transisi Terlarang (Forbidden Transitions)

```
SELESAI    → (apapun)         ✗  is_locked = 1, trigger SIGNAL error
DIBATALKAN → (apapun)         ✗  terminal state
DRAFT      → RESPON           ✗  harus melewati TERVERIFIKASI
DRAFT      → SELESAI          ✗  tidak logis, diblokir di Policy
DRAFT      → PEMULIHAN        ✗  tidak logis, diblokir di Policy
```

### Lock Rule

```sql
-- Trigger: tr_lock_incident_data
-- Dieksekusi BEFORE UPDATE pada operasi_insiden
-- Jika is_locked = 1, semua UPDATE ditolak
IF OLD.is_locked = 1 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Insiden telah dikunci. Perubahan tidak diizinkan.';
END IF;

-- is_locked diset ke 1 saat status_insiden = 'selesai'
-- (dieksekusi via AFTER UPDATE trigger atau di Service layer)
IF NEW.status_insiden = 'selesai' THEN
    SET NEW.is_locked = 1;
END IF;
```

### Pencatatan Histori Transisi

Setiap transisi `status_insiden` dicatat ke tabel `riwayat_status_insiden`:

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_insiden` | `bigint unsigned` | FK ke `operasi_insiden.id` |
| `status_sebelumnya` | `enum(...)` | Nilai state sebelum transisi |
| `status_terbaru` | `enum(...)` | Nilai state setelah transisi |
| `id_pengguna` | `bigint unsigned` | FK ke `auth_users.id`, aktor transisi |
| `alasan` | `text` | Wajib diisi saat transisi ke `dibatalkan` |
| `dibuat_pada` | `timestamp` | Waktu transisi terjadi |

**Contoh implementasi di Service Layer:**

```php
// InsidenService::transisi()
public function transisi(OperasiInsiden $insiden, string $statusBaru, ?string $alasan = null): void
{
    $statusLama = $insiden->status_insiden;

    // Validasi transisi
    $this->validasiTransisi($statusLama, $statusBaru, $alasan);

    DB::transaction(function () use ($insiden, $statusLama, $statusBaru, $alasan) {
        $insiden->update([
            'status_insiden' => $statusBaru,
            'is_locked'      => $statusBaru === 'selesai' ? 1 : $insiden->is_locked,
        ]);

        RiwayatStatusInsiden::create([
            'id_insiden'       => $insiden->id,
            'status_sebelumnya'=> $statusLama,
            'status_terbaru'   => $statusBaru,
            'id_pengguna'      => auth()->id(),
            'alasan'           => $alasan,
            'dibuat_pada'      => now(),
        ]);
    });
}
```

---

## 2. STATE MACHINE: SITREP (`operasi_sitrep`)

### Kolom State

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `status_sitrep` | `enum('draft','ditinjau','final')` | State utama sitrep |
| `waktu_difinalisasi` | `timestamp` NULL | Diisi saat state → `final` |
| `id_penfinalisasi` | `bigint unsigned` NULL | FK `auth_users.id`, diisi saat `final` |
| `hash_snapshot` | `varchar(64)` NULL | SHA-256 dari JSON snapshot, diisi saat `final` |
| `snapshot_dampak` | `json` NULL | Auto-populated dari `assessment_dampak_manusia` |
| `snapshot_logistik` | `json` NULL | Snapshot data logistik |
| `snapshot_operasi` | `json` NULL | Snapshot data operasi |
| `snapshot_aktivitas` | `json` NULL | Snapshot aktivitas lapangan |

### Diagram State

```
  ┌──────────┐  submit    ┌──────────┐  finalisasi  ┌──────────────┐
  │  DRAFT   │ ─────────► │ DITINJAU │ ────────────► │    FINAL     │
  │          │            │          │               │  (immutable) │
  └──────────┘            └──────────┘               └──────────────┘
       ▲                       │
       │      dikembalikan     │
       └───────────────────────┘
              untuk revisi

  FORBIDDEN: DRAFT → FINAL (langsung)
  FORBIDDEN: FINAL → (apapun)
```

### Definisi State

| State | Deskripsi | Bisa Diedit? |
|-------|-----------|--------------|
| `draft` | Sitrep sedang disusun oleh petugas | Ya |
| `ditinjau` | Sitrep disubmit, menunggu review komandan/pwnu | Tidak |
| `final` | Sitrep telah difinalisasi, snapshot terkunci | Tidak (immutable) |

### Aturan Transisi

| Dari | Ke | Aktor | Prasyarat |
|------|----|-------|-----------|
| `draft` | `ditinjau` | Petugas/operator | Semua field wajib sitrep terisi |
| `ditinjau` | `final` | komandan_insiden, `pwnu` | Konten sitrep valid dan lengkap |
| `ditinjau` | `draft` | komandan_insiden, `pwnu` | Catatan revisi diisi di `operasi_jurnal` |

### Finalization Rule (saat `status_sitrep` → `final`)

```php
// SitrepService::finalisasi()
public function finalisasi(OperasiSitrep $sitrep): void
{
    $snapshot = $this->buildSnapshot($sitrep);
    $hash     = hash('sha256', json_encode($snapshot));

    $sitrep->update([
        'status_sitrep'      => 'final',
        'waktu_difinalisasi' => now(),
        'id_penfinalisasi'   => auth()->id(),
        'hash_snapshot'      => $hash,
        // snapshot fields sudah terisi oleh trigger/sebelumnya
    ]);
}
```

### Snapshot Auto-Population

```
Trigger: tr_auto_snapshot_sitrep_update
Dieksekusi: BEFORE UPDATE pada operasi_sitrep

Logika:
  IF NEW.status_sitrep = 'final' THEN
      -- Trigger TIDAK mengubah snapshot (sudah final, immutable)
      -- Tidak ada perubahan diizinkan
  ELSE
      -- Auto-populate snapshot_dampak dari assessment_dampak_manusia
      -- berdasarkan id_insiden yang terkait
  END IF
```

Tabel sumber snapshot:

| Kolom Snapshot | Sumber Data | Tabel Asal |
|----------------|-------------|------------|
| `snapshot_dampak` | Data korban & dampak | `assessment_dampak_manusia` |
| `snapshot_logistik` | Stok & mutasi logistik | `logistik_stok`, `logistik_mutasi` |
| `snapshot_operasi` | Data klaster & mobilisasi | `operasi_klaster`, `operasi_mobilisasi_personil` |
| `snapshot_aktivitas` | Jurnal operasi | `operasi_jurnal` |

### Forbidden Transitions

```
FINAL → (apapun)    ✗  immutable setelah finalisasi, trigger menolak UPDATE
DRAFT → FINAL       ✗  harus melalui DITINJAU, diblokir di Policy
```

---

## 3. STATE MACHINE: PLENO (`operasi_pleno`)

> **Catatan:** Enum kolom `status_pleno` perlu dikonfirmasi dari dump lengkap schema `operasi_pleno`. Dokumen ini menggunakan nilai yang dapat diinferensikan dari PRD dan schema terkait.

### Kolom State

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `status_pleno` | `enum(...)` | State utama pleno (konfirmasi enum dari dump) |

### Tabel Terkait

- **`operasi_pleno`** — Rapat pleno utama
- **`operasi_pleno_peserta`** — Peserta pleno dan status kehadiran/persetujuan
- **`operasi_pleno_keputusan`** — Keputusan yang dihasilkan pleno

### Diagram State

```
  ┌──────────┐  siapkan   ┌──────────┐  peserta    ┌──────────────┐  penanda-  ┌───────────────┐  admin/  ┌──────────┐
  │  DRAFT   │ ─────────► │  REVIEW  │  menyetujui►│  DISETUJUI   │  tangan   ►│ DITANDATANGANI│  pwnu   ►│  FINAL   │
  │          │            │          │ ────────────►│              │ ──────────►│               │ ────────►│(immutable│
  └──────────┘            └──────────┘              └──────────────┘            └───────────────┘          └──────────┘

  FORBIDDEN: FINAL → (apapun)
  FORBIDDEN: Keputusan baru setelah pleno FINAL
```

### Definisi State

| State | Deskripsi | Bisa Tambah Keputusan? |
|-------|-----------|------------------------|
| `draft` | Pleno sedang disiapkan | Ya |
| `review` | Pleno dalam proses persetujuan peserta | Terbatas |
| `disetujui` | Mayoritas peserta telah menyetujui | Tidak |
| `ditandatangani` | Penandatangan resmi menandatangani berita acara | Tidak |
| `final` | Pleno selesai dan terkunci | Tidak (immutable) |

### Aturan Transisi

| Dari | Ke | Aktor | Prasyarat |
|------|----|-------|-----------|
| `draft` | `review` | Koordinator pleno, `pwnu`/`pcnu` | Agenda dan peserta telah ditentukan |
| `review` | `disetujui` | Sistem (otomatis setelah kuorum) | Kuorum peserta `hak_suara = 1` telah menyetujui |
| `disetujui` | `ditandatangani` | Penandatangan (`master_jabatan_penandatangan`) | Semua keputusan telah direkap |
| `ditandatangani` | `final` | `pwnu`, `super_admin` | Dokumen berita acara tersimpan |

### Peserta Pleno (`operasi_pleno_peserta`)

| Kolom | Tipe | Nilai |
|-------|------|-------|
| `status_kehadiran` | `enum(...)` | `hadir`, `izin`, `tanpa_keterangan` |
| `status_persetujuan` | `enum(...)` | `setuju`, `tolak`, `abstain` |
| `hak_suara` | `tinyint(1)` | `0` = tidak memiliki hak suara, `1` = memiliki hak suara |

### Forbidden Transitions

```
FINAL → (apapun)                         ✗  immutable
Tambah operasi_pleno_keputusan baru
  jika pleno.status_pleno = 'final'      ✗  diblokir di Policy
```

---

## 4. STATE MACHINE: SURAT (`operasi_surat_keluar`)

### Kolom State

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `status_surat` | `enum(...)` | State utama surat (konfirmasi enum dari dump) |

### Tabel Terkait

- **`operasi_surat_keluar`** — Data surat utama
- **`dokumen_surat_paraf`** — Rantai persetujuan/paraf
- **`dokumen_surat_tembusan`** — Daftar penerima tembusan
- **`master_surat_jenis`** — Master jenis surat
- **`master_surat_template`** — Template surat

### Diagram State

```
  ┌──────────┐  submit ke  ┌──────────┐  semua paraf  ┌──────────┐  penanda-  ┌──────────┐  simpan   ┌──────────┐
  │  DRAFT   │  paraf      │  REVIEW  │  disetujui   ►│ APPROVED │  tangan   ►│  SIGNED  │  arsip   ►│ ARCHIVED │
  │  (edit)  │ ──────────► │          │ ─────────────►│          │ ──────────►│          │ ─────────►│          │
  └──────────┘             └──────────┘               └──────────┘            └──────────┘           └──────────┘
       ▲                        │                                                   │
       │    paraf ditolak       │                                                   ▼
       └────────────────────────┘                                           ┌──────────────┐
                                                                            │  FINALIZED   │
                                                                            │  (immutable) │
                                                                            └──────────────┘

  FORBIDDEN: FINALIZED → (apapun)
  FORBIDDEN: Edit surat setelah APPROVED/SIGNED/FINALIZED/ARCHIVED
```

### Definisi State

| State | Deskripsi | Bisa Diedit? |
|-------|-----------|--------------|
| `draft` | Surat sedang disusun | Ya |
| `review` | Surat dikirim ke rantai paraf pertama | Tidak |
| `approved` | Semua paraf dalam `dokumen_surat_paraf` disetujui | Tidak |
| `signed` | Penandatangan utama menandatangani | Tidak |
| `finalized` | Surat final, PDF di-generate, immutable | Tidak (immutable) |
| `archived` | Surat disimpan ke arsip | Tidak |

### Rantai Paraf (`dokumen_surat_paraf`)

| Kolom | Tipe | Nilai |
|-------|------|-------|
| `status_paraf` | `enum(...)` | `menunggu`, `disetujui`, `ditolak` |
| `urutan` | `int` | Urutan paraf berurutan (1, 2, 3, ...) |

**Logika rantai paraf:**

```
Urutan 1 → urutan 2 → urutan 3 → ... → semua DISETUJUI → surat status APPROVED

Jika satu paraf dengan urutan N berstatus DITOLAK:
  → Surat dikembalikan ke DRAFT
  → Semua paraf dengan urutan > N direset ke MENUNGGU
  → Catatan penolakan disimpan
```

**Implementasi di Service:**

```php
// SuratService::prosesParaf()
public function prosesParaf(DokumenSuratParaf $paraf, string $keputusan): void
{
    DB::transaction(function () use ($paraf, $keputusan) {
        $paraf->update(['status_paraf' => $keputusan]);

        if ($keputusan === 'ditolak') {
            // Reset paraf sesudahnya
            DokumenSuratParaf::where('id_surat', $paraf->id_surat)
                ->where('urutan', '>', $paraf->urutan)
                ->update(['status_paraf' => 'menunggu']);

            // Kembalikan surat ke draft
            $paraf->suratUtama->update(['status_surat' => 'draft']);

        } elseif ($keputusan === 'disetujui') {
            // Cek apakah semua paraf sudah disetujui
            $belumSelesai = DokumenSuratParaf::where('id_surat', $paraf->id_surat)
                ->where('status_paraf', '!=', 'disetujui')
                ->exists();

            if (!$belumSelesai) {
                $paraf->suratUtama->update(['status_surat' => 'approved']);
            } else {
                // Aktifkan paraf berikutnya (urutan + 1)
                DokumenSuratParaf::where('id_surat', $paraf->id_surat)
                    ->where('urutan', $paraf->urutan + 1)
                    ->update(['status_paraf' => 'menunggu']); // siap untuk diproses
            }
        }
    });
}
```

### Finalization Rule

```
Saat status_surat = 'finalized':
  1. PDF di-generate dan disimpan ke storage/surat/
  2. Surat tidak dapat diedit (Policy::update() → false)
  3. Hash/checksum PDF disimpan untuk audit integrity

Hanya role pwnu atau super_admin yang dapat melakukan ARCHIVED.
```

### Forbidden Transitions

```
FINALIZED → (apapun)     ✗  immutable, Policy menolak
ARCHIVED  → (apapun)     ✗  terminal state
DRAFT     → SIGNED       ✗  harus melalui REVIEW dan APPROVED
DRAFT     → FINALIZED    ✗  harus melalui alur lengkap
```

---

## 5. STATE MACHINE: POS AJU (`operasi_posaju`)

> **Catatan:** Enum kolom `status_posaju` perlu dikonfirmasi dari dump lengkap schema `operasi_posaju`.

### Kolom State

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `status_posaju` | `enum(...)` | State pos aju (konfirmasi enum dari dump) |

### Diagram State

```
  ┌────────────────┐  pos      ┌──────────┐  perpanjangan   ┌──────────────┐
  │  DIRENCANAKAN  │  aktif   ►│  AKTIF   │  pleno         ►│ DIPERPANJANG │
  │  (dari pleno)  │ ─────────►│          │ ───────────────►│              │
  └────────────────┘           └──────────┘                 └──────────────┘
                                    │                               │
                                    │ ditutup                       │ ditutup
                                    ▼                               ▼
                               ┌──────────┐                   ┌──────────┐
                               │ DITUTUP  │◄──────────────────│ DITUTUP  │
                               │(terminal)│                   │(terminal)│
                               └──────────┘                   └──────────┘

  FORBIDDEN: DITUTUP → AKTIF
  FORBIDDEN: DITUTUP → DIPERPANJANG
```

### Definisi State

| State | Deskripsi |
|-------|-----------|
| `direncanakan` | Pos aju diinisiasi berdasarkan keputusan pleno (`id_pleno` diisi) |
| `aktif` | Pos aju beroperasi di lapangan |
| `diperpanjang` | Masa operasi pos aju diperpanjang via keputusan pleno baru |
| `ditutup` | Pos aju selesai beroperasi (terminal state) |

### Aturan Transisi

| Dari | Ke | Aktor | Prasyarat |
|------|----|-------|-----------|
| `direncanakan` | `aktif` | Komandan pos aju | Personil dan logistik sudah dimobilisasi |
| `aktif` | `diperpanjang` | `pwnu`, komandan_insiden | Ada keputusan pleno (`operasi_pleno_keputusan`) |
| `aktif` | `ditutup` | Komandan pos aju, `pwnu` | Laporan penutupan diisi |
| `diperpanjang` | `ditutup` | Komandan pos aju, `pwnu` | Masa perpanjangan habis atau laporan penutupan |

### Forbidden Transitions

```
DITUTUP → AKTIF          ✗  terminal state, diblokir di Policy
DITUTUP → DIPERPANJANG   ✗  terminal state, diblokir di Policy
DIRENCANAKAN → DITUTUP   ✗  tidak logis (pos belum pernah aktif)
```

---

## 6. STATE MACHINE: GAP KEBUTUHAN

> **Catatan:** Gap kebutuhan merepresentasikan kekurangan sumber daya yang teridentifikasi dari feedback klaster. Implementasi spesifik tabel perlu dikonfirmasi dari dump schema.

### Diagram State

```
  ┌──────────┐  identifikasi  ┌──────────┐  pengerahan   ┌──────────────┐  konfirmasi  ┌──────────┐
  │ TERBUKA  │  dari klaster ►│DIPROSES  │  logistik/   ►│  TERPENUHI   │  koordinator►│ DITUTUP  │
  │          │ ──────────────►│          │  relawan      │              │ ────────────►│          │
  └──────────┘                └──────────┘               └──────────────┘              └──────────┘
       ▲                           │
       │   eskalasi/reevaluasi     │
       └───────────────────────────┘

  FORBIDDEN: DITUTUP → (apapun)
  FORBIDDEN: TERPENUHI → TERBUKA (tidak dapat dibuka ulang)
```

### Definisi State

| State | Deskripsi | Aktor |
|-------|-----------|-------|
| `terbuka` | Gap diidentifikasi dari feedback klaster atau assessment | Koordinator klaster |
| `diproses` | Penanganan sedang berjalan (logistik dikirim / relawan dikerahkan) | Logistik / komandan_insiden |
| `terpenuhi` | Kebutuhan sudah terpenuhi di lapangan | Koordinator klaster |
| `ditutup` | Ditutup secara resmi oleh koordinator klaster | Koordinator klaster |

---

## 7. STATE MACHINE: ESKALASI (`operasi_eskalasi`)

### Kolom State

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `level_sebelumnya` | `enum('lokal','pcnu','pwnu','nasional')` | Level sebelum eskalasi |
| `level_baru` | `enum('lokal','pcnu','pwnu','nasional')` | Level setelah eskalasi |
| `id_pleno` | `bigint unsigned` | FK ke `operasi_pleno.id` (wajib diisi) |
| `id_insiden` | `bigint unsigned` | FK ke `operasi_insiden.id` |

### Diagram State

```
  ┌──────────┐  pcnu        ┌──────────┐  pwnu        ┌──────────┐  nasional    ┌──────────────┐
  │  lokal   │  mengambil  ►│  pcnu    │  mengambil  ►│  pwnu    │  perlu      ►│   nasional   │
  │          │  alih        │          │  alih        │          │  eskalasi    │              │
  └──────────┘              └──────────┘              └──────────┘              └──────────────┘

  Semua transisi WAJIB memiliki id_pleno yang valid (keputusan pleno)

  ESKALASI TURUN: diizinkan hanya via pleno khusus de-eskalasi
  TANPA id_pleno: FORBIDDEN
```

### Aturan Transisi

| Dari | Ke | Prasyarat |
|------|----|-----------|
| `lokal` | `pcnu` | PCNU mengambil alih, ada `id_pleno` valid |
| `pcnu` | `pwnu` | PWNU mengambil alih, ada `id_pleno` valid |
| `pwnu` | `nasional` | Eskalasi ke level nasional, ada `id_pleno` valid |

### Eskalasi Turun (De-eskalasi)

| Dari | Ke | Prasyarat |
|------|----|-----------|
| `pwnu` | `pcnu` | Pleno khusus de-eskalasi, `id_pleno` valid dengan jenis pleno de-eskalasi |
| `pcnu` | `lokal` | Pleno khusus de-eskalasi, `id_pleno` valid |

### Forbidden Transitions

```
Transisi tanpa id_pleno yang valid              ✗  FK constraint + Policy
Eskalasi turun tanpa pleno khusus               ✗  diblokir di Service layer
nasional → (lebih rendah) tanpa pleno khusus    ✗  diblokir di Policy
```

**Implementasi validasi di Service:**

```php
// EskalasiService::buat()
public function buat(array $data): OperasiEskalasi
{
    // Validasi: id_pleno wajib ada dan statusnya valid
    $pleno = OperasiPleno::findOrFail($data['id_pleno']);

    if (!in_array($pleno->status_pleno, ['disetujui', 'ditandatangani', 'final'])) {
        throw new \InvalidArgumentException('Pleno belum disetujui untuk mendukung eskalasi.');
    }

    // Validasi: tidak boleh sama
    if ($data['level_sebelumnya'] === $data['level_baru']) {
        throw new \InvalidArgumentException('Level eskalasi tidak boleh sama.');
    }

    return OperasiEskalasi::create($data);
}
```

---

## 8. STATE MACHINE: ASET (`aset_unit`)

### Kolom State

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_status` | `tinyint unsigned` | FK ke `aset_master_status.id` |
| `kondisi_fisik` | `enum('baik','rusak_ringan','rusak_berat')` | Kondisi fisik aset |

### Nilai `aset_master_status`

| `id` | Nama Status | Keterangan |
|------|-------------|------------|
| `1` | Tersedia | Aset siap digunakan |
| `2` | Dalam Tugas | Aset sedang digunakan/dipinjam |
| `3` | Perbaikan/Maintenance | Aset dalam perbaikan |
| `4` | Rusak | Aset rusak, tidak dapat digunakan |
| `5` | Hilang | Aset hilang/tidak ditemukan |

### Diagram State

```
                     ┌────────────────────────────────────────────────────┐
                     │                    HILANG (5)                      │
                     │              (dari semua state)                    │
                     └────────────────────────────────────────────────────┘
                              ↑              ↑              ↑

  ┌──────────────┐  tr_prevent    ┌──────────────────┐
  │  TERSEDIA(1) │  _double_     ►│  DALAM TUGAS (2) │
  │              │  booking_aset  │                  │
  └──────────────┘                └──────────────────┘
        ▲  │                              │
        │  │ manual                       │ waktu_kembali diisi
        │  ▼ admin                        │ tr_aset_return_to_available
        │  ┌──────────────────────────┐   │
        │  │  PERBAIKAN/MAINTENANCE(3)│   │
        └──│                          │───┘
           │  setelah selesai         │
           └──────────────────────────┘
                     │
                     ▼ (kondisi fatal)
             ┌──────────────┐
             │   RUSAK (4)  │
             │              │
             └──────────────┘
                     │ setelah diperbaiki
                     ▼
             ┌──────────────┐
             │  TERSEDIA(1) │
             └──────────────┘
```

### Aturan Transisi

| Dari | Ke | Trigger/Aktor | Prasyarat |
|------|----|---------------|-----------|
| `1` (Tersedia) | `2` (Dalam Tugas) | `tr_prevent_double_booking_aset` | Insert ke `aset_penggunaan` |
| `2` (Dalam Tugas) | `1` (Tersedia) | `tr_aset_return_to_available` | `aset_penggunaan.waktu_kembali` diisi |
| `1` (Tersedia) | `3` (Perbaikan) | Admin aset (manual) | Kondisi fisik mengharuskan perbaikan |
| `3` (Perbaikan) | `1` (Tersedia) | Admin aset (manual) | Perbaikan selesai, kondisi fisik = `baik` |
| `(semua)` | `4` (Rusak) | Admin aset | Kondisi darurat, aset tidak dapat digunakan |
| `4` (Rusak) | `1` (Tersedia) | Admin aset | Setelah perbaikan berhasil |
| `(semua)` | `5` (Hilang) | Admin aset | Aset tidak ditemukan setelah investigasi |

### Double-Booking Prevention

```sql
-- Trigger: tr_prevent_double_booking_aset
-- Dieksekusi: BEFORE INSERT pada aset_penggunaan
DELIMITER $$
CREATE TRIGGER tr_prevent_double_booking_aset
BEFORE INSERT ON aset_penggunaan
FOR EACH ROW
BEGIN
    DECLARE current_status TINYINT;

    SELECT id_status INTO current_status
    FROM aset_unit
    WHERE id = NEW.id_aset;

    IF current_status <> 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Aset tidak tersedia untuk digunakan (status bukan Tersedia).';
    END IF;
END$$
DELIMITER ;
```

### Auto-Return Trigger

```sql
-- Trigger: tr_aset_return_to_available
-- Dieksekusi: AFTER UPDATE pada aset_penggunaan
DELIMITER $$
CREATE TRIGGER tr_aset_return_to_available
AFTER UPDATE ON aset_penggunaan
FOR EACH ROW
BEGIN
    IF NEW.waktu_kembali IS NOT NULL AND OLD.waktu_kembali IS NULL THEN
        UPDATE aset_unit
        SET id_status = 1
        WHERE id = NEW.id_aset;
    END IF;
END$$
DELIMITER ;
```

### Kondisi Fisik (`kondisi_fisik`)

```
baik         → id_status dapat Tersedia (1) atau Dalam Tugas (2)
rusak_ringan → id_status disarankan Perbaikan (3)
rusak_berat  → id_status disarankan Rusak (4)
```

---

## 9. STATE MACHINE: PERMINTAAN LOGISTIK (`logistik_permintaan`)

### Kolom State

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `status_permintaan` | `enum('draft','diajukan','disetujui','ditolak','dikirim','selesai')` | State utama permintaan |

### Diagram State

```
  ┌──────────┐  submit    ┌──────────┐  approve   ┌──────────────┐  kirim   ┌──────────┐  terima  ┌──────────┐
  │  DRAFT   │ ─────────► │ DIAJUKAN │ ──────────►│  DISETUJUI   │ ────────►│ DIKIRIM  │ ────────►│ SELESAI  │
  │  (edit)  │            │          │            │              │          │          │          │          │
  └──────────┘            └──────────┘            └──────────────┘          └──────────┘          └──────────┘
                               │ tolak
                               ▼
                          ┌──────────┐
                          │ DITOLAK  │
                          │(terminal)│
                          └──────────┘

  FORBIDDEN: SELESAI → (apapun)
  FORBIDDEN: DITOLAK → (apapun, harus buat permintaan baru)
  FORBIDDEN: DRAFT → DIKIRIM langsung
```

### Definisi State

| State | Deskripsi | Bisa Diedit? |
|-------|-----------|--------------|
| `draft` | Permintaan sedang disusun oleh koordinator klaster | Ya |
| `diajukan` | Permintaan disubmit ke logistik pwnu/pcnu | Tidak |
| `disetujui` | Permintaan disetujui, barang disiapkan | Tidak |
| `ditolak` | Permintaan ditolak (terminal, buat permintaan baru) | Tidak |
| `dikirim` | Barang telah dikirim ke lapangan | Tidak |
| `selesai` | Barang diterima dan dikonfirmasi penerima | Tidak (immutable) |

### Aturan Transisi

| Dari | Ke | Aktor | Prasyarat |
|------|----|-------|-----------|
| `draft` | `diajukan` | Koordinator klaster (`operasi_klaster_koordinator`) | Item permintaan tidak kosong |
| `diajukan` | `disetujui` | Logistik `pwnu`/`pcnu` | Stok tersedia di `logistik_stok` |
| `diajukan` | `ditolak` | Logistik `pwnu`/`pcnu` | Alasan penolakan diisi |
| `disetujui` | `dikirim` | Logistik | Mutasi keluar dicatat di `logistik_mutasi` |
| `dikirim` | `selesai` | Penerima manfaat / koordinator lapangan | Konfirmasi penerimaan diisi |

### Integrasi dengan `logistik_mutasi`

```
Saat status_permintaan → 'dikirim':
  → INSERT ke logistik_mutasi dengan tipe_mutasi = 'keluar'
  → logistik_stok.jumlah_tersedia berkurang
  → id_permintaan dicatat pada logistik_mutasi

Saat status_permintaan → 'selesai':
  → UPDATE logistik_mutasi.waktu_konfirmasi diisi
```

---

## 10. STATE MACHINE: RELAWAN PENDAFTARAN (`relawan_pendaftaran`)

> **Catatan:** Enum kolom `status_pendaftaran` perlu dikonfirmasi dari dump lengkap schema `relawan_pendaftaran`. Nilai berikut diinferensi dari konteks sistem.

### Kolom State

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `status_pendaftaran` | `enum(...)` | State pendaftaran relawan (konfirmasi dari dump) |

### Diagram State

```
  ┌──────────┐  submit    ┌──────────┐  verifikasi  ┌──────────┐  aktifkan  ┌──────────┐
  │ MENUNGGU │ ─────────► │MENUNGGU  │ ────────────►│DIVERIFI- │ ──────────►│  AKTIF   │
  │ (baru)   │            │VERIFIKASI│              │  KASI    │            │          │
  └──────────┘            └──────────┘              └──────────┘            └──────────┘
                               │ tolak
                               ▼
                          ┌──────────┐
                          │ DITOLAK  │
                          │(terminal)│
                          └──────────┘
                                                         │ nonaktifkan
                                                         ▼
                                                    ┌──────────┐
                                                    │NONAKTIF  │
                                                    │          │
                                                    └──────────┘
```

### Definisi State (Perkiraan)

| State | Deskripsi |
|-------|-----------|
| `menunggu` | Pendaftaran baru disubmit, belum diverifikasi |
| `diverifikasi` | Data dan keahlian relawan telah diverifikasi pcnu/pwnu |
| `aktif` | Relawan aktif, dapat ditugaskan |
| `ditolak` | Pendaftaran ditolak (terminal) |
| `nonaktif` | Relawan dinonaktifkan sementara |

### Keterkaitan dengan `auth_users`

```
relawan_pendaftaran.status_pendaftaran = 'aktif'
  → auth_users.status_akun = 'aktif'   (sinkronisasi diperlukan)
  → model_has_roles: role 'relawan' ditetapkan ke user

relawan_pendaftaran.status_pendaftaran = 'ditolak' / 'nonaktif'
  → auth_users.status_akun = 'nonaktif' atau 'suspend'
```

### Keterkaitan dengan `auth_pengguna_keahlian`

```
Saat pendaftaran DIVERIFIKASI:
  → Keahlian relawan dicatat ke auth_pengguna_keahlian
    - id_pengguna, id_keahlian (FK ke auth_keahlian_master)
    - tingkat_keahlian, status_verifikasi
  → Profil dicatat/diperbarui di auth_pengguna_profil
```

---

## 11. CATATAN: LOCK RULE GLOBAL

### Hierarki Lock

```
operasi_insiden.is_locked = 1
  ├── operasi_sitrep (terkait insiden) → tidak bisa diubah ke non-final
  ├── operasi_klaster (terkait insiden) → tidak bisa ditambah/diubah
  ├── operasi_mobilisasi_personil (terkait insiden) → immutable
  ├── logistik_permintaan (terkait insiden) → tidak bisa diajukan baru
  ├── aset_penggunaan (terkait insiden) → tidak bisa booking baru
  └── assessment_utama (terkait insiden) → immutable
```

### Tabel Lock Reference

| Tabel | Kolom Lock | Kondisi Lock | Mekanisme |
|-------|------------|--------------|-----------|
| `operasi_insiden` | `is_locked` | `status_insiden = 'selesai'` | MySQL Trigger + Laravel Policy |
| `operasi_sitrep` | `status_sitrep = 'final'` | Finalisasi sitrep | Trigger + Policy |
| `operasi_surat_keluar` | `status_surat = 'finalized'` | Finalisasi surat | Laravel Policy |
| `operasi_pleno` | `status_pleno = 'final'` | Finalisasi pleno | Laravel Policy |
| `operasi_eskalasi` | — | Data tidak dapat dihapus | FK + Policy (no delete) |

### Implementasi Laravel Policy (Contoh)

```php
// App\Policies\InsidenPolicy
public function update(User $user, OperasiInsiden $insiden): bool
{
    // Cek lock database
    if ($insiden->is_locked) {
        return false;
    }

    // Cek state yang tidak bisa diedit
    if (in_array($insiden->status_insiden, ['selesai', 'dibatalkan'])) {
        return false;
    }

    // Cek role
    return $user->hasAnyRole(['pwnu', 'pcnu', 'super_admin']);
}

public function transisiStatus(User $user, OperasiInsiden $insiden, string $statusBaru): bool
{
    if ($insiden->is_locked) {
        return false;
    }

    $allowed = $this->getAllowedTransitions($insiden->status_insiden);

    if (!in_array($statusBaru, $allowed)) {
        return false;
    }

    // Hanya pwnu yang bisa membatalkan
    if ($statusBaru === 'dibatalkan') {
        return $user->hasRole('pwnu') || $user->hasRole('super_admin');
    }

    return $user->hasAnyRole(['pwnu', 'pcnu', 'super_admin']);
}

private function getAllowedTransitions(string $statusSaatIni): array
{
    return match($statusSaatIni) {
        'draft'         => ['terverifikasi', 'dibatalkan'],
        'terverifikasi' => ['respon', 'dibatalkan'],
        'respon'        => ['pemulihan', 'dibatalkan'],
        'pemulihan'     => ['selesai', 'dibatalkan'],
        'selesai'       => [],        // immutable
        'dibatalkan'    => [],        // terminal
        default         => [],
    };
}
```

### Konsistensi Validasi (3 Lapis)

```
Lapisan 1: MySQL Trigger
  → tr_lock_incident_data (BEFORE UPDATE operasi_insiden)
  → tr_prevent_double_booking_aset (BEFORE INSERT aset_penggunaan)
  → tr_aset_return_to_available (AFTER UPDATE aset_penggunaan)
  → tr_auto_snapshot_sitrep_update (BEFORE UPDATE operasi_sitrep)

Lapisan 2: Laravel Policy / Service
  → InsidenPolicy::update(), InsidenPolicy::transisiStatus()
  → SitrepPolicy::update()
  → SuratPolicy::update()
  → AsetPolicy::gunakan()

Lapisan 3: Form Request / Controller Validation
  → TransisiInsidenRequest (validasi enum dan prasyarat)
  → PermintaanLogistikRequest (validasi stok tersedia)
  → AsetPenggunaanRequest (validasi status aset = Tersedia)
```

---

## Ringkasan State Machine per Domain

| Domain | Tabel Utama | Kolom State | State Terminal | Lock Mechanism |
|--------|-------------|-------------|----------------|----------------|
| Insiden | `operasi_insiden` | `status_insiden` | `selesai`, `dibatalkan` | `is_locked=1` + Trigger |
| Sitrep | `operasi_sitrep` | `status_sitrep` | `final` | Policy + Trigger |
| Pleno | `operasi_pleno` | `status_pleno` | `final` | Policy |
| Surat | `operasi_surat_keluar` | `status_surat` | `finalized`, `archived` | Policy |
| Pos Aju | `operasi_posaju` | `status_posaju` | `ditutup` | Policy |
| Gap Kebutuhan | *(konfirmasi tabel)* | *(konfirmasi kolom)* | `ditutup` | Policy |
| Eskalasi | `operasi_eskalasi` | `level_baru` | — | FK + Policy |
| Aset | `aset_unit` | `id_status` | `5` (Hilang) | Trigger |
| Logistik | `logistik_permintaan` | `status_permintaan` | `selesai`, `ditolak` | Policy |
| Relawan | `relawan_pendaftaran` | `status_pendaftaran` | `ditolak` | Policy |

---

*Dokumen ini bersifat pra-produksi. Enum yang bertanda "konfirmasi dari dump" harus diverifikasi dengan schema dump terbaru sebelum implementasi.*
