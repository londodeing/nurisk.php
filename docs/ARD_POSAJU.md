# ARD — Application Requirements Document
## Domain: Pos Aju (Pos Komando Lapangan)

**Versi:** 1.0
**Tanggal:** 2026-07-14
**Source of Truth:** MySQL Schema `nurisk` + DOMAIN_RULES.md + IMPLEMENTATION_BACKLOG.md
**Status:** Draft

---

## 1. BUSINESS CONTEXT

### 1.1 Domain Definition
Pos Aju (Pos Komando/Aju) adalah pusat komando lapangan untuk respons bencana di tingkat PCNU. Setiap Pos Aju:
- Didirikan berdasarkan keputusan pleno (`operasi_pleno_keputusan`)
- Menjadi pusat distribusi bantuan per klaster (SAR, Medis, Logistik, Psikososial, Dapur Umum, dll)
- Merekam feedback distribusi bantuan untuk evaluasi
- Memiliki komandan yang ditunjuk via pleno
- Terhubung ke stok logistik, personel/relawan, dan penugasan

### 1.2 Flow Bisnis End-to-End
```
Pleno → Keputusan Aktivasi Posko
  → Pos Aju Dibuat (direncanakan)
  → Komandan Ditunjuk
  → Pos Aju Diaktifkan
  → Klaster Beroperasi di Pos
  → Distribusi Bantuan per Klaster
  → Feedback Distribusi
  → Sitrep & Jurnal
  → Pos Aju Ditutup (keputusan pleno baru)
```

### 1.3 Stakeholders
| Role | Kepentingan |
|------|------------|
| Super Admin | Full access |
| PWNU | Monitoring provinsi |
| PCNU | Operasional harian |
| TRC | Response lapangan |
| Komandan Pos | Operasional pos |
| Koordinator Klaster | Distribusi bantuan per klaster |

---

## 2. DATA ARCHITECTURE (Source of Truth)

### 2.1 Entity Relationship Diagram (Textual)

```
┌──────────────────────┐
│   operasi_insiden     │
└──────────┬───────────┘
           │
           ├──────────────────────────────────┐
           │                                  │
┌──────────▼───────────┐        ┌─────────────▼──────────┐
│    operasi_posaju     │        │     operasi_pleno      │
│                      │        │                        │
│ PK id_posaju         │        │ PK id_pleno            │
│ FK id_insiden        │        │ FK id_insiden          │
│ FK id_pleno_keputusan│◄───────┤ jenis_pleno (enum)     │
│ FK pj_posaju         │        │ status_pleno (enum)    │
│ nama_posaju          │        └──────────┬─────────────┘
│ latitude, longitude  │                   │
│ alamat_lokasi        │        ┌──────────▼─────────────┐
│ status_alur (enum)   │        │operasi_pleno_keputusan │
│ waktu_diaktifkan     │        │                        │
│ waktu_ditutup        │        │ PK id_keputusan        │
│ alasan_penutupan     │        │ FK id_pleno            │
│ alasan_perpanjangan  │        │ kategori_objek (enum)  │
└──────┬───────────────┘        │ jenis_keputusan (enum) │
       │                        │ tipe_target (enum)     │
       ├──────────────────┐     │ payload_eksekusi (JSON)│
       │                  │     │ referensi_tabel        │
┌──────▼─────────┐ ┌─────▼─────┐│ referensi_id          │
│operasi_posaju  │ │operasi_   ││ status_pelaksanaan    │
│_komandan       │ │penugasan  │└───────────────────────┘
│                │ │           │
│ PK id_komandan │ │PK id_     │
│ FK id_posaju   │ │ penugasan │
│ FK id_pengguna │ │FK id_posaju│
│ FK id_pleno_   │ │FK id_     │
│   keputusan    │ │klaster    │
│ waktu_mulai_   │ │peran_     │
│   tugas        │ │otoritas   │
│ waktu_selesai_ │ │status_    │
│   tugas        │ │penugasan  │
└────────────────┘ └─────┬─────┘
                         │
              ┌──────────┼──────────┐
              │          │          │
     ┌────────▼───┐ ┌───▼────┐ ┌───▼────────┐
     │operasi_    │ │relawan_│ │logistik_   │
     │tugas       │ │kebutuhan│ │stok        │
     │            │ │        │ │            │
     │FK id_posaju│ │FK id_  │ │FK id_posaju│
     │FK id_klaster│ │posaju  │ │(no FK!)    │
     │status_tugas│ │FK id_  │ │jumlah_     │
     │             │ │klaster │ │tersedia    │
     └────────────┘ └────────┘ └──────┬─────┘
                                      │
                             ┌────────▼────────┐
                             │logistik_        │
                             │permintaan       │
                             │                 │
                             │id_posaju_tujuan │
                             │(no FK!)         │
                             │prioritas (enum) │
                             │status_permintaan│
                             └─────────────────┘

┌──────────────────────┐  ┌──────────────────────┐
│   operasi_jurnal      │  │   operasi_aktivasi   │
│                       │  │                      │
│ FK id_insiden        │  │ FK id_komandan (no FK)│
│ kategori_event       │  │ FK id_surat_tugas    │
│ tabel_referensi      │  │   (no FK)             │
│ id_referensi         │  │ status_darurat (enum) │
└──────────────────────┘  └──────────────────────┘
```

### 2.2 Tabel Detail (Source of Truth)

#### 2.2.1 `operasi_posaju` — Core Entity

| Kolom | Tipe | Nullable | Default | FK | Catatan |
|-------|------|----------|---------|----|---------|
| `id_posaju` | bigint unsigned PK | NO | AUTO | — | |
| `id_insiden` | bigint unsigned | YES | NULL | → `operasi_insiden` | |
| `id_periode_operasi` | bigint unsigned | YES | NULL | **TIDAK ADA** | DEAD COLUMN — table tidak exist |
| `id_pleno_pendirian` | bigint unsigned | YES | NULL | **TIDAK ADA** | Seharusnya FK → `operasi_pleno` |
| `id_pleno_keputusan` | bigint unsigned | YES | NULL | → `operasi_pleno_keputusan` | WAJIB diisi secara aplikasi |
| `nama_posaju` | varchar(150) | NO | — | — | |
| `id_surat_pendirian` | bigint unsigned | YES | NULL | **TIDAK ADA** | Seharusnya FK → `operasi_surat_keluar` |
| `alamat_lokasi` | text | YES | NULL | — | |
| `latitude` | decimal(10,8) | YES | NULL | — | WAJIB secara aplikasi |
| `longitude` | decimal(11,8) | YES | NULL | — | WAJIB secara aplikasi |
| `pj_posaju` | bigint unsigned | NO | — | → `auth_users` | |
| `dibuat_pada` | timestamp | NO | CURRENT_TIMESTAMP | — | |
| `status_alur` | **enum** | NO | `direncanakan` | — | Lihat state machine |
| `waktu_diaktifkan` | datetime | YES | NULL | — | |
| `diperpanjang_hingga` | datetime | YES | NULL | — | |
| `waktu_ditutup` | datetime | YES | NULL | — | |
| `alasan_penutupan` | text | YES | NULL | — | |
| `alasan_perpanjangan` | text | YES | NULL | — | |
| `dihapus_pada` | timestamp | YES | NULL | — | Soft delete |

**ENUM `status_alur`:** `direncanakan` → `aktif` → `diperpanjang` → `ditutup`

#### 2.2.2 `operasi_posaju_komandan` — Komandan History

| Kolom | Tipe | Nullable | FK |
|-------|------|----------|----|
| `id_komandan` | bigint unsigned PK | NO | — |
| `id_posaju` | bigint unsigned | NO | → `operasi_posaju` CASCADE |
| `id_pengguna` | bigint unsigned | NO | → `auth_users` CASCADE |
| `id_pleno_keputusan` | bigint unsigned | NO | → `operasi_pleno_keputusan` CASCADE |
| `waktu_mulai_tugas` | datetime | NO | — |
| `waktu_selesai_tugas` | datetime | YES | — |
| `dibuat_pada` | timestamp | NO | — |
| `dihapus_pada` | timestamp | YES | Soft delete |

#### 2.2.3 `operasi_penugasan` — Personel Assignment (Bridge Table)

| Kolom | Tipe | Nullable | FK |
|-------|------|----------|----|
| `id_penugasan` | bigint unsigned PK | NO | — |
| `uuid_penugasan` | char(36) UNIQUE | NO | — |
| `id_insiden` | bigint unsigned | NO | → `operasi_insiden` |
| `id_pengguna` | bigint unsigned | NO | → `auth_users` |
| `id_klaster_operasi` | bigint unsigned | YES | → `operasi_klaster` |
| **`id_posaju`** | bigint unsigned | YES | → `operasi_posaju` |
| `peran_otoritas` | varchar(100) | NO | — |
| `status_penugasan` | varchar(50) | NO | `draft` |
| `waktu_mulai` | datetime | NO | — |
| `waktu_selesai` | datetime | YES | — |
| `ditugaskan_oleh` | bigint unsigned | NO | → `auth_users` |

#### 2.2.4 `operasi_klaster` — Per-Insiden Cluster Activation

| Kolom | Tipe | FK |
|-------|------|----|
| `id_klaster_operasi` | bigint unsigned PK | — |
| `id_insiden` | bigint unsigned | → `operasi_insiden` |
| `id_master_klaster` | bigint unsigned | → `master_klaster` |
| `status_klaster` | varchar(50) | — |
| `prioritas` | varchar(50) | — |
| `progres_persen` | decimal(5,2) | — |
| `waktu_aktivasi` | datetime | — |
| `waktu_ditutup` | datetime | — |

#### 2.2.5 `master_klaster` — Cluster Master Data (8 records)

| ID | Nama | Deskripsi |
|----|------|-----------|
| 1 | Kesehatan | Medis, obat-obatan, trauma healing |
| 2 | Pencarian dan Penyelamatan (SAR) | Evakuasi, pencarian |
| 3 | Logistik | Distribusi bantuan, makanan, peralatan |
| 4 | Pengungsian dan Perlindungan | Tempat berlindung |
| 5 | Pendidikan | Pendidikan darurat |
| 6 | Sarana Prasarana | Infrastruktur |
| 7 | Ekonomi | Pemulihan ekonomi |
| 8 | Pemulihan Dini | Early recovery |

#### 2.2.6 `operasi_tugas` — Specific Tasks in Cluster

| Kolom | Tipe | FK |
|-------|------|----|
| `id_tugas` | bigint unsigned PK | — |
| `id_operasi_klaster` | bigint unsigned | → `operasi_klaster` CASCADE |
| `id_posaju` | bigint unsigned YES | → `operasi_posaju` |
| `judul_tugas` | varchar(255) | — |
| `target_indikator` | varchar(255) YES | — |
| `status_tugas` | ENUM `rencana,berjalan,tertunda,selesai` | — |
| `progres_persen` | decimal(5,2) | — |

#### 2.2.7 `logistik_stok` — Stock at Pos Aju

| Kolom | Tipe | Catatan |
|-------|------|---------|
| `id_stok` | bigint unsigned PK | — |
| **`id_posaju`** | bigint unsigned NOT NULL | **TIDAK ADA FK!** |
| `id_gudang` | bigint unsigned YES | **TIDAK ADA FK!** |
| `id_katalog` | int(11) YES | **TIDAK ADA FK!** |
| `jumlah_tersedia` | decimal(15,2) | — |

#### 2.2.8 `logistik_permintaan` — Logistics Request

| Kolom | Tipe | Catatan |
|-------|------|---------|
| `id_permintaan` | bigint unsigned PK | — |
| `id_operasi_klaster` | bigint unsigned YES | FK → `operasi_klaster` |
| `id_penugasan` | bigint unsigned | FK → `operasi_penugasan` |
| **`id_posaju_tujuan`** | bigint unsigned NOT NULL | **TIDAK ADA FK!** |
| `prioritas` | ENUM `biasa,mendesak, darurat` | — |
| `status_permintaan` | ENUM `draft,diajukan,disetujui,ditolak,dikirim,selesai` | — |

#### 2.2.9 `operasi_jurnal` — Audit Trail

| Kolom | Tipe | Catatan |
|-------|------|---------|
| `id_jurnal` | bigint unsigned PK | — |
| `id_insiden` | bigint unsigned | FK → `operasi_insiden` |
| `id_pengguna` | bigint unsigned YES | — |
| `kategori_event` | varchar(50) | Hanya `aktivasi` saat ini |
| `judul_event` | varchar(255) | — |
| `deskripsi_event` | text YES | — |
| `id_referensi` | bigint unsigned YES | — |
| `tabel_referensi` | varchar(100) YES | — |

#### 2.2.10 `operasi_aktivasi` — Emergency Activation (Orphan Table)

| Kolom | Tipe | Catatan |
|-------|------|---------|
| `id_aktivasi` | bigint unsigned PK | — |
| `id_insiden` | bigint unsigned YES | **TIDAK ADA FK!** |
| `id_komandan` | bigint unsigned NOT NULL | **TIDAK ADA FK!** |
| `id_surat_tugas` | bigint unsigned NOT NULL | **TIDAK ADA FK!** |
| `status_darurat` | ENUM `siaga,tanggap_darurat,pemulihan,selesai` | — |

---

## 3. STATE MACHINES

### 3.1 Pos Aju Lifecycle

```
                    ┌──────────────────┐
                    │   DIRENCANAKAN   │ ◄────── Pos Aju dibuat (manual/pleno)
                    └────────┬─────────┘
                             │
                       [activate] ─── syarat: id_pleno_keputusan WAJIB
                             │
                    ┌────────▼─────────┐
                    │      AKTIF       │
                    └──┬──────────┬────┘
                       │          │
                  [extend]    [close]
                       │          │
              ┌────────▼──┐      │
              │DIPERPANJANG│      │
              └────────┬───┘      │
                       │          │
                  [close]    [close]
                       │          │
                    ┌──▼──────────▼──┐
                    │    DITUTUP     │ ◄────── Final state
                    └────────────────┘
```

**Syarat Transisi:**
| Transisi | From | To | Syarat |
|----------|------|----|--------|
| `activate` | `direncanakan` | `aktif` | `id_pleno_keputusan` terisi, user punya akses |
| `extend` | `aktif` | `diperpanjang` | User punya akses |
| `close` | `aktif`, `diperpanjang` | `ditutup` | User punya akses |

### 3.2 Komandan Lifecycle

```
                    ┌───────────────────────┐
                    │   IDLE (tidak ada)     │
                    └───────────┬───────────┘
                                │
                     [tunjuk via pleno]
                                │
                    ┌───────────▼───────────┐
                    │   BERTUGAS            │
                    │   (waktu_selesai=NULL)│
                    └──┬────────────────┬───┘
                       │                │
                  [akhir tugas]    [ganti komandan]
                       │                │
                    ┌──▼────────┐  ┌────▼──────────┐
                    │ SELESAI   │  │ KOMANDAN BARU │
                    │ TUGAS     │  │ (mulai tugas) │
                    └───────────┘  └───────────────┘
```

### 3.3 Klaster Lifecycle

```
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│ DIACTIVE │───►│  AKTIF   │───►│ DITUTUP  │───►│ SELESAI  │
│ (planned)│    │(running) │    │(inactive)│    │(archived)│
└──────────┘    └──────────┘    └──────────┘    └──────────┘
```

### 3.4 Distribusi Bantuan Lifecycle (NEW — Proposed)

```
┌───────────┐    ┌───────────┐    ┌───────────┐    ┌───────────┐
│ DIRENCANA │───►│ DIDISTRIB │───►│ DITERIMA  │───►│ DIREVIEW  │
│ NAKAN     │    │ USIKAN    │    │ (konfirm) │    │ (feedback) │
└───────────┘    └───────────┘    └───────────┘    └───────────┘
```

---

## 4. BUSINESS RULES MATRIX

| ID | Rule | Level | Status | Source |
|----|------|-------|--------|--------|
| BR-POSAJU-001 | Pos aju hanya dapat diaktifkan jika memiliki `id_pleno_keputusan` valid | WAJIB (APP) | ✅ FIXED | Domain Rules |
| BR-POSAJU-002 | Setiap komandan HARUS memiliki `id_pleno_keputusan` valid | WAJIB (DB) | ✅ DB ENFORCED | Domain Rules |
| BR-POSAJU-003 | Stok logistik di pos aju ditrack via `logistik_stok.id_posaju` | WAJIB | ⚠️ NO FK | Domain Rules |
| BR-POSAJU-004 | Pos aju `ditutup` tidak dapat diaktifkan kembali | WAJIB | ✅ DONE | Domain Rules |
| BR-POSAJU-005 | Koordinat GPS WAJIB diisi | WAJIB (APP) | ✅ FIXED | Domain Rules |
| BR-POSAJU-006 | Scope stok sesuai scope insiden | WAJIB | ⚠️ NOT IMPL | Domain Rules |
| BR-PLENO-007 | Pembukaan pos aju baru WAJIB memiliki `id_pleno_keputusan` | WAJIB | ✅ FIXED | Domain Rules |
| BR-JURNAL-005 | Aktivasi pos aju & penugasan komandan harus tercatat di jurnal | WAJIB | ❌ NOT IMPL | Domain Rules |
| BR-FEEDBACK-001 | Feedback evaluasi retrospektif response | SARAN | ❌ NOT IMPL | Domain Rules |
| BR-FEEDBACK-002 | Hanya koordinator terdaftar bisa isi feedback | WAJIB | ❌ NOT IMPL | Domain Rules |
| BR-FEEDBACK-003 | Feedback gap suplai-demand harus menghasilkan gap record | WAJIB | ❌ NOT IMPL | Domain Rules |
| BR-FEEDBACK-004 | Feedback final = terkunci | WAJIB | ❌ NOT IMPL | Domain Rules |

---

## 5. FUNCTIONAL MODULES

### Module A: Core Pos Aju Management
**Priority:** P0 — KRITIS

#### A.1 CRUD Pos Aju
- **Create:** Form untuk buat pos aju baru (manual)
  - Input: nama, insiden (dropdown), keputusan pleno (dropdown, filter: `aktivasi_posko`), latitude, longitude, alamat, PJ
  - Validasi: nama required, insiden required, `id_pleno_keputusan` required, lat/lng required
  - Post-create: redirect ke show page
  - Event: jurnal "Pos Aju Dibuat"
- **Read/List:** Tabel dengan filter status + search
  - Columns: nama, insiden, PJ, status, waktu diaktifkan, waktu ditutup, aksi
  - Filter by: status_alur, insiden
  - Pagination (15/page)
  - Eager load: insiden, PJ, komandanAktif
- **Show:** Detail page dengan tabs: Info, Stok, Personel, Distribusi, Feedback, Peta
- **Update:** Edit nama, alamat, PJ, koordinat
- **Delete:** Soft delete (hanya jika status `direncanakan`)

#### A.2 Status Transitions (activate/extend/close)
- **Activate:** `direncanakan` → `aktif`
  - Guard: `id_pleno_keputusan` WAJIB terisi
  - Set: `waktu_diaktifkan = now()`
  - Event: jurnal "Pos Aju Diaktifkan"
- **Extend:** `aktif` → `diperpanjang`
  - Input: `diperpanjang_hingga` (datetime), `alasan_perpanjangan`
  - Set: `diperpanjang_hingga`, `alasan_perpanjangan`
  - Event: jurnal "Pos Aju Diperpanjang"
- **Close:** `aktif`/`diperpanjang` → `ditutup`
  - Input: `alasan_penutupan`
  - Set: `waktu_ditutup = now()`
  - Guard: cek semua penugasan aktif sudah selesai
  - Event: jurnal "Pos Aju Ditutup"

#### A.3 Pleno-Driven Auto Creation
- Saat keputusan pleno `aktivasi_posko` dibuat:
  - Buat pos aju dengan `status_alur = 'aktif'` (immediate active)
  - Set `id_pleno_keputusan`, `id_pleno_pendirian`
  - Buat komandan dari `payload.id_koordinator`
  - Buat penugasan untuk koordinator (`peran_otoritas = 'koordinator_pos'`)
  - Generate surat tugas via NomorSuratService
  - Event: jurnal "Pos Aju Dibuat via Pleno"
- Guard: pleno harus status `final` untuk event listener, immediate execute di store()

---

### Module B: Komandan Management
**Priority:** P0 — KRITIS

#### B.1 Penunjukan Komandan
- Via keputusan pleno (`id_pleno_keputusan` WAJIB)
- End komandan sebelumnya: set `waktu_selesai_tugas = now()`
- Create baru: `waktu_mulai_tugas = now()`
- Update `pj_posaju` di `operasi_posaju`
- Event: jurnal "Komandan Pos Aju Ditunjuk"

#### B.2 Akhiri Tugas Komandan
- Set `waktu_selesai_tugas = now()`
- Guard: hanya jika ada komandan aktif
- Event: jurnal "Tugas Komandan Berakhir"

#### B.3 Riwayat Komandan
- View all: filter by pos aju
- Status aktif/selesai
- Link ke profil pengguna
- Link ke keputusan pleno penunjukan

---

### Module C: Klaster & Penugasan di Pos Aju
**Priority:** P1 — TINGGI

#### C.1 Aktivasi Klaster di Pos Aju
- Klaster (SAR, Medis, Logistik, dll) diaktifkan di suatu pos aju
- Tabel `operasi_klaster` sudah ada dengan `id_insiden`
- Perlu link `operasi_klaster` → `operasi_posaju` (via `id_posaju` di klaster atau pivot)
- Setiap klaster punya: koordinator, progres, target cakupan

#### C.2 Penugasan Personel ke Pos Aju via Klaster
- `operasi_penugasan` sebagai bridge: personel → pos aju + klaster
- Personel bisa ditugaskan ke:
  - Pos aju langsung (`id_posaju` terisi)
  - Klaster (`id_klaster_operasi` terisi)
  - Keduanya
- Sync dengan `relawan_penugasan` untuk relawan

#### C.3 Tugas dalam Klaster
- `operasi_tugas` dengan FK ke klaster & pos aju
- Track progres (0-100%)
- Status: rencana → berjalan → tertunda → selesai
- Target indikator terukur

---

### Module D: Distribusi Bantuan (NEW — Proposed Schema)
**Priority:** P1 — TINGGI

#### D.1 Tabel Baru: `operasi_distribusi`

| Kolom | Tipe | FK | Keterangan |
|-------|------|----|------------|
| `id_distribusi` | bigint unsigned PK | — | |
| `uuid_distribusi` | char(36) UNIQUE | — | |
| `id_posaju` | bigint unsigned NOT NULL | → `operasi_posaju` | Pos asal distribusi |
| `id_klaster_operasi` | bigint unsigned NOT NULL | → `operasi_klaster` | Klaster pelaksana |
| `id_penugasan` | bigint unsigned YES | → `operasi_penugasan` | Petugas pelaksana |
| `id_barang_katalog` | int(11) YES | → `logistik_barang_katalog` | Barang didistribusi |
| `nama_barang` | varchar(255) NOT NULL | — | Nama jika bukan dari katalog |
| `jumlah` | decimal(15,2) NOT NULL | — | Jumlah didistribusi |
| `satuan` | varchar(50) NOT NULL | — | |
| `lokasi_tujuan` | text YES | — | Lokasi penerima |
| `penerima` | varchar(255) YES | — | Nama/kelompok penerima |
| `waktu_distribusi` | datetime NOT NULL | — | |
| `status_distribusi` | ENUM `direncanakan,didistribusikan,diterima,direview` | — | `direncanakan` |
| `dibuat_oleh` | bigint unsigned | → `auth_users` | |
| `dibuat_pada` | timestamp | — | |
| `diperbarui_pada` | timestamp | — | |
| `dihapus_pada` | timestamp | — | Soft delete |

#### D.2 Tabel Baru: `operasi_feedback_distribusi`

| Kolom | Tipe | FK | Keterangan |
|-------|------|----|------------|
| `id_feedback` | bigint unsigned PK | — | |
| `id_distribusi` | bigint unsigned NOT NULL | → `operasi_distribusi` | |
| `id_pengguna` | bigint unsigned NOT NULL | → `auth_users` | Koordinator/komandan |
| `kecukupan` | ENUM `kurang,cukup,berlebih` NOT NULL | — | |
| `kualitas` | ENUM `baik,sedang,buruk` NOT NULL | — | |
| `tepat_waktu` | boolean NOT NULL | — | |
| `tepat_sasaran` | boolean NOT NULL | — | |
| `kendala` | text YES | — | |
| `rekomendasi` | text YES | — | |
| `status_feedback` | ENUM `draft,final` NOT NULL | — | `draft` |
| `dibuat_pada` | timestamp | — | |
| `dikunci_pada` | timestamp YES | — | Saat final |

#### D.3 Alur Distribusi Bantuan
```
1. Koordinator klaster merencanakan distribusi
2. Input: barang, jumlah, lokasi tujuan, penerima, waktu
3. Status: 'direncanakan'
4. Setelah distribusi fisik → status: 'didistribusikan'
5. Konfirmasi penerimaan → status: 'diterima'
6. Feedback dari koordinator/komandan → status: 'direview'
7. Final → terkunci
```

---

### Module E: Logistik Integration
**Priority:** P1 — TINGGI

#### E.1 Perbaiki FK Constraints
- `logistik_stok.id_posaju` → add FK ke `operasi_posaju`
- `logistik_permintaan.id_posaju_tujuan` → add FK ke `operasi_posaju`

#### E.2 Stok di Pos Aju
- View stok di halaman show pos aju
- Mutasi stok: masuk/keluar/penyesuaian
- Minimum stok alert

#### E.3 Permintaan Logistik dari Pos
- Pos aju bisa request logistik
- Approve/reject workflow
- Track status: draft → diajukan → disetujui/ditolak → dikirim → selesai

---

### Module F: Relawan Integration
**Priority:** P2 — NORMAL

#### F.1 Kebutuhan Relawan per Pos
- `relawan_kebutuhan` dengan `id_posaju`
- Form: jumlah dibutuhkan, keahlian, periode tugas
- Status rekrutmen: dibuka → terpenuhi → ditutup

#### F.2 Penugasan Relawan
- `relawan_penugasan` dengan `id_posaju`
- Relawan diassign ke pos aju spesifik
- Track masa tugas

---

### Module G: Jurnal / Audit Trail
**Priority:** P2 — NORMAL

#### G.1 Event Jurnal Pos Aju
Tambahkan kategori_event baru di `operasi_jurnal`:
| Kategori | Event |
|----------|-------|
| `posaju_dibuat` | Pos aju dibuat manual/via pleno |
| `posaju_diaktifkan` | Status → aktif |
| `posaju_diperpanjang` | Status → diperpanjang |
| `posaju_ditutup` | Status → ditutup |
| `komandan_ditunjuk` | Komandan baru ditunjuk |
| `komandan_berakhir` | Tugas komandan selesai |
| `distribusi_dibuat` | Rencana distribusi bantuan |
| `distribusi_dikirim` | Bantuan didistribusikan |
| `distribusi_direview` | Feedback distribusi diisi |

#### G.2 Integrasi Jurnal
- Setiap action di controller/listener/service → catat jurnal
- `tabel_referensi` = `operasi_posaju`
- `id_referensi` = `id_posaju` terkait

---

### Module H: Map Integration
**Priority:** P2 — NORMAL

#### H.1 Leaflet Map di Show Page ✅ DONE
- Tampilkan marker pos aju
- Popup: nama pos aju

#### H.2 Command Center Map (NEW)
- Endpoint: `GET /api/insiden/{id}/posaju/aktif` ✅ DONE
- Tampilkan semua pos aju aktif di peta
- Filter by klaster marker

#### H.3 Input Koordinat (NEW)
- Drag marker untuk set koordinat
- Reverse geocoding untuk alamat

---

### Module I: API Design
**Priority:** P1 — TINGGI

#### I.1 Web Routes (Existing)
| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | `/posaju` | `posaju.index` | PosAjuWebController@index |
| GET | `/posaju/buat` | `posaju.create` | PosAjuWebController@create |
| POST | `/posaju` | `posaju.store` | PosAjuWebController@store |
| GET | `/posaju/{posaju}` | `posaju.show` | PosAjuWebController@show |
| PATCH | `/posaju/{posaju}/activate` | `posaju.activate` | PosAjuWebController@activate |
| PATCH | `/posaju/{posaju}/extend` | `posaju.extend` | PosAjuWebController@extend |
| PATCH | `/posaju/{posaju}/close` | `posaju.close` | PosAjuWebController@close |
| PATCH | `/posaju/{posaju}/tutup` | `posaju.tutup` | PosAjuWebController@tutup |
| POST | `/posaju/{posaju}/komandan` | `posaju.komandan.store` | PosajuKomandanController@store |
| DELETE | `/posaju/{posaju}/komandan/{komandan}` | `posaju.komandan.destroy` | PosajuKomandanController@destroy |

#### I.2 Scoped Routes (Under Insiden)
| Method | URI | Name |
|--------|-----|------|
| GET | `/insiden/{insiden}/posaju` | `insiden.posaju.index` |
| GET | `/insiden/{insiden}/posaju/buat` | `insiden.posaju.create` |
| POST | `/insiden/{insiden}/posaju` | `insiden.posaju.store` |
| GET | `/insiden/{insiden}/posaju/{posaju}` | `insiden.posaju.show` |
| PATCH | `/insiden/{insiden}/posaju/{posaju}/activate` | `insiden.posaju.activate` |
| PATCH | `/insiden/{insiden}/posaju/{posaju}/tutup` | `insiden.posaju.tutup` |
| PATCH | `/insiden/{insiden}/posaju/{posaju}/extend` | `insiden.posaju.extend` |
| POST | `/insiden/{insiden}/posaju/{posaju}/komandan` | `insiden.posaju.komandan.store` |
| DELETE | `/insiden/{insiden}/posaju/{posaju}/komandan/{komandan}` | `insiden.posaju.komandan.destroy` |

#### I.3 API Routes (Sanctum)
| Method | URI | Name |
|--------|-----|------|
| GET | `/api/operasi/posaju` | `api.operasi.posaju.index` |
| POST | `/api/operasi/posaju` | `api.operasi.posaju.store` |
| GET | `/api/operasi/posaju/{posaju}` | `api.operasi.posaju.show` |
| PUT | `/api/operasi/posaju/{posaju}` | `api.operasi.posaju.update` |
| POST | `/api/operasi/posaju/{posaju}/activate` | `api.operasi.posaju.activate` |
| POST | `/api/operasi/posaju/{posaju}/extend` | `api.operasi.posaju.extend` |
| POST | `/api/operasi/posaju/{posaju}/close` | `api.operasi.posaju.close` |
| GET | `/api/operasi/insiden/{insiden}/posaju/aktif` | `api.operasi.posaju.active-by-insiden` |

#### I.4 API Resource Fields
```json
{
  "id_posaju": 4,
  "nama_posaju": "Pos Aju Pusat",
  "insiden": { "id_insiden": 37, "kode_kejadian": "INS-2607-01-37816" },
  "pj": { "id_pengguna": 8, "nama": "Admin PCNU" },
  "komandan_aktif": { "id_komandan": 1, "nama": "Komandan" },
  "latitude": -6.2088,
  "longitude": 106.8456,
  "alamat_lokasi": "Gedung PCNU",
  "status_alur": "aktif",
  "status_label": "Aktif",
  "waktu_diaktifkan": "2026-07-14T04:25:47",
  "waktu_ditutup": null,
  "jumlah_stok": 0,
  "jumlah_personel": 0
}
```

---

### Module J: Security & Authorization
**Priority:** P0 — KRITIS

#### J.1 Policy Methods
| Method | Checks |
|--------|--------|
| `viewAny` | Role: super_admin, pwnu, pcnu |
| `view` | `canManageInsiden(user, posaju.insiden)` |
| `create` | Role check ✅ + **locked incident guard (DIRENCANAKAN)** |
| `update` | `canManageInsiden` |
| `activate` | Not closed + not already active + `canManageInsiden` + **`id_pleno_keputusan` terisi** |
| `extend` | Not closed + is active + `canManageInsiden` |
| `close` | Not closed + is active/extended + `canManageInsiden` |
| `tambahKomandan` | Not closed + `canManageInsiden` |

#### J.2 Create Guard: Locked Incident
**GAP SAAT INI:** Policy `create()` tidak memeriksa apakah insiden terkunci.
**FIX:** Pass insiden ke policy:
```php
public function create(AuthUser $user, ?OperasiInsiden $insiden = null): bool
{
    if ($insiden && $insiden->is_locked) return false;
    return $this->authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']);
}
```

---

## 6. NON-FUNCTIONAL REQUIREMENTS

### 6.1 Performance
- List page: < 500ms dengan 1000 pos aju
- Show page: < 300ms dengan eager loading
- API responses: < 200ms

### 6.2 Security
- All endpoints: auth:sanctum (API) / auth (web)
- Role-based access via Policy
- Soft delete (no hard delete)
- Audit trail via operasi_jurnal

### 6.3 Data Integrity
- All FKs: proper constraints (fix missing FKs)
- Soft delete: `dihapus_pada` column
- Timestamps: `dibuat_pada`, `diperbarui_pada`
- UUID for sync: `uuid_penugasan`, `uuid_mutasi`, etc
- Versioning: `sync_version` for conflict resolution

### 6.4 UI/UX
- Responsive (Tailwind CSS)
- Tab-based layout: Info, Stok, Personel, Distribusi, Feedback
- Leaflet.js map
- Toast notifications for success/error
- Form validation with inline errors

---

## 7. INTEGRATION MAP

```
┌───────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  Assessment   │     │  Pleno (M07)     │     │  Sitrep (M?)    │
│  (M05-M06)    │     │  ── keputusan ──►│◄────│  ── data ──────►│
│               │     │  aktivasi_posko  │     │  personel/stok  │
└───────────────┘     └────────┬─────────┘     └─────────────────┘
                               │
                               ▼
                    ┌──────────────────┐
                    │   POS AJU (M15)  │◄──── Jurnal (M17)
                    └──┬────┬────┬────┘
                       │    │    │
            ┌──────────┘    │    └──────────┐
            ▼               ▼               ▼
    ┌────────────┐  ┌────────────┐  ┌──────────────┐
    │  Logistik  │  │  Relawan   │  │  Surat Tugas │
    │  (M12)     │  │  (M13-M14) │  │  (M?)        │
    │  stok      │  │  penugasan │  │  penerbitan  │
    │  permintaan│  │  kebutuhan │  │  otomatis    │
    └────────────┘  └────────────┘  └──────────────┘
```

---

## 8. TECHNICAL DEBT / GAPS

| Gap | Module | Dampak | Prioritas |
|-----|--------|--------|-----------|
| `logistik_stok.id_posaju` tanpa FK | E | Data integrity | 🔴 Segera |
| `logistik_permintaan.id_posaju_tujuan` tanpa FK | E | Data integrity | 🔴 Segera |
| `operasi_aktivasi` orphan table | A | Dead code/confusion | 🟡 Sedang |
| `operasi_posaju.id_periode_operasi` dead column | A | Cleanup | 🟢 Nanti |
| `operasi_posaju.id_pleno_pendirian` tanpa FK | A | Lost reference | 🟡 Sedang |
| `operasi_posaju.id_surat_pendirian` tanpa FK | A | Lost reference | 🟡 Sedang |
| Policy `create()` tidak cek locked insiden | J | Security gap | 🟠 Segera |
| Jurnal tidak catat event pos aju | G | Audit gap | 🟡 Sedang |
| Distribusi bantuan + feedback belum ada | D | Feature gap | 🟠 Segera |

---

## 9. CLOSING

Dokumen ini adalah **source of truth** untuk pengembangan domain Pos Aju. Setiap perubahan pada kode harus merujuk pada:

1. **MySQL Schema** — Struktur tabel yang sudah ada
2. **ARD Ini** — Business rules, state machines, modul
3. **DOMAIN_RULES.md** — Aturan domain
4. **IMPLEMENTATION_BACKLOG.md** — Prioritas backlog

Perubahan pada schema harus melalui migration, dengan dokumentasi yang jelas.
