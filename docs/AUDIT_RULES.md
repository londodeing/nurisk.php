# AUDIT_RULES.md — NURISK

> Dokumen ini mendefinisikan aturan pencatatan log audit di sistem NURISK.
> Semua nama tabel dan kolom mengacu langsung pada SQL schema production.

---

## 1. Perbedaan Tiga Jenis Log

### 1.1 Audit Log — `operasi_jurnal`

| Atribut       | Detail                                                           |
|---------------|------------------------------------------------------------------|
| **Tabel**     | `operasi_jurnal`                                                 |
| **Tujuan**    | Catatan naratif event operasional selama siklus hidup insiden    |
| **Dicatat**   | Oleh service layer (bukan controller, bukan observer)            |

**Kolom utama:**

| Kolom               | Tipe      | Keterangan                                          |
|---------------------|-----------|-----------------------------------------------------|
| `id_jurnal`         | PK        | Primary key                                         |
| `id_insiden`        | FK        | Referensi ke `operasi_insiden.id_insiden`           |
| `id_pengguna`       | FK        | Referensi ke `auth_users.id` — siapa yang melakukan |
| `kategori_event`    | ENUM/str  | Kategori event (lihat bagian 2)                     |
| `judul_event`       | string    | Judul singkat event                                 |
| `deskripsi_event`   | text      | Narasi detail event (opsional)                      |
| `id_referensi`      | int       | Polymorphic — ID entitas yang dirujuk               |
| `tabel_referensi`   | string    | Polymorphic — nama tabel entitas yang dirujuk       |
| `waktu_event`       | timestamp | Waktu event terjadi                                 |

**Karakteristik:**
- Dicatat oleh service layer secara eksplisit (bukan otomatis via Observer)
- Polymorphic reference: `id_referensi` + `tabel_referensi` menunjuk ke entitas mana pun
- **Tidak dapat dihapus** — tidak ada kolom `dihapus_pada` di tabel ini
- Dicatat untuk setiap event penting dalam lifecycle operasi insiden

---

### 1.2 Workflow Log — `riwayat_status_insiden`

| Atribut       | Detail                                                           |
|---------------|------------------------------------------------------------------|
| **Tabel**     | `riwayat_status_insiden`                                         |
| **Tujuan**    | Histori transisi status insiden dari satu state ke state lain    |
| **Dicatat**   | Otomatis setiap kali `operasi_insiden.status_insiden` berubah    |

**Kolom utama:**

| Kolom               | Tipe      | Keterangan                                           |
|---------------------|-----------|------------------------------------------------------|
| `id_history`        | PK        | Primary key                                          |
| `id_insiden`        | FK        | Referensi ke `operasi_insiden.id_insiden`            |
| `status_sebelumnya` | ENUM      | Status sebelum transisi                              |
| `status_terbaru`    | ENUM      | Status setelah transisi                              |
| `id_pengguna`       | FK        | Referensi ke `auth_users.id` — siapa yang mengubah  |
| `alasan`            | text      | Alasan transisi (wajib untuk status `dibatalkan`)    |
| `dibuat_pada`       | timestamp | Waktu transisi terjadi                               |
| `dihapus_pada`      | timestamp | Soft delete (tersedia, tapi tidak boleh di-purge)    |

**Karakteristik:**
- `status_sebelumnya` dan `status_terbaru` wajib tercatat di setiap baris
- `alasan` wajib diisi jika `status_terbaru = 'dibatalkan'`
- Soft delete tersedia via `dihapus_pada`, namun **tidak boleh di-purge permanen**
- Nilai enum status: `draft`, `terverifikasi`, `respon`, `pemulihan`, `selesai`, `dibatalkan`

---

### 1.3 Spatie Activity Log (Opsional)

Jika paket `spatie/laravel-activitylog` diinstal, gunakan dengan aturan berikut:

**Field yang BOLEH dilog:**
- `operasi_insiden.status_insiden` — perubahan status
- `logistik_stok.jumlah_tersedia` — perubahan stok signifikan
- `dokumen_surat_paraf.status_paraf` — persetujuan/penolakan paraf

**Field yang DILARANG dilog:**
- Semua field secara massal (`$fillable` tanpa filter)
- Field yang berubah sering dan tidak kritis (misalnya `diperbarui_pada`)
- Field binary/blob

**Payload wajib per entry:**
```
actor       → auth_users.id
action      → nama aksi (string)
entity      → nama tabel yang terdampak
before      → nilai sebelum (JSON)
after       → nilai sesudah (JSON)
timestamp   → waktu perubahan
ip_address  → IP aktor
```

---

## 2. Event yang WAJIB Dilog ke `operasi_jurnal`

Setiap event berikut **wajib** menghasilkan satu baris di tabel `operasi_jurnal`.

| No | Event                                     | `kategori_event` | Entitas Referensi                       |
|----|-------------------------------------------|------------------|-----------------------------------------|
| 1  | Insiden dibuat                            | `laporan`        | `operasi_insiden`                       |
| 2  | Insiden diverifikasi                      | `aktivasi`       | `operasi_insiden`                       |
| 3  | Tanggap darurat dimulai (status: respon)  | `respon`         | `operasi_insiden`                       |
| 4  | Pleno dibuat                              | `sistem`         | `operasi_pleno`                         |
| 5  | Pleno difinalkan                          | `sistem`         | `operasi_pleno`                         |
| 6  | Surat ditandatangani / paraf disetujui    | `sistem`         | `dokumen_surat_paraf`                   |
| 7  | Penugasan operasi dibuat                  | `penugasan`      | `operasi_penugasan`                     |
| 8  | Mutasi logistik signifikan                | `logistik`       | `logistik_mutasi`                       |
| 9  | Aset dipinjam (status → Dalam Tugas)      | `aset`           | `aset_penggunaan`                       |
| 10 | Aset dikembalikan (status → Tersedia)     | `aset`           | `aset_penggunaan`                       |
| 11 | Personel mobilisasi ke lapangan           | `personil`       | `operasi_mobilisasi_personil`           |
| 12 | Pos aju dibuka                            | `posko`          | `operasi_posaju`                        |
| 13 | Pos aju ditutup                           | `posko`          | `operasi_posaju`                        |
| 14 | Insiden selesai (status: selesai)         | `selesai`        | `operasi_insiden`                       |
| 15 | Eskalasi terjadi                          | `sistem`         | `operasi_eskalasi`                      |
| 16 | Gap kebutuhan ditutup                     | `logistik`       | `assessment_kebutuhan_mendesak`         |
| 17 | Relawan diapprove                         | `personil`       | `relawan_pendaftaran`                   |

---

## 3. Field Standar Audit

Setiap log — baik di `operasi_jurnal` maupun `riwayat_status_insiden` — wajib memiliki field berikut:

| Field         | Kolom di DB             | Wajib | Keterangan                                          |
|---------------|-------------------------|-------|-----------------------------------------------------|
| `actor`       | `id_pengguna`           | ✅    | ID pengguna dari `auth_users.id`                    |
| `action`      | `judul_event`           | ✅    | String deskriptif aksi yang dilakukan               |
| `entity`      | `tabel_referensi`       | ✅    | Nama tabel yang terdampak                           |
| `before`      | `deskripsi_event` (JSON)| ⬜    | Nilai sebelum — opsional, format JSON dalam string  |
| `after`       | `deskripsi_event` (JSON)| ✅    | Nilai sesudah — format JSON dalam string            |
| `timestamp`   | `waktu_event`           | ✅    | `CURRENT_TIMESTAMP` saat event terjadi              |
| `ip_address`  | *(kolom tambahan)*      | ⬜    | Tambahkan ke `operasi_jurnal` jika skema mendukung  |

> [!NOTE]
> Kolom `ip_address` belum ada di schema saat ini. Jika ditambahkan, gunakan `request()->ip()` di service layer. Dokumentasikan sebagai mismatch jika tidak ditambahkan ke skema.

---

## 4. Implementasi di Laravel

### 4.1 Method Helper di Service

Setiap service yang mengelola operasi insiden wajib mengimplementasikan method berikut (private):

```php
/**
 * Catat event ke tabel operasi_jurnal.
 * Panggil method ini di dalam setiap method service yang mengubah state penting.
 *
 * @param int         $idInsiden        ID insiden terkait (operasi_insiden.id_insiden)
 * @param string      $kategori         Kategori event: laporan|aktivasi|respon|sistem|penugasan|logistik|aset|personil|posko|selesai
 * @param string      $judul            Judul singkat event (max 255 karakter)
 * @param string|null $deskripsi        Narasi detail event (opsional)
 * @param int|null    $idReferensi      ID entitas yang dirujuk (polymorphic)
 * @param string|null $tabelReferensi   Nama tabel entitas yang dirujuk (polymorphic)
 */
private function catatJurnal(
    int $idInsiden,
    string $kategori,
    string $judul,
    ?string $deskripsi = null,
    ?int $idReferensi = null,
    ?string $tabelReferensi = null
): void {
    OperasiJurnal::create([
        'id_insiden'       => $idInsiden,
        'id_pengguna'      => auth()->id(),
        'kategori_event'   => $kategori,
        'judul_event'      => $judul,
        'deskripsi_event'  => $deskripsi,
        'id_referensi'     => $idReferensi,
        'tabel_referensi'  => $tabelReferensi,
        // waktu_event → dihandle oleh default CURRENT_TIMESTAMP di DB
    ]);
}
```

### 4.2 Method Helper untuk Transisi Status

Setiap transisi `operasi_insiden.status_insiden` wajib memanggil method berikut:

```php
/**
 * Catat transisi status ke tabel riwayat_status_insiden.
 * Panggil SETELAH status di operasi_insiden berhasil di-update.
 *
 * @param int         $idInsiden        ID insiden (operasi_insiden.id_insiden)
 * @param string      $statusSebelumnya Nilai status sebelum transisi
 * @param string      $statusTerbaru    Nilai status setelah transisi
 * @param string|null $alasan           Wajib jika $statusTerbaru === 'dibatalkan'
 */
private function catatTransisiStatus(
    int $idInsiden,
    string $statusSebelumnya,
    string $statusTerbaru,
    ?string $alasan = null
): void {
    if ($statusTerbaru === 'dibatalkan' && empty($alasan)) {
        throw new \InvalidArgumentException('Alasan wajib diisi untuk transisi ke status dibatalkan.');
    }

    RiwayatStatusInsiden::create([
        'id_insiden'        => $idInsiden,
        'status_sebelumnya' => $statusSebelumnya,
        'status_terbaru'    => $statusTerbaru,
        'id_pengguna'       => auth()->id(),
        'alasan'            => $alasan,
    ]);
}
```

### 4.3 Contoh Penggunaan dalam Service

```php
// Dalam InsidenService::verifikasiInsiden()
public function verifikasiInsiden(int $idInsiden, int $idPengguna): OperasiInsiden
{
    $insiden = OperasiInsiden::findOrFail($idInsiden);
    $statusLama = $insiden->status_insiden;

    $insiden->update(['status_insiden' => 'terverifikasi']);

    // Log transisi status
    $this->catatTransisiStatus($idInsiden, $statusLama, 'terverifikasi');

    // Log ke jurnal operasional
    $this->catatJurnal(
        idInsiden: $idInsiden,
        kategori: 'aktivasi',
        judul: 'Insiden diverifikasi',
        deskripsi: "Insiden #{$idInsiden} diverifikasi oleh pengguna ID {$idPengguna}.",
        idReferensi: $idInsiden,
        tabelReferensi: 'operasi_insiden'
    );

    return $insiden->fresh();
}
```

---

## 5. Audit Trail untuk Surat dan Pleno

### 5.1 Audit Paraf Surat

Setiap perubahan pada `dokumen_surat_paraf` wajib menghasilkan log di `operasi_jurnal`:

| Kondisi                     | `judul_event`                    | `kategori_event` |
|-----------------------------|----------------------------------|------------------|
| Paraf disetujui             | `Surat diparaf: [nama penanda]`  | `sistem`         |
| Paraf ditolak               | `Paraf ditolak: [nama penanda]`  | `sistem`         |
| Tembusan ditambahkan        | `Tembusan surat ditambahkan`     | `sistem`         |

Referensi polymorphic:
- `id_referensi` → `dokumen_surat_paraf.id_paraf`
- `tabel_referensi` → `'dokumen_surat_paraf'`

### 5.2 Audit Keputusan Pleno

Setiap baris yang dibuat di `operasi_pleno_keputusan` wajib dilog:

| Kondisi                     | `judul_event`                        | `kategori_event` |
|-----------------------------|--------------------------------------|------------------|
| Keputusan pleno dibuat      | `Keputusan pleno #[id] dicatat`      | `sistem`         |
| Pleno difinalkan            | `Pleno #[id] difinalkan`             | `sistem`         |
| Peserta hadir dikonfirmasi  | `Peserta pleno dikonfirmasi`         | `sistem`         |

Referensi:
- `id_referensi` → `operasi_pleno.id_pleno`
- `tabel_referensi` → `'operasi_pleno'`

### 5.3 Audit Finalisasi Sitrep

Saat `operasi_sitrep.status_sitrep` berubah ke `'final'`, wajib catat:

```php
$this->catatJurnal(
    idInsiden: $sitrep->id_insiden,
    kategori: 'sistem',
    judul: "Sitrep #{$sitrep->id_sitrep} difinalkan",
    deskripsi: json_encode([
        'id_sitrep'    => $sitrep->id_sitrep,
        'id_pengguna'  => auth()->id(),
        'file_pdf'     => $sitrep->file_pdf_path,
    ]),
    idReferensi: $sitrep->id_sitrep,
    tabelReferensi: 'operasi_sitrep'
);
```

---

## 6. Aturan Retensi Log

| Tabel                      | Boleh Dihapus?     | Keterangan                                              |
|----------------------------|--------------------|---------------------------------------------------------|
| `operasi_jurnal`           | ❌ DILARANG        | Tidak ada mekanisme delete — harus dipertahankan selamanya |
| `riwayat_status_insiden`   | ⬜ Soft delete saja | Kolom `dihapus_pada` tersedia; hard delete/purge dilarang |
| `failed_jobs`              | ✅ Boleh           | Hapus setelah 30 hari via `php artisan queue:flush`     |
| `jobs`                     | ✅ Otomatis        | Dihapus otomatis setelah job selesai diproses           |
| `cache` / `cache_locks`    | ✅ Boleh           | Dihapus sesuai TTL atau via `php artisan cache:clear`   |

> [!CAUTION]
> Jangan pernah menjalankan `TRUNCATE operasi_jurnal` atau `DELETE FROM operasi_jurnal` di environment manapun, termasuk staging. Data jurnal adalah bukti audit operasional yang tidak dapat dipulihkan.
