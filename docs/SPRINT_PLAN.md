# SPRINT_PLAN.md — NURISK
# Roadmap Implementasi Sprint — Senior Engineering Manager

> Versi: 1.0 — Tanggal: 16 Juni 2026
> Sumber Kebenaran: SQL Dump v37 Frozen Final (100 tabel, 15 trigger, 10 view)
>
> ⚠️ **KOREKSI PENTING DARI AUDIT SQL MENDALAM:**
> Dokumen pra-produksi sebelumnya mengandung beberapa asumsi keliru karena
> SQL dump belum diaudit penuh. Dokumen ini berdasarkan fakta aktual dari SQL.
>
> **Koreksi utama:**
> | Item | Asumsi Lama (SALAH) | Fakta SQL (BENAR) |
> |------|---------------------|-------------------|
> | Tabel jabatan | `master_jabatan` | `master_jabatan` |
> | Tabel surat utama | `operasi_surat_keluar` | `operasi_surat_keluar` |
> | Feedback klaster | Tidak ada | `operasi_klaster_feedback` ✅ ADA |
> | Gap kebutuhan | Tidak ada | `operasi_gap_kebutuhan` ✅ ADA |
> | Pengungsian | Tidak ada | `operasi_pos_pengungsian` + `pengungsian_sensus_harian` ✅ ADA |
> | Organisasi | Hanya enum | `organisasi_pcnu`, `organisasi_mwc`, `organisasi_ranting`, `organisasi_unit` ✅ ADA |
> | Wilayah | Tidak ada | `wilayah_kabupaten`, `wilayah_kecamatan`, `wilayah_desa` ✅ ADA |
> | Shift personel | Tidak ada | `operasi_shift_personel` ✅ ADA |
> | Kebutuhan relawan | Tidak ada | `relawan_kebutuhan` ✅ ADA |
> | Tugas operasi | Tidak ada | `operasi_tugas` ✅ ADA |
> | Sistem audit | Tidak ada | `sistem_audit_log`, `sistem_log_aktivitas`, `sistem_log_alur_kerja` ✅ ADA |

---

## RINGKASAN SPRINT

| Sprint | Nama | Durasi | Status |
|--------|------|--------|--------|
| S01 | Autentikasi & Authorization | 2 minggu | ⬜ |
| S02 | Organisasi & Wilayah | 2 minggu | ⬜ |
| S03 | Insiden & Laporan Kejadian | 2 minggu | ⬜ |
| S04 | Assessment | 1 minggu | ⬜ |
| S05 | Sitrep | 1 minggu | ⬜ |
| S06 | Pos Aju & Pengungsian | 2 minggu | ⬜ |
| S07 | Logistik | 2 minggu | ⬜ |
| S08 | Relawan & Tugas Operasi | 2 minggu | ⬜ |
| S09 | Pleno & Eskalasi | 2 minggu | ⬜ |
| S10 | Surat Menyurat | 2 minggu | ⬜ |
| S11 | Feedback Klaster & Gap Kebutuhan | 1 minggu | ⬜ |
| S12 | Dashboard & Command Center | 2 minggu | ⬜ |

**Total estimasi: ~22 minggu** (~5,5 bulan development)

---

---

## SPRINT 1 — Autentikasi & Authorization

### 1. Tujuan Sprint
Membangun fondasi sistem: login, registrasi, manajemen akun, dan infrastruktur authorization 4-lapis. Setelah sprint ini, seluruh role dapat login dan sistem mengenali scope wilayah masing-masing.

### 2. Modul yang Dikerjakan

**A. Auth Core**

Tabel:
- `auth_users` — akun utama, login via `no_hp` + `kata_sandi`
- `auth_roles` — 5 role PRD (super_admin, pwnu, pcnu, relawan, publik)
- `auth_pengguna_profil` — profil: `nik`, `nama_lengkap`, `email`
- `auth_keahlian_master` — master 7 keahlian
- `auth_pengguna_keahlian` — pivot pengguna ↔ keahlian
- `model_has_roles`, `model_has_permissions`, `roles`, `permissions` — Spatie

Kritis:
- PK: `id_pengguna` (bukan `id`)
- Login: kolom `no_hp` (bukan `email`)
- Password: kolom `kata_sandi` (bukan `password`)
- Timestamps: `dibuat_pada`/`diperbarui_pada`
- Trigger `tr_sync_user_role_insert` dan `tr_sync_user_role_update` — sync ke `model_has_roles`
- `status_akun` ENUM: `menunggu`, `aktif`, `nonaktif`, `suspend`

**B. Authorization Infrastructure**

Tabel:
- `master_jabatan` — jabatan struktural (PK: `id_master_jabatan`, bukan `id_jabatan`)
- `pengguna_jabatan` — mapping user ke jabatan dengan `tipe_lingkup` dan `id_lingkup`
- `sistem_log_aktivitas` — audit trail login/logout
- `sistem_audit_log` — audit INSERT/UPDATE/DELETE

Kritis:
- `master_jabatan.id_master_jabatan` adalah PK (integer, bukan `id_jabatan`)
- `pengguna_jabatan` menggunakan `id_master_jabatan` sebagai FK (bukan `id_jabatan`)
- `pengguna_jabatan.tipe_lingkup` dan `id_lingkup` menentukan jabatan struktural per wilayah
- `sistem_log_aktivitas` digunakan untuk audit trail umum (bukan `sistem_audit_log`)

### 3. Deliverable
- [ ] Login via `no_hp` + `kata_sandi` berhasil
- [ ] Register publik → status `menunggu`, aktivasi oleh super_admin/pwnu
- [ ] Redirect post-login berbeda per role
- [ ] Middleware `check.akun.aktif` memblokir akun non-aktif
- [ ] 5 Gate Authorization terdefinisi di `AuthServiceProvider`
- [ ] `BaseNuriskPolicy` dengan helper `isSuperAdmin()`, `isPwnu()`, `isPcnu()`, `cekScopeWilayah()`
- [ ] CRUD `master_jabatan` oleh super_admin
- [ ] CRUD `pengguna_jabatan` oleh super_admin/pwnu
- [ ] Panel manajemen user (index, show, edit, aktivasi, suspend) untuk super_admin
- [ ] Layout `app.blade.php` + sidebar kondisional per role
- [ ] Trigger `tr_sync_user_role_insert/update` berfungsi dan diverifikasi
- [ ] `sistem_log_aktivitas`: setiap login/logout dicatat

### 4. Risiko
- **Tinggi**: Override `getAuthPassword()` dan `getAuthIdentifierName()` di `AuthUser` — harus tepat agar Laravel Auth bekerja
- **Tinggi**: Spatie Permission menggunakan tabel `model_has_roles` — trigger DB sudah populate, pastikan tidak konflik dengan Spatie
- **Sedang**: Kolom `id_master_jabatan` (bukan `id_jabatan`) di `master_jabatan` — rentan typo di semua referensi FK
- **Sedang**: `pengguna_jabatan.tipe_lingkup` + `id_lingkup` lebih kompleks dari asumsi awal

### 5. Dependensi
- Tidak ada dependensi modul sebelumnya
- Paket: `spatie/laravel-permission`
- Database MySQL production + testing sudah dikonfigurasi

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=Auth` — semua test passing di MySQL
- [ ] Login berhasil untuk semua 5 role
- [ ] Akun non-aktif ditolak dengan pesan yang benar
- [ ] Trigger `tr_sync_user_role_insert` diverifikasi via DB test
- [ ] Gate `escalate-insiden`, `finalize-pleno`, `finalize-sitrep` mengembalikan hasil benar
- [ ] `BaseNuriskPolicy::cekScopeWilayah()` diuji untuk semua kombinasi role
- [ ] Tidak ada `created_at`/`updated_at`/`password` tersisa di kode

---

## SPRINT 2 — Organisasi & Wilayah

### 1. Tujuan Sprint
Membangun hierarki organisasi NU (PWNU → PCNU → MWC → Ranting → Unit) dan data wilayah administratif (Kabupaten → Kecamatan → Desa). Ini adalah fondasi scope wilayah seluruh sistem.

### 2. Modul yang Dikerjakan

**A. Organisasi Hierarki**

Tabel (semuanya sudah ada di SQL dump, ada seed data):
- `organisasi_unit` — entitas induk: PK `id_unit`, kolom `parent_id`, `nama_unit`, `tipe_unit` ENUM(`pwnu`,`pcnu`,`mwc`,`ranting`,`lembaga`,`banom`), `id_wilayah`
- `organisasi_pcnu` — cabang PCNU: `id_pcnu`, `id_unit` (FK ke `organisasi_unit`), `nama_pcnu`
- `organisasi_mwc` — MWC: `id_mwc`, `id_pcnu`, `nama_mwc`, `id_unit`
- `organisasi_ranting` — Ranting: (struktur perlu dikonfirmasi dari SQL)

Kritis:
- `organisasi_unit.tipe_unit` adalah ENUM yang sama dengan `auth_users.default_scope_type`
- `auth_users.id_unit` FK ke `organisasi_unit.id_unit` — ini kunci scope wilayah
- Seed data sudah ada di SQL dump untuk `organisasi_pcnu` dan `organisasi_unit`

**B. Wilayah Administratif**

Tabel (semuanya sudah ada di SQL dump, ada seed data):
- `wilayah_kabupaten` — PK `id_kab` CHAR(4), `nama_kab`, `tipe` ENUM(`Kabupaten`,`Kota`)
- `wilayah_kecamatan` — FK ke `wilayah_kabupaten`
- `wilayah_desa` — FK ke `wilayah_kecamatan`

Kritis:
- Wilayah adalah referensi administratif KEMENDAGRI, BUKAN wilayah organisasi NU
- `auth_pengguna_profil.id_desa_domisili` FK ke `wilayah_desa`
- `organisasi_unit.id_wilayah` FK ke wilayah (kabupaten/kecamatan)
- Data seed sudah ada (wilayah Jawa Tengah)

**C. Integrasi Auth ↔ Organisasi**

- Update `AuthUser` untuk menyertakan relasi ke `organisasi_unit`
- Helper `cekScopeWilayah()` menggunakan `organisasi_pcnu.id_pcnu` (bukan enum saja)
- Dropdown organisasi di form register dan manajemen user

### 3. Deliverable
- [ ] Migration semua tabel organisasi (jika belum ada dari SQL import)
- [ ] Seeder `OrganisasiSeeder`: mengisi `organisasi_unit`, `organisasi_pcnu`, `organisasi_mwc` dari data SQL
- [ ] Seeder `WilayahSeeder`: mengisi `wilayah_kabupaten`, `wilayah_kecamatan`, `wilayah_desa`
- [ ] Model `OrganisasiUnit`, `OrganisasiPcnu`, `OrganisasiMwc` dengan relasi hierarki
- [ ] Model `WilayahKabupaten`, `WilayahKecamatan`, `WilayahDesa` dengan relasi bertingkat
- [ ] `AuthUser::organisasiUnit()` relasi ke `organisasi_unit`
- [ ] `AuthUser::pcnu()` helper method → `organisasi_pcnu` via `organisasi_unit`
- [ ] Panel admin: tampilan hierarki organisasi (read-only untuk pcnu, editable untuk super_admin)
- [ ] Dropdown wilayah bertingkat di form profil pengguna (kab → kec → desa, AJAX)
- [ ] API: `/api/wilayah/kabupaten`, `/api/wilayah/{kab}/kecamatan`, `/api/wilayah/{kec}/desa`
- [ ] `cekScopeWilayah()` diperbarui menggunakan join ke `organisasi_pcnu` (bukan enum saja)

### 4. Risiko
- **Tinggi**: Data seed wilayah sangat besar (ribuan desa) — perlu batch insert atau load dari SQL langsung
- **Sedang**: `organisasi_unit.parent_id` self-referential — rekursi hierarki bisa kompleks
- **Sedang**: Konsistensi `auth_users.default_scope_type` dengan `organisasi_unit.tipe_unit` harus dijaga
- **Rendah**: Dropdown 3-tingkat (kab-kec-desa) memerlukan AJAX — tidak boleh load semua sekaligus

### 5. Dependensi
- **S01** (Auth) — wajib selesai, karena `auth_users.id_unit` FK ke `organisasi_unit`

### 6. Kriteria Selesai
- [ ] `php artisan db:seed --class=OrganisasiSeeder` berhasil
- [ ] `php artisan db:seed --class=WilayahSeeder` berhasil tanpa timeout
- [ ] Dropdown kab→kec→desa berfungsi via AJAX (test manual)
- [ ] Hierarki organisasi PWNU→PCNU→MWC tampil benar
- [ ] `AuthUser::pcnu()` mengembalikan nama PCNU yang benar
- [ ] `php artisan test --filter=Organisasi` — semua passing

---

## SPRINT 3 — Insiden & Laporan Kejadian

### 1. Tujuan Sprint
Membangun domain inti: pelaporan publik kejadian bencana, pembentukan insiden resmi, manajemen status insiden beserta seluruh business rule dan trigger database yang melindunginya.

### 2. Modul yang Dikerjakan

**A. Laporan Kejadian (Publik)**

Tabel:
- `laporan_kejadian` — PK `id_laporan_kejadian`; kolom: `nama_pelapor`, `hp_pelapor`, `waktu_kejadian`, `latitude`, `longitude`, `is_valid` ENUM(`menunggu`,`ya`,`tidak`)
- `sistem_file_media` — lampiran foto laporan (polymorphic: `tipe_entitas='laporan_kejadian'`)

Trigger aktif:
- `tr_validate_coords_laporan` — koordinat di luar batas Indonesia → SIGNAL error

**B. Insiden Resmi**

Tabel:
- `operasi_insiden` — PK `id_insiden`; `kode_kejadian` VARCHAR(25) UNIQUE; `status_insiden` ENUM(6 nilai); `status_operasi` ENUM(5 nilai); `is_locked` TINYINT(1)
- `riwayat_status_insiden` — histori transisi
- `sistem_transisi_status` — generic status transition log (berlaku untuk semua entitas)
- `operasi_jurnal` — catatan naratif event operasional

Trigger aktif:
- `tr_validate_temporal_incident` — `waktu_selesai < waktu_mulai` → SIGNAL error
- `tr_lock_incident_data` — `is_locked=1` → UPDATE ditolak DB

**C. Audit Infrastructure**

Tabel:
- `sistem_transisi_status` — log generic untuk semua transisi status di sistem
- `operasi_jurnal` — catatan naratif human-readable per insiden

Catatan: `sistem_transisi_status` digunakan untuk mencatat transisi status SEMUA entitas (insiden, sitrep, pleno, surat, dll). Setiap `InsidenService::transisiStatus()` WAJIB INSERT ke sini.

### 3. Deliverable
- [ ] Migration `laporan_kejadian`, `operasi_insiden`, `riwayat_status_insiden`, `sistem_transisi_status`, `operasi_jurnal`
- [ ] Model: `LaporanKejadian`, `OperasiInsiden`, `RiwayatStatusInsiden`, `SistemTransisiStatus`, `OperasiJurnal`
- [ ] `kode_kejadian` auto-generate format `INS-YYYYMM-{seq}` di Service
- [ ] `InsidenService::transisiStatus()` dalam `DB::transaction()`:
  - Update `status_insiden` + kolom waktu terkait
  - Set `is_locked=1` saat status `selesai`
  - INSERT ke `riwayat_status_insiden`
  - INSERT ke `sistem_transisi_status` (tipe_entitas='operasi_insiden')
  - INSERT ke `operasi_jurnal` via `JurnalService`
- [ ] `JurnalService::catat()` tersedia dan digunakan oleh semua service
- [ ] `SistemFileMedia` model + upload helper (polymorphic)
- [ ] Form laporan publik dengan peta Leaflet klik-untuk-koordinat
- [ ] Halaman insiden: index, show (tab-based), create, edit
- [ ] Halaman laporan internal: index (menunggu validasi), show + tombol validasi/tolak
- [ ] Scope PCNU berjalan di semua query insiden
- [ ] Trigger `tr_lock_incident_data` diverifikasi via DB test

### 4. Risiko
- **Tinggi**: `sistem_transisi_status` adalah tabel GENERIC — setiap service domain wajib menulis ke sini, bukan hanya insiden. Harus didesain agar mudah digunakan kembali.
- **Tinggi**: `operasi_jurnal` dan `sistem_transisi_status` keduanya mencatat transisi — definisikan yang mana untuk apa: `sistem_transisi_status` = machine log (tipe_entitas + id), `operasi_jurnal` = human narrative
- **Sedang**: Trigger `tr_lock_incident_data` — jika Laravel mencoba UPDATE setelah lock, akan throw `QueryException`. Harus ditangani dengan try-catch di Service.
- **Rendah**: Foto laporan di `sistem_file_media` — pastikan validasi mime + ukuran ketat

### 5. Dependensi
- **S01** (Auth), **S02** (Organisasi — untuk `id_pcnu` FK ke `organisasi_pcnu`) — wajib selesai

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=Insiden` — semua passing
- [ ] Form laporan publik dapat disubmit tanpa login
- [ ] PCNU A tidak dapat melihat insiden PCNU B
- [ ] Transisi status tercatat di `riwayat_status_insiden`, `sistem_transisi_status`, dan `operasi_jurnal`
- [ ] `is_locked=1` setelah status `selesai` — UI menampilkan banner terkunci
- [ ] Trigger `tr_lock_incident_data` dibuktikan via feature test MySQL

---

## SPRINT 4 — Assessment

### 1. Tujuan Sprint
Membangun modul kajian lapangan yang terhubung ke insiden. Assessment adalah data primer yang menjadi dasar sitrep dan laporan kebutuhan.

### 2. Modul yang Dikerjakan

Tabel:
- `assessment_utama` — PK `id_assessment_utama`; `jenis_laporan` ENUM(`kaji_cepat`,`pendataan_lanjutan`); `is_latest` TINYINT; `waktu_assesment` (typo — IKUTI SQL); soft delete `dihapus_pada`
- `assessment_dampak_manusia` — `meninggal`, `hilang`, `menderita_mengungsi`
- `assessment_kebutuhan_mendesak` — `kebutuhan_pangan`, `kebutuhan_medis`
- `sistem_file_media` — lampiran foto assessment (polymorphic)

Trigger aktif:
- `tr_single_latest_assessment` — INSERT `is_latest=1` → set semua assessment lain di insiden menjadi `is_latest=0`

Catatan kritis dari SQL:
- Kolom `waktu_assesment` bukan `waktu_assessment` — **IKUTI TYPO SQL**
- `assessment_utama` memiliki `dihapus_pada` (soft delete)
- Koordinat: `latitude_titik_kaji`, `longitude_titik_kaji` — opsional tapi ada validasi batas Indonesia

### 3. Deliverable
- [ ] Migration ketiga tabel assessment
- [ ] Model `AssessmentUtama`, `AssessmentDampakManusia`, `AssessmentKebutuhanMendesak`
- [ ] `AssessmentController::store()` dalam `DB::transaction()` — simpan ketiga tabel sekaligus
- [ ] `AssessmentPolicy`: relawan TRC yang ditugaskan dapat membuat assessment
- [ ] Validasi: assessment tidak dapat dibuat untuk insiden `draft` atau `selesai`/`is_locked`
- [ ] Logika pencegahan delete: assessment yang menjadi basis sitrep tidak dapat dihapus
- [ ] Badge `is_latest` di UI dengan refresh otomatis setelah assessment baru dibuat
- [ ] Upload foto via `sistem_file_media` (polymorphic)
- [ ] Trigger `tr_single_latest_assessment` diverifikasi via DB test

### 4. Risiko
- **Tinggi**: Typo `waktu_assesment` — JANGAN dikoreksi. Setiap developer harus ingat ini.
- **Sedang**: Trigger `tr_single_latest_assessment` — jika assessment dibuat bersamaan (concurrent), bisa race condition. Dokumentasikan limitasi ini.
- **Rendah**: Koordinat opsional — validasi hanya jika koordinat diisi

### 5. Dependensi
- **S03** (Insiden) — wajib selesai

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=Assessment` — semua passing
- [ ] Trigger `is_latest` dibuktikan via MySQL test
- [ ] Assessment tidak dapat dibuat untuk insiden terkunci
- [ ] Delete assessment yang menjadi basis sitrep ditolak dengan pesan yang jelas
- [ ] `waktu_assesment` (typo) tersimpan benar di DB

---

## SPRINT 5 — Sitrep

### 1. Tujuan Sprint
Membangun laporan situasi resmi dengan lifecycle draft→ditinjau→final, snapshot otomatis, hash integrity, dan generate PDF. Sitrep FINAL bersifat immutable.

### 2. Modul yang Dikerjakan

Tabel:
- `operasi_sitrep` — `nomor_sitrep` UNIQUE per insiden; `status_sitrep` ENUM(`draft`,`ditinjau`,`final`); `snapshot_dampak` JSON; `hash_snapshot` VARCHAR(64); `file_pdf_path`; soft delete
- `operasi_sitrep_sumber` — pivot: `id_sitrep` + `id_assessment` + `waktu_versi_assessment` (histori assessment yang digunakan)

Trigger aktif:
- `tr_auto_snapshot_sitrep` — INSERT → auto-populate `snapshot_dampak`
- `tr_auto_snapshot_sitrep_update` — UPDATE (non-final) → update snapshot

Kritis dari SQL:
- `operasi_sitrep_sumber` adalah tabel pivot yang mencatat assessment mana yang menjadi sumber sitrep — gunakan ini untuk audit trail
- `hash_snapshot` diisi di aplikasi (bukan trigger) — wajib SHA2 dari `snapshot_dampak`

### 3. Deliverable
- [ ] Migration `operasi_sitrep`, `operasi_sitrep_sumber`
- [ ] Model `OperasiSitrep`, `OperasiSitrepSumber`
- [ ] `nomor_sitrep` auto-generate via `SELECT MAX + 1` dalam transaction
- [ ] `SitrepService::finalisasi()` dalam `DB::transaction()`:
  - Set `status='final'`, `waktu_difinalisasi`, `id_penfinalisasi`
  - Hitung `hash_snapshot = SHA2(snapshot_dampak, 256)`
  - Generate PDF via `SitrepPdfService`
  - Update `file_pdf_path`
  - INSERT ke `sistem_transisi_status`
  - INSERT ke `operasi_jurnal`
- [ ] `SitrepPdfService` menggunakan dompdf
- [ ] Template PDF `pdf/sitrep.blade.php` format resmi NURISK
- [ ] Sitrep FINAL: UI readonly, semua form disabled, hash ditampilkan

### 4. Risiko
- **Tinggi**: Trigger `tr_auto_snapshot_sitrep` — jika assessment_basis belum ada saat sitrep dibuat, snapshot mungkin kosong. Validasi di Controller sebelum create.
- **Sedang**: Generate PDF dengan dompdf memakan waktu — gunakan job queue untuk insiden besar
- **Rendah**: `nomor_sitrep` race condition jika dua sitrep dibuat bersamaan — gunakan DB lock

### 5. Dependensi
- **S04** (Assessment) — wajib selesai

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=Sitrep` — semua passing
- [ ] Trigger snapshot dibuktikan via MySQL test
- [ ] `hash_snapshot` terisi setelah finalisasi
- [ ] PDF dapat didownload setelah finalisasi
- [ ] Sitrep FINAL: tidak dapat diedit oleh siapapun termasuk super_admin

---

## SPRINT 6 — Pos Aju & Pengungsian

### 1. Tujuan Sprint
Membangun manajemen pos komando lapangan dan sistem pengungsian. Sprint ini digabung karena kedua domain terhubung secara geografis dan operasional.

> ⚠️ **CATATAN DEPENDENCY:** Pos Aju secara penuh memerlukan Pleno (Sprint 9) untuk penunjukan komandan dan keputusan pembukaan. Pada Sprint 6 ini, implementasikan Pos Aju **tanpa enforcing FK pleno** — FK `id_pleno_keputusan` dan `id_pleno_penunjukan` dibuat nullable sementara. Sprint 9 akan mengaktifkan enforcement tersebut.

### 2. Modul yang Dikerjakan

**A. Pos Aju**

Tabel:
- `operasi_posaju` — `id_pos_pengungsian` (sesuaikan dengan SQL), koordinat GPS, status, FK insiden
- `operasi_posaju_komandan` — histori komandan, `id_pleno_penunjukan` (nullable di sprint ini)

Kritis dari SQL:
- Pos aju DITUTUP: status tidak bisa kembali aktif
- Stok logistik pos aju via `logistik_stok.id_posaju` (FK ke `operasi_posaju`)

**B. Pengungsian**

Tabel (ada di SQL, sebelumnya tidak terdokumentasi):
- `operasi_pos_pengungsian` — pos pengungsian: `jml_kk`, `jml_jiwa`, `jml_laki`, `jml_perempuan`, `jml_lansia`, `jml_balita`, `jml_disabilitas`, `kapasitas_maksimal_kk/jiwa`, `status_pos` ENUM(`aktif`,`tutup`)
- `pengungsian_sensus_harian` — sensus harian per pos: tanggal + breakdown demografi detail + `kondisi_kesehatan_umum` + `kebutuhan_mendesak_harian`
- `master_penerima_manfaat` — data individu penerima bantuan

Kritis dari SQL:
- `operasi_pos_pengungsian` memiliki semua field kapasitas dan demografi — ini adalah pos pengungsian FISIK
- `pengungsian_sensus_harian` adalah update harian dari kondisi pos pengungsian
- `master_penerima_manfaat.tipe_penerima` ENUM: `individu`,`kk`,`kelompok`,`posko`,`desa`,`lembaga`

### 3. Deliverable
- [ ] Migration `operasi_posaju`, `operasi_posaju_komandan`
- [ ] Migration `operasi_pos_pengungsian`, `pengungsian_sensus_harian`, `master_penerima_manfaat`
- [ ] Model semua tabel di atas dengan relasi yang benar
- [ ] `PosajuController`: CRUD, buka/tutup pos
- [ ] `PosajuPolicy`: tutup hanya oleh id_peran ∈ [1,2] atau komandan insiden
- [ ] `PosPengungsiController`: CRUD pos pengungsian + input sensus harian
- [ ] Halaman pos aju: list + peta marker Leaflet
- [ ] Halaman pengungsian: list pos + kapasitas meter visual + form sensus harian
- [ ] `master_penerima_manfaat`: CRUD penerima bantuan dengan validasi NIK untuk individu
- [ ] Statistik pengungsian tampil di halaman show insiden (tab Pengungsian)

### 4. Risiko
- **Tinggi**: FK `id_pleno_penunjukan` di `operasi_posaju_komandan` nullable sementara — WAJIB diaktifkan di Sprint 9
- **Sedang**: `pengungsian_sensus_harian` — validasi UNIQUE per `(id_pos_pengungsian, tanggal_sensus)` — satu sensus per hari per pos
- **Rendah**: Kapasitas maksimal — alert jika `jml_kk > kapasitas_maksimal_kk` di UI

### 5. Dependensi
- **S03** (Insiden) — wajib selesai
- **S07** (Logistik) — untuk stok pos aju (boleh dikerjakan setelah Sprint 7)

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=PosAju` — semua passing
- [ ] `php artisan test --filter=Pengungsian` — semua passing
- [ ] Pos aju DITUTUP tidak dapat diaktifkan kembali
- [ ] Sensus harian satu entri per hari per pos (UNIQUE constraint diuji)
- [ ] Marker pos aju tampil di peta Leaflet
- [ ] Data kapasitas vs aktual tampil di UI dengan warna warning

---

## SPRINT 7 — Logistik

### 1. Tujuan Sprint
Membangun sistem manajemen logistik lengkap dengan aturan paling ketat: seluruh perubahan stok WAJIB melalui `logistik_mutasi`, dilindungi 4 trigger database.

### 2. Modul yang Dikerjakan

Tabel:
- `logistik_gudang` — gudang penyimpanan (scope PCNU/PWNU)
- `logistik_barang_katalog` — katalog standar barang
- `logistik_kategori` — 7 kategori (sudah seed)
- `master_satuan` — 29 satuan (sudah seed)
- `logistik_stok` — stok aktual; `jumlah_tersedia` HANYA diubah via trigger
- `logistik_mutasi` — log setiap perubahan; `uuid_mutasi` CHAR(36) UNIQUE; `tipe_mutasi` ENUM(`masuk`,`keluar`,`penyesuaian`)
- `logistik_permintaan` — permintaan dari pos aju ke gudang; `status_permintaan` ENUM 6 nilai
- `logistik_perencanaan` — perencanaan kebutuhan per insiden

Trigger aktif (JANGAN BYPASS):
- `tr_execute_logistik_stok_update` — INSERT ke `logistik_mutasi` → UPDATE stok otomatis
- `tr_logistik_mutasi_integrity_guard` — keluar > stok → SIGNAL error
- `tr_validate_stock_ownership` — gudang PCNU-A tidak suplai insiden PCNU-B → SIGNAL error
- `tr_validate_logistik_request_scope` — posaju tujuan harus dalam insiden yang sama → SIGNAL error

### 3. Deliverable
- [ ] Migration semua tabel logistik
- [ ] Model: `LogistikGudang`, `LogistikBarangKatalog`, `LogistikStok`, `LogistikMutasi`, `LogistikPermintaan`, `LogistikPerencanaan`
- [ ] `LogistikMutasi::boot()` — auto-generate `uuid_mutasi = Str::uuid()` saat creating
- [ ] `LogistikMutasiService::catat()` dalam `DB::transaction()` — SATU-SATUNYA cara ubah stok
- [ ] TIDAK ADA route `PATCH /logistik/stok/{id}` yang mengubah `jumlah_tersedia`
- [ ] Halaman stok: per gudang + per pos aju, highlight stok kritis
- [ ] Form mutasi: tampilkan stok saat ini sebagai referensi, pilih tipe mutasi
- [ ] Kanban permintaan: `draft|diajukan|disetujui|dikirim|selesai`
- [ ] Perencanaan kebutuhan per insiden per prioritas
- [ ] Semua aksi logistik dicatat ke `operasi_jurnal` kategori `logistik`
- [ ] Semua 4 trigger diverifikasi via MySQL feature test

### 4. Risiko
- **KRITIS**: Jika ada satu pun kode yang menulis `$stok->update(['jumlah_tersedia' => X])` langsung, ini adalah bug fatal. Wajib ada linting rule atau code review khusus untuk ini.
- **Tinggi**: Trigger `tr_logistik_mutasi_integrity_guard` throw `QueryException` — wajib di-handle dengan try-catch yang menampilkan pesan user-friendly "Stok tidak mencukupi"
- **Sedang**: `logistik_permintaan` cross-insiden dicegah trigger — pastikan error dari trigger ditampilkan dengan baik di UI
- **Sedang**: Performa query stok dengan join ke katalog dan satuan — pastikan eager loading

### 5. Dependensi
- **S03** (Insiden), **S06** (Pos Aju — untuk FK `id_posaju` di `logistik_stok`) — wajib selesai

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=Logistik` — semua passing di MySQL
- [ ] Keempat trigger dibuktikan via dedicated DB test
- [ ] `uuid_mutasi` selalu unik — diverifikasi via UNIQUE constraint test
- [ ] Tidak ada route yang memungkinkan update langsung `jumlah_tersedia`
- [ ] Mutasi negatif (keluar > stok) menghasilkan pesan error yang mudah dipahami user
- [ ] PCNU A tidak dapat akses logistik insiden PCNU B

---

## SPRINT 8 — Relawan & Tugas Operasi

### 1. Tujuan Sprint
Membangun manajemen relawan lengkap: kebutuhan relawan, pendaftaran, verifikasi, penugasan operasional, shift, dan tugas spesifik per klaster.

### 2. Modul yang Dikerjakan

**A. Relawan**

Tabel (sebelumnya `relawan_kebutuhan` tidak terdokumentasi):
- `relawan_kebutuhan` — kebutuhan rekrutmen per insiden: `judul_posisi`, `deskripsi_tugas`, `jumlah_dibutuhkan`, `status_rekrutmen` ENUM(`dibuka`,`terpenuhi`,`dibatalkan`,`ditutup`), FK ke `operasi_klaster` dan `operasi_posaju`
- `relawan_pendaftaran` — daftar relawan untuk kebutuhan: UNIQUE `(id_pengguna, id_relawan_kebutuhan)`
- `relawan_penugasan` — penugasan relawan aktif ke operasi
- `relawan_shift` — shift waktu tugas relawan: `id_penugasan_relawan`, `waktu_mulai`, `waktu_selesai`

Kritis dari SQL:
- `relawan_kebutuhan` adalah entitas yang WAJIB ada sebelum `relawan_pendaftaran` — relawan mendaftar ke kebutuhan spesifik, bukan ke insiden secara umum
- `relawan_kebutuhan.id_keahlian_utama` FK ke `auth_keahlian_master` — kecocokan keahlian
- UNIQUE `(id_pengguna, id_relawan_kebutuhan)` di `relawan_pendaftaran`

**B. Klaster & Mobilisasi**

Tabel:
- `operasi_klaster` — 6 klaster per insiden; `status_klaster` ENUM; `progres_persen`
- `operasi_klaster_koordinator` — koordinator klaster (dengan `id_pleno_penunjukan` nullable di sprint ini)
- `operasi_mobilisasi_personil` — status fisik personel di lapangan
- `operasi_shift_personel` — shift personel operasional: `mulai_shift`, `selesai_shift`, `lokasi_tugas`, `status_shift` ENUM(`rencana`,`aktif`,`selesai`,`izin`)

**C. Tugas Operasi**

Tabel (sebelumnya tidak terdokumentasi):
- `operasi_tugas` — tugas spesifik per klaster: `judul_tugas`, `target_indikator`, `status_tugas` ENUM(`rencana`,`berjalan`,`tertunda`,`selesai`), `progres_persen`, FK ke `operasi_klaster` dan `operasi_posaju`, `id_surat_perintah` (nullable)

**D. Assignment**

Tabel:
- `operasi_penugasan` — assignment personel ke insiden dengan `peran_otoritas`
- `operasi_otoritas_kontekstual` — otoritas kontekstual per insiden

### 3. Deliverable
- [ ] Migration semua tabel relawan, klaster, mobilisasi, shift, tugas
- [ ] CRUD `relawan_kebutuhan` — super_admin/pcnu membuka lowongan relawan
- [ ] Pendaftaran relawan ke kebutuhan (UNIQUE constraint via FormRequest)
- [ ] Approval pendaftaran: menunggu → aktif / ditolak
- [ ] `relawan_shift`: jadwal shift relawan aktif
- [ ] 6 klaster default per insiden baru (dari `operasi_master_klaster` seed)
- [ ] CRUD `operasi_tugas` per klaster — komandan/koordinator
- [ ] Status tugas: update progres_persen, transisi status
- [ ] `operasi_shift_personel`: jadwal shift personel per klaster
- [ ] `operasi_mobilisasi_personil`: status kehadiran fisik (menuju_lokasi→di_lokasi→kembali)
- [ ] `operasi_penugasan` + `operasi_otoritas_kontekstual`
- [ ] Halaman klaster: 6 klaster + progres bar + daftar tugas
- [ ] Halaman relawan: kebutuhan aktif, pendaftaran saya, status verifikasi

### 4. Risiko
- **Tinggi**: `relawan_kebutuhan` adalah prerequisite `relawan_pendaftaran` — banyak form relawan yang bergantung pada data ini. Pastikan CRUD kebutuhan tersedia dan terisi sebelum pendaftaran dibuka.
- **Sedang**: `operasi_tugas.id_surat_perintah` FK ke `operasi_surat_keluar` (Sprint 10) — buat nullable di sprint ini
- **Sedang**: Shift overlap — validasi `mulai_shift < selesai_shift` dan tidak overlap dengan shift lain pengguna yang sama

### 5. Dependensi
- **S03** (Insiden), **S06** (Pos Aju — karena `relawan_kebutuhan` FK ke posaju)

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=Relawan` — semua passing
- [ ] UNIQUE `(id_pengguna, id_relawan_kebutuhan)` diuji via test
- [ ] Relawan belum terverifikasi tidak dapat ditugaskan
- [ ] Cross-region assignment: `auth_users.id_unit` tidak berubah — diverifikasi
- [ ] 6 klaster default tersedia setelah insiden aktif dibuat
- [ ] Progres tugas klaster dapat diupdate oleh koordinator

---

## SPRINT 9 — Pleno & Eskalasi

### 1. Tujuan Sprint
Membangun modul rapat pleno sebagai governance entity utama. Sprint ini juga **mengaktifkan enforcement FK pleno** yang di-nullable-kan di Sprint 6 dan 8.

### 2. Modul yang Dikerjakan

**A. Pleno**

Tabel:
- `operasi_pleno` — `nomor_pleno`, `jenis_pleno` ENUM(6: `aktivasi_operasi`,`evaluasi_rutin`,`perpanjangan_operasi`,`penutupan_operasi`,`eskalasi_wilayah`,`khusus`), `status_pleno` ENUM(6: `draft`,`ditinjau`,`disetujui`,`ditandatangani`,`final`,`dibatalkan`), `hash_dokumen`, `metode_tanda_tangan`
- `operasi_pleno_keputusan` — keputusan per pleno
- `operasi_pleno_peserta` — peserta dengan `status_kehadiran` + `status_persetujuan` + `hak_suara`
- `operasi_eskalasi` — eskalasi level insiden (harus via pleno)
- `operasi_aktivasi` — aktivasi status darurat

**B. Relasi Surat-Pleno (Sprint 10 preview)**

Tabel pivot yang akan digunakan di Sprint 10:
- `relasi_surat_pleno` — `id_surat` + `id_pleno`
- `relasi_surat_aktivasi` — `id_surat` + `id_aktivasi`

**C. Aktivasi FK yang Di-nullable-kan**

- `operasi_posaju_komandan.id_pleno_penunjukan` → aktifkan NOT NULL constraint
- `operasi_klaster_koordinator.id_pleno_penunjukan` → aktifkan NOT NULL constraint
- `operasi_periode.id_pleno_keputusan` → aktifkan NOT NULL constraint

Catatan trigger:
- `tr_validate_legal_temporal` — validasi temporal untuk entitas legal (pleno, surat, dll)

### 3. Deliverable
- [ ] Migration `operasi_pleno`, `operasi_pleno_keputusan`, `operasi_pleno_peserta`, `operasi_eskalasi`, `operasi_aktivasi`
- [ ] Migration pivot `relasi_surat_pleno`, `relasi_surat_aktivasi`
- [ ] Model semua tabel pleno dengan relasi lengkap
- [ ] `PlanoService::finalisasi()` dengan hash dokumen
- [ ] `EskalasiService::buat()` — validasi level naik, wajib ada `id_pleno`
- [ ] `PlanoPolicy`: finalisasi hanya id_peran ∈ [1,2] (Gate `finalize-pleno`)
- [ ] `EskalasiPolicy`: create hanya id_peran ∈ [1,2] (Gate `escalate-insiden`)
- [ ] Migration update: aktifkan NOT NULL untuk FK pleno di `operasi_posaju_komandan`, `operasi_klaster_koordinator`, `operasi_periode`
- [ ] Halaman pleno: index, show (tab Info|Keputusan|Peserta), create
- [ ] Form peserta: voting setuju/tolak/abstain, hak_suara
- [ ] Form eskalasi: pilih pleno sebagai dasar, level sebelumnya/baru
- [ ] Semua aksi pleno dicatat ke `sistem_transisi_status` dan `operasi_jurnal`
- [ ] Trigger `tr_validate_legal_temporal` diverifikasi

### 4. Risiko
- **Tinggi**: Mengaktifkan NOT NULL untuk FK pleno di sprint ini — dapat breaking change untuk data yang sudah dibuat di Sprint 6/8 tanpa pleno. Solusi: migration dengan default value atau data migration.
- **Tinggi**: `status_pleno` memiliki 6 nilai (lebih kompleks dari dokumentasi sebelumnya) — pastikan semua transisi state terdokumentasi
- **Sedang**: `hash_dokumen` di pleno — implementasikan hash dari snapshot content pleno
- **Sedang**: `jenis_pleno` ENUM — 6 jenis, masing-masing memiliki keputusan yang berbeda

### 5. Dependensi
- **S03** (Insiden), **S06** (Pos Aju — FK posaju_komandan), **S08** (Klaster — FK klaster_koordinator)

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=Pleno` — semua passing
- [ ] Pleno FINAL tidak dapat diubah
- [ ] FK pleno enforcement berjalan: komandan pos aju WAJIB ada pleno
- [ ] Eskalasi tanpa pleno ditolak di FK + Service level
- [ ] Eskalasi level turun ditolak oleh Service
- [ ] Voting peserta tersimpan benar

---

## SPRINT 10 — Surat Menyurat

### 1. Tujuan Sprint
Membangun sistem surat resmi sebagai legal entity dengan alur paraf berurutan, nomor otomatis, PDF generation, dan immutability setelah ditandatangani.

### 2. Modul yang Dikerjakan

> ⚠️ **KOREKSI KRITIS DARI AUDIT SQL:**
> Tabel surat utama adalah `operasi_surat_keluar` (BUKAN `operasi_surat_keluar` yang tidak ada di SQL).
> `dokumen_surat_paraf` dan `dokumen_surat_tembusan` ada dan FK ke `operasi_surat_keluar.id_surat`.

Tabel:
- `operasi_surat_keluar` — PK `id_surat`; `nomor_surat_resmi`; `status_surat` ENUM(`draft`,`review_paraf`,`siap_tanda_tangan`,`ditandatangani`,`ditolak`,`arsip`); `id_jabatan_ttd` FK ke `master_jabatan_penandatangan`; `isi_surat_snapshot` LONGTEXT; `file_pdf_path`
- `dokumen_surat_paraf` — `urutan` INT; `status_paraf` ENUM(`menunggu`,`disetujui`,`ditolak`); `waktu_paraf`
- `dokumen_surat_tembusan` — `nama_pihak` VARCHAR
- `master_surat_jenis` — jenis surat + format nomor
- `master_surat_template` — template isi surat
- `master_jabatan_penandatangan` — jabatan yang berwenang tanda tangan
- `relasi_surat_pleno` — pivot surat ↔ pleno
- `relasi_surat_aktivasi` — pivot surat ↔ aktivasi
- `relasi_surat_tugas` — pivot surat ↔ tugas operasi

Kritis dari SQL:
- Status surat ada **6 nilai** bukan 4 seperti di dokumen awal: `draft`, `review_paraf`, `siap_tanda_tangan`, `ditandatangani`, `ditolak`, `arsip`
- `isi_surat_snapshot` LONGTEXT — di-snapshot saat finalisasi (immutable)
- `id_jabatan_ttd` FK ke `master_jabatan_penandatangan` — bukan `master_jabatan`
- `sistem_log_alur_kerja` — log approval workflow surat dan pleno

Trigger aktif:
- `tr_validate_legal_temporal` — validasi temporal untuk dokumen legal

### 3. Deliverable
- [ ] Migration `operasi_surat_keluar`, `dokumen_surat_paraf`, `dokumen_surat_tembusan` (pivot relasi sudah ada dari S09)
- [ ] Model: `OperasiSuratKeluar`, `DokumenSuratParaf`, `DokumenSuratTembusan`
- [ ] `NomorSuratService::generate()` — parsing `format_nomor` dari `master_surat_jenis`
- [ ] `SuratService::prosesParaf()` dalam `DB::transaction()`:
  - Disetujui + paraf berikutnya → aktifkan urutan selanjutnya
  - Disetujui + tidak ada lagi → status `siap_tanda_tangan`
  - Ditolak → status `draft`, reset paraf setelahnya ke `menunggu`
  - INSERT ke `sistem_log_alur_kerja`
- [ ] `SuratPdfService::generate()` via dompdf
- [ ] Snapshot `isi_surat_snapshot` saat transisi ke `ditandatangani`
- [ ] Relasi surat ↔ pleno via `relasi_surat_pleno`
- [ ] Relasi surat ↔ tugas via `relasi_surat_tugas`
- [ ] Halaman surat: index, show (timeline paraf), create (dari template), edit (hanya draft)
- [ ] Timeline paraf: urutan visual, status warna, waktu paraf

### 4. Risiko
- **KRITIS**: Nama tabel `operasi_surat_keluar` berbeda dari semua dokumentasi sebelumnya (`operasi_surat_keluar`) — setiap referensi lama harus dikoreksi
- **Tinggi**: Status surat 6 nilai (lebih kompleks dari dokumentasi) — state machine harus tepat
- **Sedang**: `sistem_log_alur_kerja` — harus diintegrasikan ke semua approval flow (paraf surat, approval klaster, dsb)

### 5. Dependensi
- **S09** (Pleno — untuk `relasi_surat_pleno`), **S08** (Tugas — untuk `relasi_surat_tugas`)

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=Surat` — semua passing
- [ ] Nama model menggunakan `OperasiSuratKeluar` (bukan `DokumenSuratUtama`)
- [ ] Alur 6 status berjalan benar
- [ ] `sistem_log_alur_kerja` terisi setiap aksi paraf
- [ ] Surat `ditandatangani` tidak dapat diedit — `isi_surat_snapshot` immutable
- [ ] PDF dapat didownload setelah `ditandatangani`

---

## SPRINT 11 — Feedback Klaster & Gap Kebutuhan

### 1. Tujuan Sprint
Membangun sistem evaluasi pasca-respon per klaster dan identifikasi gap kebutuhan aktual dari lapangan.

> ✅ **KONFIRMASI DARI AUDIT SQL:** Kedua tabel ini ADA di SQL dump. Tidak perlu menunggu konfirmasi — implementasi dapat langsung dilakukan.

### 2. Modul yang Dikerjakan

**A. Feedback Klaster**

Tabel:
- `operasi_klaster_feedback` — `id_feedback`; `id_insiden`; `id_klaster`; `id_incident_assignment` (FK ke `operasi_penugasan`); `status_lokasi` ENUM(`membaik`,`stagnan`,`memburuk`); `tingkat_prioritas` ENUM; `perlu_tindak_lanjut` TINYINT; `kondisi_aktual` TEXT; `kebutuhan_lanjutan` TEXT; `rekomendasi` TEXT; koordinat GPS; soft delete

Kritis dari SQL:
- `id_incident_assignment` nullable — feedback bisa dibuat tanpa assignment (oleh pcnu/pwnu langsung)
- Koordinat GPS opsional — untuk geo-tagging laporan feedback
- Feedback dengan `perlu_tindak_lanjut=1` harus memunculkan gap kebutuhan

**B. Gap Kebutuhan**

Tabel:
- `operasi_gap_kebutuhan` — `id_gap`; `id_insiden`; `id_feedback` (FK ke `operasi_klaster_feedback`); `id_klaster`; `kategori_gap` ENUM(`relawan`,`logistik`,`medis`,`alat`,`shelter`,`assessment`,`keamanan`,`lainnya`); `prioritas`; `deskripsi_gap`; `jumlah_dibutuhkan`; `satuan`; `status_gap` ENUM(`terbuka`,`diproses`,`terpenuhi`,`ditutup`); `direkomendasikan_oleh`; `dipenuhi_pada`; soft delete

Kritis dari SQL:
- Gap SELALU berasal dari feedback (`id_feedback` NOT NULL) — tidak ada gap tanpa feedback
- `status_gap = 'terpenuhi'`: `dipenuhi_pada` diisi
- `kategori_gap` menentukan domain yang bertanggung jawab menangani gap

### 3. Deliverable
- [ ] Migration `operasi_klaster_feedback`, `operasi_gap_kebutuhan`
- [ ] Model `OperasiKlasterFeedback`, `OperasiGapKebutuhan` dengan relasi
- [ ] `FeedbackController`: CRUD per insiden+klaster; hanya relawan dengan assignment atau pcnu/pwnu
- [ ] `GapKebutuhanController`: CRUD; auto-create dari feedback `perlu_tindak_lanjut=1`
- [ ] Logic: feedback dengan `perlu_tindak_lanjut=1` → otomatis buka form create gap
- [ ] Status gap: `terbuka→diproses→terpenuhi/ditutup` + isi `dipenuhi_pada` saat terpenuhi
- [ ] `FeedbackPolicy`: relawan dengan assignment aktif atau pcnu/pwnu
- [ ] `GapPolicy`: pcnu/pwnu dapat assign gap ke domain terkait
- [ ] Halaman feedback: list per klaster, form isi feedback lapangan
- [ ] Halaman gap: kanban `terbuka|diproses|terpenuhi`, filter per kategori
- [ ] Dashboard insiden tab "Feedback & Gap" — agregasi statistik

### 4. Risiko
- **Sedang**: Gap SELALU dari feedback — pastikan UI tidak memungkinkan create gap langsung tanpa feedback
- **Rendah**: Koordinat GPS feedback opsional — tampilkan di peta hanya jika ada koordinat

### 5. Dependensi
- **S08** (Relawan & Klaster), **S03** (Insiden)

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=Feedback` — semua passing
- [ ] `php artisan test --filter=Gap` — semua passing
- [ ] Gap tidak dapat dibuat tanpa feedback (FK constraint + FormRequest)
- [ ] Feedback `perlu_tindak_lanjut=1` memunculkan form gap
- [ ] Status gap `terpenuhi` mengisi `dipenuhi_pada`

---

## SPRINT 12 — Dashboard & Command Center

### 1. Tujuan Sprint
Membangun tampilan operasional real-time: peta insiden aktif, statistik lintas domain, stok kritis, timeline jurnal, dan public map. Menggunakan 10 view SQL yang sudah ada.

### 2. Modul yang Dikerjakan

**A. View SQL yang Tersedia (GUNAKAN, JANGAN BUAT ULANG)**

View yang sudah ada di SQL dump dan siap dipakai:
- `v_command_center_summary` — ringkasan command center all-in-one
- `v_incident_timeline_comprehensive` — timeline lengkap per insiden
- `v_logistik_distribution_audit` — audit distribusi logistik
- `v_aset_operasional_ready` — aset yang siap pakai
- `v_aset_siap_pakai` — alias untuk aset tersedia
- `v_relawan_domisili_check` — cek domisili relawan vs wilayah insiden
- `v_user_access_control` — access control view
- `v_alert_insiden_baru` — alert insiden baru yang belum ditangani
- `v_wilayah_blank_spot` — wilayah tanpa PCNU aktif
- `v_audit_surat_orphans` — audit surat yang tidak punya relasi

Kritis: **Gunakan view SQL ini via `DB::table('v_command_center_summary')->get()`** — JANGAN membuat query aggregasi manual yang lebih lambat.

**B. Dashboard Internal**

- Dashboard per role: pcnu (scope sendiri), pwnu (semua pcnu)
- Widget: insiden aktif count, personel di lapangan, stok kritis, pos pengungsian aktif
- Filter: per wilayah (untuk pwnu), per status

**C. Command Center**

- Peta Leaflet.js dengan marker clustering
- Custom icon per `bencana_master_jenis`
- Layer control: filter per status insiden, per wilayah
- Panel kanan: jurnal terbaru, alert gap terbuka, stok kritis
- AJAX polling 30 detik via `fetch()` + JSON API
- TIDAK menggunakan WebSocket

**D. Public Map**

- Peta insiden aktif tanpa login
- Hanya data non-sensitif: jenis bencana, lokasi umum, status
- Form laporan kejadian dari masyarakat

### 3. Deliverable
- [ ] `DashboardController` menggunakan `v_command_center_summary` (bukan custom query)
- [ ] `CommandCenterApiController`: 5 endpoint AJAX
  - `GET /api/command-center/insiden-aktif` — dari `v_alert_insiden_baru`
  - `GET /api/command-center/statistik` — dari `v_command_center_summary`
  - `GET /api/command-center/stok-kritis` — dari `logistik_stok` aggregasi
  - `GET /api/command-center/jurnal-terbaru` — dari `operasi_jurnal`
  - `GET /api/command-center/gap-terbuka` — dari `operasi_gap_kebutuhan` status=terbuka
- [ ] Peta Leaflet: marker clustering, popup, layer control
- [ ] AJAX polling 30s: `setInterval(fn, 30000)` berjalan tanpa error
- [ ] Halaman publik: map + form laporan, responsive, tidak butuh login
- [ ] Cache: data summary di-cache 5 menit (bukan real-time)
- [ ] Scope enforcement: PCNU hanya melihat data wilayahnya

### 4. Risiko
- **Sedang**: View SQL `v_command_center_summary` — pastikan view ini tidak mengandung data cross-scope. Audit query view sebelum digunakan.
- **Sedang**: Polling 30 detik — jika banyak user aktif bersamaan, beban DB meningkat. Pastikan API endpoint ter-cache dengan baik.
- **Rendah**: Leaflet marker clustering — pastikan library dimuat dari CDN atau bundled

### 5. Dependensi
- Semua sprint sebelumnya (minimum S01–S07)

### 6. Kriteria Selesai
- [ ] `php artisan test --filter=Dashboard` — semua passing
- [ ] View SQL terpakai (bukan query manual) — konfirmasi via query log
- [ ] AJAX polling berjalan 30s tanpa memory leak (test di browser dev tools)
- [ ] Peta Leaflet dengan marker insiden aktif berfungsi
- [ ] PCNU melihat data scope sendiri, PWNU melihat semua
- [ ] Public map dapat diakses tanpa login
- [ ] Data sensisitf tidak bocor ke API publik

---

---

## MATRIKS DEPENDENSI SPRINT

```
S01 (Auth)
  └── S02 (Organisasi & Wilayah)
        └── S03 (Insiden)
              ├── S04 (Assessment)
              │     └── S05 (Sitrep)
              ├── S06 (Pos Aju & Pengungsian)
              │     └── S07 (Logistik)
              │           └── S08 (Relawan & Tugas)
              │                 └── S09 (Pleno & Eskalasi)
              │                       └── S10 (Surat Menyurat)
              │                             └── S11 (Feedback & Gap)
              └── S12 (Dashboard) ← butuh S01–S07 minimal
```

---

## KOREKSI WAJIB PADA DOKUMEN PRA-PRODUKSI

Berdasarkan audit SQL mendalam, dokumen pra-produksi berikut perlu dikoreksi:

| Dokumen | Item yang Perlu Dikoreksi |
|---------|--------------------------|
| `IMPLEMENTATION_BACKLOG.md` | Nama tabel surat: `operasi_surat_keluar` (bukan `operasi_surat_keluar`) |
| `IMPLEMENTATION_BACKLOG.md` | Status surat 6 nilai (bukan 4/5 nilai) |
| `IMPLEMENTATION_BACKLOG.md` | Tabel jabatan: `master_jabatan` dengan PK `id_master_jabatan` (bukan `master_jabatan`) |
| `IMPLEMENTATION_BACKLOG.md` | M16 Pengungsian: gunakan `operasi_pos_pengungsian` + `pengungsian_sensus_harian` |
| `IMPLEMENTATION_BACKLOG.md` | M17 Feedback & Gap: tabel terkonfirmasi, Sprint 11 dapat langsung diimplementasi |
| `DATABASE_CONVENTION.md` | Tambahkan prefix tabel baru: `v_` (views), `sistem_` (infrastruktur), `wilayah_` (administratif) |
| `SYSTEM_ARCHITECTURE.md` | Tambahkan 10 view SQL sebagai read-only aggregation layer |
| `DEFINITION_OF_DONE.md` | Koreksi nama tabel jabatan dan surat di semua modul |
| Semua dokumen | `master_jabatan` tidak ada — ganti dengan `master_jabatan` |

---

## CATATAN UNTUK AI AGENT

Sebelum memulai setiap sprint, AI Agent **WAJIB**:

1. **Baca SQL dump langsung** untuk konfirmasi nama kolom:
   ```bash
   awk '/CREATE TABLE `nama_tabel`/,/^\) ENGINE/' /home/londo/Downloads/nurisk_37_frozen_final\(1\).sql
   ```

2. **Jangan percaya dokumentasi lama** jika ada keraguan — SQL dump adalah sumber kebenaran

3. **Cek tabel benar-benar ada** sebelum membuat migration:
   ```bash
   grep "CREATE TABLE \`nama_tabel\`" /home/londo/Downloads/nurisk_37_frozen_final\(1\).sql
   ```

4. **Gunakan view SQL yang sudah ada** di Sprint 12 — jangan buat aggregasi manual

5. **Periksa typo** di nama kolom: `waktu_assesment` (bukan `waktu_assessment`)
