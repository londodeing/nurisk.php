# DEFINITION_OF_DONE.md — NURISK
# Standar Penyelesaian Modul — FROZEN

> **Senior QA Engineer & Technical Lead**
> Versi: 1.0 — Tanggal: 16 Juni 2026
> Sumber: SQL Dump v37 (FROZEN), PRD Final, Dokumen Pra-Produksi
>
> ⚠️ **STATUS DOKUMEN: DIFREEZE**
> Dokumen ini adalah standar resmi seluruh proyek NURISK.
> Suatu modul **TIDAK BOLEH** dinyatakan selesai jika ada satu pun checklist
> yang belum terpenuhi. Tidak ada pengecualian tanpa keputusan eksplisit dari
> Technical Lead dan didokumentasikan dalam changelog.

---

## CARA MEMBACA DOKUMEN INI

Setiap modul memiliki 5 blok checklist:

- **A — Database**: migration, FK, constraint, seed
- **B — Backend**: model, relasi, validation, service, policy, controller
- **C — Frontend Web**: halaman-halaman yang wajib ada
- **D — Security**: authorization, scope wilayah, audit trail
- **E — Testing**: feature test, workflow test, authorization test

Simbol status:
- `[ ]` = belum dikerjakan
- `[/]` = sedang dikerjakan
- `[x]` = selesai dan diverifikasi

**Suatu modul dinyatakan DONE jika dan hanya jika seluruh checklist bertanda `[x]`.**

---

## STANDAR GLOBAL (BERLAKU UNTUK SEMUA MODUL)

Sebelum checklist per modul, standar berikut berlaku universal:

### Standar Kode
- [ ] Semua nama tabel mengikuti SQL dump v37 (snake_case, Bahasa Indonesia)
- [ ] Semua nama kolom mengikuti SQL dump v37
- [ ] Tidak ada `created_at`/`updated_at` — wajib `dibuat_pada`/`diperbarui_pada`
- [ ] Tidak ada `deleted_at` — wajib `dihapus_pada` untuk tabel yang memilikinya
- [ ] Tidak ada `id` sebagai primary key — wajib `id_{nama_entitas}`
- [ ] Setiap Model mendefinisikan `$table`, `$primaryKey`, `CREATED_AT`, `UPDATED_AT`
- [ ] Setiap operasi multi-tabel dibungkus `DB::transaction()`
- [ ] Tidak ada query di Blade view

### Standar Security
- [ ] Setiap route internal dilindungi middleware `auth` + `check.akun.aktif`
- [ ] Setiap Controller action memanggil `$this->authorize()` atau `Gate::authorize()`
- [ ] Tidak ada `if ($user->id_peran == X)` di Controller — wajib gunakan Policy
- [ ] Scope wilayah divalidasi: PCNU hanya akses `id_pcnu = default_scope_id`
- [ ] Publik tidak dapat akses data operasional

### Standar Testing
- [ ] Semua test dijalankan menggunakan database MySQL (bukan SQLite)
- [ ] `RefreshDatabase` digunakan + `DatabaseSeeder` dijalankan di `setUp()`
- [ ] Setiap test method menggunakan nama deskriptif format `subjek_kondisi_hasil()`
- [ ] Tidak ada test yang hanya memverifikasi HTTP 200 tanpa validasi data
- [ ] Trigger database tidak di-mock — diuji dengan DB nyata

---

---

## M01 — AUTH & USER MANAGEMENT

### A — Database
- [ ] Migration `auth_users` berhasil dijalankan tanpa error di MySQL
- [ ] Migration `auth_pengguna_profil` berhasil dengan FK ke `auth_users.id_pengguna`
- [ ] Migration `auth_keahlian_master` berhasil
- [ ] Migration `auth_pengguna_keahlian` berhasil dengan FK ke `auth_users` dan `auth_keahlian_master`
- [ ] Kolom `status_akun` ENUM(`menunggu`,`aktif`,`nonaktif`,`suspend`) terdefinisi benar
- [ ] Kolom `default_scope_type` ENUM(`pwnu`,`pcnu`,`mwc`,`ranting`,`lembaga`,`banom`) terdefinisi benar
- [ ] `auth_users.kata_sandi` menggunakan `bcrypt` saat disimpan (bukan plaintext)
- [ ] Trigger `tr_sync_user_role_insert` aktif: INSERT ke `auth_users` → INSERT ke `model_has_roles`
- [ ] Trigger `tr_sync_user_role_update` aktif: UPDATE `id_peran` → sync `model_has_roles`
- [ ] Seed: 5 role PRD tersedia di `auth_roles` (`super_admin`, `pwnu`, `pcnu`, `relawan`, `publik`)

### B — Backend
- [ ] `AuthUser` model: `$table='auth_users'`, `$primaryKey='id_pengguna'`, `const CREATED_AT='dibuat_pada'`, `const UPDATED_AT='diperbarui_pada'`
- [ ] `AuthUser::getAuthPassword()` mengembalikan `$this->kata_sandi`
- [ ] `AuthUser::getAuthIdentifierName()` mengembalikan `'no_hp'`
- [ ] `config/auth.php` provider menggunakan `AuthUser::class`
- [ ] Relasi `AuthUser hasOne AuthPenggunaProfil` berfungsi
- [ ] Relasi `AuthUser belongsTo AuthRole via id_peran` berfungsi
- [ ] Relasi `AuthUser belongsToMany AuthKeahlianMaster via auth_pengguna_keahlian` berfungsi
- [ ] `LoginRequest` validasi: `no_hp` required, `kata_sandi` required min:8
- [ ] `RegisterPublikRequest` validasi: `no_hp` unique, `kata_sandi` confirmed, `nik` nullable digits:16
- [ ] `LoginController::login()` menolak akun `status_akun != 'aktif'` dengan pesan error yang jelas
- [ ] `RegisterPublikController::store()` menyimpan `status_akun = 'menunggu'` secara default
- [ ] `UserManagementController` tersedia untuk `super_admin`: index, show, edit, update, aktivasi, suspend
- [ ] `AuthUserPolicy` terdefinisi dan terdaftar di `AuthServiceProvider`

### C — Frontend Web
- [ ] Halaman login (`/login`): form `no_hp` + `kata_sandi`, pesan error inline, link ke register
- [ ] Halaman register (`/daftar`): form nama, no_hp, nik, email, password confirmed
- [ ] Halaman setelah register: konfirmasi "Akun menunggu aktivasi"
- [ ] Halaman `admin/users/index`: tabel daftar pengguna, filter status_akun, pagination
- [ ] Halaman `admin/users/show`: detail profil + keahlian + jabatan + status akun
- [ ] Halaman `admin/users/edit`: form edit profil, ubah role (super_admin only)
- [ ] Layout `layouts/app.blade.php`: sidebar, navbar dengan nama pengguna + scope wilayah, logout
- [ ] Layout `layouts/public.blade.php`: header logo, footer
- [ ] Sidebar menampilkan menu berbeda berdasarkan `id_peran`
- [ ] Scope wilayah aktif (nama PCNU/PWNU) tampil di navbar
- [ ] Redirect setelah login mengarah ke `/dashboard` (berbeda per role jika perlu)

### D — Security
- [ ] Route `/dashboard` dan semua route internal diblokir untuk guest (redirect ke `/login`)
- [ ] Akun `menunggu` mendapat pesan: "Akun Anda sedang menunggu aktivasi"
- [ ] Akun `suspend` mendapat pesan: "Akun Anda ditangguhkan"
- [ ] Akun `nonaktif` mendapat pesan yang sesuai
- [ ] `super_admin` tidak dapat mengubah `id_peran` dirinya sendiri menjadi lebih rendah via UI
- [ ] Tidak ada data pengguna lain yang bocor via API

### E — Testing
- [ ] `test_user_dapat_login_dengan_no_hp_valid` ✓
- [ ] `test_login_gagal_dengan_kata_sandi_salah` ✓
- [ ] `test_akun_menunggu_tidak_dapat_login` ✓
- [ ] `test_akun_suspend_tidak_dapat_login` ✓
- [ ] `test_akun_nonaktif_tidak_dapat_login` ✓
- [ ] `test_register_publik_berhasil_status_menunggu` ✓
- [ ] `test_no_hp_duplikat_ditolak_saat_register` ✓
- [ ] `test_trigger_sync_model_has_roles_berjalan` ✓
- [ ] `test_super_admin_dapat_aktivasi_akun` ✓
- [ ] `test_pcnu_tidak_dapat_akses_user_management` ✓
- [ ] `php artisan test --filter=Auth` → semua passing

---

## M02 — AUTHORIZATION INFRASTRUCTURE

### A — Database
- [ ] Migration `master_jabatan` berhasil: `id_jabatan`, `kode_jabatan`, `nama_jabatan`, `urutan_hierarki`, `aktif`
- [ ] Migration `pengguna_jabatan` berhasil dengan FK ke `auth_users.id_pengguna` dan `master_jabatan.id_jabatan`
- [ ] Seed: 15 jabatan default dari SQL dump tersedia di `master_jabatan`

### B — Backend
- [ ] `JabatanPosisi` model: `$table='master_jabatan'`, `$primaryKey='id_jabatan'`, `public $timestamps = false`
- [ ] `PenggunaJabatan` model: relasi ke `auth_users` dan `master_jabatan`
- [ ] `AuthUser::jabatan()` hasMany `PenggunaJabatan` berfungsi
- [ ] `AuthUser::jabatanAktif()` hasOne (jabatan dengan tanggal aktif saat ini) berfungsi
- [ ] `BaseNuriskPolicy` tersedia dengan semua helper method:
  - [ ] `isSuperAdmin(AuthUser $user): bool` — `$user->id_peran === 1`
  - [ ] `isPwnu(AuthUser $user): bool` — `$user->id_peran === 2`
  - [ ] `isPcnu(AuthUser $user): bool` — `$user->id_peran === 3`
  - [ ] `isRelawan(AuthUser $user): bool` — `$user->id_peran === 4`
  - [ ] `isPublik(AuthUser $user): bool` — `$user->id_peran === 5`
  - [ ] `cekScopeWilayah(AuthUser $user, int $idPcnu): bool` — true jika super_admin/pwnu ATAU (pcnu && scope_id == $idPcnu)
  - [ ] `hasAssignment(AuthUser $user, int $idInsiden, ?string $peran): bool` — cek `operasi_penugasan` aktif
- [ ] 5 Gate terdefinisi di `AuthServiceProvider::boot()`:
  - [ ] `Gate::define('finalize-sitrep', ...)` — id_peran ∈ [1,2,3]
  - [ ] `Gate::define('escalate-insiden', ...)` — id_peran ∈ [1,2]
  - [ ] `Gate::define('finalize-pleno', ...)` — id_peran ∈ [1,2]
  - [ ] `Gate::define('approve-logistik', ...)` — id_peran ∈ [1,2,3]
  - [ ] `Gate::define('sign-surat', ...)` — cek jabatan penandatangan
- [ ] Middleware `role:*` dari Spatie berfungsi (test: `route()->middleware('role:super_admin')`)
- [ ] Middleware `check.akun.aktif` terdaftar di `app/Http/Kernel.php` atau `bootstrap/app.php`
- [ ] Semua Policy terdaftar di `AuthServiceProvider::$policies`
- [ ] `JabatanPolicy` CRUD tersedia untuk super_admin

### C — Frontend Web
- [ ] Halaman `admin/jabatan/index`: tabel daftar jabatan, sortable, filter aktif/nonaktif
- [ ] Halaman `admin/jabatan/create`: form tambah jabatan
- [ ] Halaman `admin/jabatan/edit`: form edit jabatan

### D — Security
- [ ] Akses halaman admin jabatan diblokir untuk role selain super_admin
- [ ] Gate `escalate-insiden` mengembalikan false untuk PCNU
- [ ] Gate `finalize-pleno` mengembalikan false untuk PCNU
- [ ] `cekScopeWilayah()` mengembalikan true untuk super_admin berapapun idPcnu-nya
- [ ] `cekScopeWilayah()` mengembalikan false untuk PCNU jika scope_id tidak cocok

### E — Testing
- [ ] `test_super_admin_dapat_akses_semua_route` ✓
- [ ] `test_pwnu_tidak_dapat_akses_super_admin_route` ✓
- [ ] `test_pcnu_dibatasi_scope_sendiri` ✓
- [ ] `test_relawan_tidak_dapat_akses_tanpa_assignment` ✓
- [ ] `test_gate_escalate_hanya_untuk_pwnu` ✓
- [ ] `test_gate_finalize_pleno_hanya_untuk_pwnu` ✓
- [ ] `test_helper_cek_scope_wilayah_benar_untuk_semua_kombinasi` ✓
- [ ] `test_helper_has_assignment_deteksi_penugasan_aktif` ✓

---

## M03 — MASTER DATA & SEEDER

### A — Database
- [ ] `DatabaseSeeder` berjalan tanpa error: `php artisan db:seed`
- [ ] `auth_roles` memiliki tepat 5 baris sesuai PRD
- [ ] `bencana_master_jenis` memiliki 13 baris sesuai SQL dump
- [ ] `master_satuan` memiliki 29 baris sesuai SQL dump
- [ ] `logistik_kategori` memiliki 7 baris sesuai SQL dump
- [ ] `auth_keahlian_master` memiliki 7 baris sesuai SQL dump
- [ ] `aset_master_kategori` memiliki 5 baris sesuai SQL dump
- [ ] `aset_master_jenis` memiliki 6 baris sesuai SQL dump
- [ ] `aset_master_status` memiliki 5 baris (id 1–5) sesuai SQL dump
- [ ] `operasi_master_klaster` memiliki 6 baris sesuai SQL dump
- [ ] `master_jabatan` memiliki 15 baris default sesuai SQL dump

### B — Backend
- [ ] Semua model master data tersedia: `BencanaMasterJenis`, `MasterSatuan`, `LogistikKategori`, `AsetMasterKategori`, `AsetMasterJenis`, `AsetMasterStatus`, `OperasiMasterKlaster`
- [ ] Semua model master memiliki `public $timestamps = false`
- [ ] Cache API master data: `Cache::remember('bencana_master_jenis', 3600, fn() => ...)`
- [ ] Cache dapat di-clear via `php artisan cache:clear`
- [ ] CRUD panel `MasterSuratJenisController` tersedia untuk super_admin
- [ ] CRUD panel `MasterJabatanPenandatanganController` tersedia untuk super_admin

### C — Frontend Web
- [ ] Halaman `admin/master/index`: dashboard master data dengan link ke semua sub-master
- [ ] Halaman `admin/master/surat-jenis/index`: list jenis surat + format nomor
- [ ] Halaman `admin/master/surat-jenis/create`: form tambah jenis surat
- [ ] Halaman `admin/master/jabatan-penandatangan/index`: list jabatan penandatangan
- [ ] Halaman `admin/master/jabatan-penandatangan/create`: form tambah jabatan

### D — Security
- [ ] Semua halaman admin master diblokir untuk role selain super_admin
- [ ] API master data (`/api/master/*`) mengembalikan 401 untuk guest

### E — Testing
- [ ] `test_semua_seeder_berhasil_tanpa_error` ✓
- [ ] `test_auth_roles_memiliki_5_role_PRD` ✓
- [ ] `test_bencana_master_jenis_13_baris` ✓
- [ ] `test_master_satuan_29_baris` ✓
- [ ] `test_operasi_master_klaster_6_baris` ✓
- [ ] `test_api_master_bencana_jenis_terlihat_oleh_pcnu` ✓
- [ ] `test_api_master_tidak_terlihat_oleh_guest` ✓

---

## M04 — INSIDEN & LAPORAN KEJADIAN

### A — Database
- [ ] Migration `laporan_kejadian` berhasil: semua kolom sesuai SQL dump
- [ ] Migration `operasi_insiden` berhasil: semua kolom dan ENUM sesuai SQL dump
- [ ] Migration `riwayat_status_insiden` berhasil dengan FK ke `operasi_insiden.id_insiden` dan `auth_users.id_pengguna`
- [ ] ENUM `status_insiden` terdefinisi: `draft`,`terverifikasi`,`respon`,`pemulihan`,`selesai`,`dibatalkan`
- [ ] ENUM `status_operasi` terdefinisi: `monitoring`,`siaga`,`tanggap_darurat`,`pemulihan`,`selesai`
- [ ] ENUM `prioritas` terdefinisi: `rendah`,`sedang`,`tinggi`,`kritis`
- [ ] Kolom `is_locked` TINYINT(1) DEFAULT 0 terdefinisi di `operasi_insiden`
- [ ] Kolom `dihapus_pada` ada di `operasi_insiden` dan `riwayat_status_insiden`
- [ ] Trigger `tr_validate_temporal_incident` aktif: `waktu_selesai < waktu_mulai` → SIGNAL error
- [ ] Trigger `tr_lock_incident_data` aktif: UPDATE saat `is_locked=1` → SIGNAL error
- [ ] Trigger `tr_validate_coords_laporan` aktif: koordinat di luar batas → SIGNAL error

### B — Backend
- [ ] `OperasiInsiden` model: semua atribut sesuai SQL, `use SoftDeletes`, `const DELETED_AT = 'dihapus_pada'`
- [ ] `LaporanKejadian` model: semua atribut sesuai SQL
- [ ] `RiwayatStatusInsiden` model: semua atribut sesuai SQL, `use SoftDeletes`
- [ ] Relasi `OperasiInsiden hasMany OperasiSitrep` via `id_insiden`
- [ ] Relasi `OperasiInsiden hasMany AssessmentUtama` via `id_insiden`
- [ ] Relasi `OperasiInsiden hasMany OperasiPenugasan` via `id_insiden`
- [ ] Relasi `OperasiInsiden hasMany RiwayatStatusInsiden` via `id_insiden`
- [ ] Relasi `OperasiInsiden hasMany OperasiJurnal` via `id_insiden`
- [ ] Relasi `OperasiInsiden belongsTo BencanaMasterJenis` via `id_jenis_bencana`
- [ ] Relasi `OperasiInsiden belongsTo LaporanKejadian` via `id_laporan_asal`
- [ ] `OperasiInsiden::scopeByWilayah()` memfilter berdasarkan `id_pcnu` sesuai scope user
- [ ] `OperasiInsiden::scopeAktif()` mengecualikan `selesai` dan `dibatalkan`
- [ ] `kode_kejadian` di-generate otomatis format `INS-YYYYMM-{seq}` di Service
- [ ] `StoreLaporanKejadianRequest`: validasi koordinat `latitude between:-15,10`, `longitude between:90,150`
- [ ] `StoreInsidenRequest`: semua kolom wajib tervalidasi
- [ ] `TransisiStatusInsidenRequest`: `alasan` required_if `dibatalkan`
- [ ] `InsidenService::transisiStatus()` mengeksekusi dalam `DB::transaction()`:
  - [ ] Update `status_insiden` di `operasi_insiden`
  - [ ] Update kolom waktu terkait (`waktu_verifikasi`, `waktu_respon_dimulai`, dll)
  - [ ] Set `is_locked = 1` saat status = `selesai`
  - [ ] INSERT ke `riwayat_status_insiden`
  - [ ] INSERT ke `operasi_jurnal` via `JurnalService::catat()`
- [ ] `InsidenPolicy`: semua aksi terdefinisi (viewAny, view, create, update, delete, transisiStatus, validasiLaporan)
- [ ] `LaporanKejadianPolicy`: store (publik), validasi (pcnu/pwnu)
- [ ] `InsidenController`: index, show, create, store, edit, update, destroy
- [ ] `InsidenStatusController::update()`: handle transisi melalui `InsidenService`
- [ ] `LaporanKejadianController`: store (publik tanpa auth), indexInternal, show, validasi

### C — Frontend Web
- [ ] Halaman `publik/laporan/create`: form laporan publik, peta Leaflet untuk pilih koordinat, tanpa auth
- [ ] Halaman `publik/laporan/sukses`: konfirmasi pengiriman laporan
- [ ] Halaman `operasi/insiden/index`: tabel dengan filter status, prioritas, tanggal; pagination 15
- [ ] Halaman `operasi/insiden/show`: tab-based (Info Umum | Assessment | Sitrep | Klaster | Logistik | Personel | Jurnal)
- [ ] Halaman `operasi/insiden/create`: form lengkap, dropdown jenis bencana
- [ ] Halaman `operasi/insiden/edit`: form edit, disabled jika `is_locked=1`
- [ ] Halaman `operasi/laporan/index`: daftar laporan menunggu validasi, filter `is_valid`
- [ ] Halaman `operasi/laporan/show`: detail laporan + tombol validasi/tolak
- [ ] Badge status warna benar: abu-abu/biru/oranye/kuning/hijau/merah sesuai status
- [ ] Banner `⚠️ INSIDEN TERKUNCI` tampil jika `is_locked = 1`, semua form disabled
- [ ] Tombol transisi status tampil kondisional berdasarkan `$user->can('transisiStatus', $insiden)`
- [ ] Tabel insiden menampilkan `kode_kejadian`, `jenis_bencana`, `prioritas`, `status_insiden`, `waktu_mulai`

### D — Security
- [ ] Guest tidak dapat akses `/insiden/*` (redirect ke login)
- [ ] PCNU hanya melihat insiden dengan `id_pcnu = default_scope_id` di semua query
- [ ] PCNU tidak dapat membuat insiden di `id_pcnu` milik PCNU lain
- [ ] Publik dapat submit laporan di `/publik/laporan` tanpa login
- [ ] Insiden dengan `is_locked = 1` mengembalikan 403 untuk semua aksi update
- [ ] Transisi `selesai → apapun` ditolak oleh Policy
- [ ] `dibatalkan` hanya bisa dilakukan oleh id_peran ∈ [1,2]
- [ ] Setiap transisi status tercatat ke `operasi_jurnal`

### E — Testing
- [ ] `test_pcnu_hanya_melihat_insiden_scope_sendiri` ✓
- [ ] `test_pwnu_melihat_semua_insiden_lintas_pcnu` ✓
- [ ] `test_kode_kejadian_unik_tidak_duplikat` ✓
- [ ] `test_waktu_selesai_sebelum_waktu_mulai_ditolak` ✓ (trigger DB)
- [ ] `test_transisi_draft_ke_terverifikasi` ✓
- [ ] `test_transisi_selesai_ke_apapun_ditolak` ✓
- [ ] `test_transisi_mencatat_riwayat_status` ✓
- [ ] `test_transisi_mencatat_jurnal_operasi` ✓
- [ ] `test_insiden_terkunci_setelah_selesai` ✓
- [ ] `test_is_locked_mencegah_update` ✓ (trigger DB)
- [ ] `test_laporan_publik_tanpa_login` ✓
- [ ] `test_koordinat_di_luar_indonesia_ditolak` ✓ (trigger DB + FormRequest)
- [ ] `test_publik_tidak_dapat_buat_insiden` ✓

---

## M05 — ASSESSMENT

### A — Database
- [ ] Migration `assessment_utama` berhasil: semua kolom termasuk `waktu_assesment` (typo sesuai SQL — IKUTI SQL)
- [ ] Migration `assessment_dampak_manusia` berhasil dengan FK ke `assessment_utama.id_assessment_utama`
- [ ] Migration `assessment_kebutuhan_mendesak` berhasil dengan FK ke `assessment_utama.id_assessment_utama`
- [ ] ENUM `jenis_laporan` terdefinisi: `kaji_cepat`, `pendataan_lanjutan`
- [ ] Kolom `is_latest` TINYINT(1) DEFAULT 1 terdefinisi
- [ ] Kolom `dihapus_pada` ada di `assessment_utama`
- [ ] Trigger `tr_single_latest_assessment` aktif: INSERT dengan `is_latest=1` → set `is_latest=0` pada assessment lain dalam insiden yang sama

### B — Backend
- [ ] `AssessmentUtama` model: `use SoftDeletes`, `const DELETED_AT='dihapus_pada'`, `$primaryKey='id_assessment_utama'`
- [ ] **PERHATIAN**: `$fillable` menggunakan `waktu_assesment` (bukan `waktu_assessment`) sesuai SQL
- [ ] Relasi `AssessmentUtama belongsTo OperasiInsiden` via `id_insiden`
- [ ] Relasi `AssessmentUtama hasOne AssessmentDampakManusia` via `id_assessment`
- [ ] Relasi `AssessmentUtama hasOne AssessmentKebutuhanMendesak` via `id_assessment`
- [ ] `StoreAssessmentRequest`: validasi koordinat `between:-15,10` dan `between:90,150`, validasi `jenis_laporan` enum
- [ ] `AssessmentController::store()` menyimpan ketiga tabel dalam `DB::transaction()`
- [ ] `AssessmentPolicy`: create, view, delete (soft); delete ditolak jika assessment menjadi basis sitrep
- [ ] Logic pencegahan delete: cek `operasi_sitrep` di mana `id_assessment_basis = id_assessment_utama`

### C — Frontend Web
- [ ] Halaman `operasi/assessment/index`: list assessment per insiden, badge `is_latest`
- [ ] Halaman `operasi/assessment/create`: form dengan section dampak manusia + kebutuhan mendesak
- [ ] Halaman `operasi/assessment/show`: detail lengkap, badge `is_latest`, readonly

### D — Security
- [ ] Assessment tidak dapat dibuat untuk insiden `draft`
- [ ] Assessment tidak dapat dibuat untuk insiden `selesai` (is_locked)
- [ ] Relawan hanya dapat buat assessment jika memiliki assignment aktif `trc` di insiden tersebut
- [ ] Penghapusan assessment yang menjadi basis sitrep ditolak dengan pesan yang jelas

### E — Testing
- [ ] `test_assessment_baru_set_is_latest_dan_reset_sebelumnya` ✓ (trigger DB)
- [ ] `test_assessment_tersimpan_beserta_dampak_dan_kebutuhan` ✓
- [ ] `test_assessment_tidak_bisa_untuk_insiden_draft` ✓
- [ ] `test_koordinat_di_luar_indonesia_ditolak` ✓
- [ ] `test_soft_delete_assessment_berhasil` ✓
- [ ] `test_assessment_basis_sitrep_tidak_dapat_dihapus` ✓
- [ ] `test_relawan_trc_dapat_buat_assessment` ✓
- [ ] `test_relawan_tanpa_assignment_tidak_dapat_buat_assessment` ✓

---

## M06 — SITREP

### A — Database
- [ ] Migration `operasi_sitrep` berhasil: semua kolom sesuai SQL dump
- [ ] ENUM `status_sitrep` terdefinisi: `draft`, `ditinjau`, `final`
- [ ] Kolom `snapshot_dampak` LONGTEXT dengan `CHECK (json_valid())` terdefinisi
- [ ] Kolom `hash_snapshot` VARCHAR(64) terdefinisi
- [ ] Kolom `id_penfinalisasi` dengan FK ke `auth_users.id_pengguna` terdefinisi
- [ ] Kolom `dihapus_pada` terdefinisi
- [ ] Trigger `tr_auto_snapshot_sitrep` aktif: INSERT → auto-populate `snapshot_dampak` dari assessment terkait insiden
- [ ] Trigger `tr_auto_snapshot_sitrep_update` aktif: UPDATE non-final → update snapshot; final → snapshot tidak berubah

### B — Backend
- [ ] `OperasiSitrep` model: `use SoftDeletes`, `const DELETED_AT='dihapus_pada'`
- [ ] `$casts`: `snapshot_dampak` → `array`, `snapshot_logistik` → `array`, `waktu_difinalisasi` → `datetime`
- [ ] Relasi `OperasiSitrep belongsTo OperasiInsiden` via `id_insiden`
- [ ] Relasi `OperasiSitrep belongsTo AuthUser as petugas` via `id_petugas`
- [ ] Relasi `OperasiSitrep belongsTo AuthUser as penfinalisasi` via `id_penfinalisasi`
- [ ] Relasi `OperasiSitrep belongsTo AssessmentUtama` via `id_assessment_basis`
- [ ] `nomor_sitrep` di-generate di Service: `SELECT MAX(nomor_sitrep) + 1 FROM operasi_sitrep WHERE id_insiden = X`
- [ ] UNIQUE constraint (`id_insiden`, `nomor_sitrep`) ditegakkan: validasi di Service sebelum INSERT
- [ ] `StoreSitrepRequest`: validasi lengkap termasuk `status_sitrep` tidak boleh `final` saat create
- [ ] `SitrepService::finalisasi()` berjalan dalam `DB::transaction()`:
  - [ ] Validasi `status_sitrep = 'ditinjau'` (tidak boleh dari 'draft')
  - [ ] Update `status_sitrep='final'`, `waktu_difinalisasi`, `id_penfinalisasi`
  - [ ] Generate PDF via `SitrepPdfService`
  - [ ] Update `file_pdf_path`
  - [ ] Catat `operasi_jurnal`
- [ ] `SitrepPdfService::generate()` menghasilkan file PDF di `storage/app/public/sitrep/`
- [ ] `SitrepPolicy`: create (internal), finalisasi (Gate finalize-sitrep + scope), delete hanya draft
- [ ] Sitrep FINAL tidak dapat di-update oleh apapun (Policy + logika di Controller)

### C — Frontend Web
- [ ] Halaman `operasi/sitrep/index`: list urut `nomor_sitrep DESC`, badge status
- [ ] Halaman `operasi/sitrep/create`: form, dropdown assessment_basis
- [ ] Halaman `operasi/sitrep/show`: readonly, tampilkan `hash_snapshot`, snapshot data, tombol Download PDF jika final
- [ ] Halaman `operasi/sitrep/edit`: hanya untuk status `draft` atau `ditinjau`
- [ ] Tombol "Tinjau" tampil jika status `draft` dan user berwenang
- [ ] Tombol "Finalisasi" tampil jika status `ditinjau` dan user punya Gate `finalize-sitrep`
- [ ] Sitrep FINAL: semua form disabled, banner "FINAL 🔒" tampil, hash_snapshot tampil
- [ ] Template PDF `pdf/sitrep.blade.php`: format surat resmi NURISK

### D — Security
- [ ] Sitrep FINAL mengembalikan 403 untuk semua aksi edit/delete
- [ ] Hanya id_peran ∈ [1,2,3] yang dapat finalisasi (Gate)
- [ ] PCNU hanya dapat finalisasi sitrep dalam scope insiden sendiri
- [ ] `hash_snapshot` diverifikasi: nilainya SHA2(snapshot_dampak, 256)
- [ ] Semua aksi finalisasi dicatat ke `operasi_jurnal`

### E — Testing
- [ ] `test_nomor_sitrep_generate_otomatis_berurutan` ✓
- [ ] `test_nomor_sitrep_unik_per_insiden` ✓
- [ ] `test_snapshot_dampak_terisi_otomatis_saat_insert` ✓ (trigger DB)
- [ ] `test_sitrep_final_tidak_dapat_diedit` ✓
- [ ] `test_sitrep_draft_tidak_dapat_langsung_final` ✓
- [ ] `test_finalisasi_mengisi_waktu_dan_penfinalisasi` ✓
- [ ] `test_hash_snapshot_tidak_null_setelah_finalisasi` ✓
- [ ] `test_pdf_terbuat_dan_path_tersimpan` ✓
- [ ] `test_relawan_tidak_ditugaskan_tidak_dapat_buat_sitrep` ✓
- [ ] `test_pcnu_tidak_dapat_finalisasi_sitrep_luar_scope` ✓

---

## M07 — PLENO & ESKALASI

### A — Database
- [ ] Migration `operasi_pleno` berhasil: semua kolom sesuai SQL dump
- [ ] Migration `operasi_pleno_keputusan` berhasil dengan FK ke `operasi_pleno`
- [ ] Migration `operasi_pleno_peserta` berhasil dengan FK ke `operasi_pleno` dan `auth_users`
- [ ] Migration `operasi_eskalasi` berhasil: FK ke `operasi_insiden` dan `operasi_pleno`
- [ ] Migration `operasi_aktivasi` berhasil: FK ke `operasi_insiden` dan `auth_users`
- [ ] ENUM `status_kehadiran` di `operasi_pleno_peserta`: `hadir`, `izin`, `tanpa_keterangan`
- [ ] ENUM `status_persetujuan` di `operasi_pleno_peserta`: `setuju`, `tolak`, `abstain`
- [ ] ENUM `level_sebelumnya/level_baru` di `operasi_eskalasi`: `lokal`, `pcnu`, `pwnu`, `nasional`
- [ ] ENUM `status_darurat` di `operasi_aktivasi`: `siaga`, `tanggap_darurat`, `pemulihan`, `selesai`

### B — Backend
- [ ] Semua model pleno tersedia dengan relasi yang benar
- [ ] `OperasiPleno::keputusan()` hasMany `OperasiPlanoKeputusan`
- [ ] `OperasiPleno::peserta()` hasMany `OperasiPlenoPeserta`
- [ ] `OperasiEskalasi`: validasi level naik ditegakkan di Service sebelum INSERT
- [ ] `PlanoPolicy`: create (scope), finalisasi (Gate finalize-pleno = pwnu only)
- [ ] `EskalasiPolicy`: create hanya id_peran ∈ [1,2]
- [ ] Pleno FINAL: Policy mengembalikan false untuk semua aksi update/tambah keputusan
- [ ] Peserta `tolak` wajib isi `catatan_peserta`: validasi di FormRequest
- [ ] `StoreEskalasiRequest`: validasi `level_baru` harus berbeda dari `level_sebelumnya`
- [ ] `EskalasiService::buat()`: validasi level naik secara programatik (lokal<pcnu<pwnu<nasional)

### C — Frontend Web
- [ ] Halaman `governance/pleno/index`: list pleno per insiden, filter status
- [ ] Halaman `governance/pleno/show`: tab Info | Keputusan | Peserta
- [ ] Halaman `governance/pleno/create`: form pleno, pilih insiden
- [ ] Halaman `governance/pleno/peserta`: form tambah/voting peserta
- [ ] Halaman `governance/eskalasi/create`: form eskalasi, pilih pleno sebagai dasar
- [ ] Tombol "Finalisasi Pleno" hanya tampil untuk id_peran ∈ [1,2]
- [ ] Pleno FINAL: semua form disabled, badge "FINAL" tampil

### D — Security
- [ ] PCNU tidak dapat finalisasi pleno (Gate finalize-pleno)
- [ ] Eskalasi hanya bisa dilakukan oleh id_peran ∈ [1,2] (Gate escalate-insiden)
- [ ] Eskalasi wajib memiliki `id_pleno` yang valid — FK constraint + validasi FormRequest
- [ ] Eskalasi level turun ditolak oleh Service
- [ ] Pleno FINAL tidak dapat tambah keputusan baru
- [ ] Setiap eskalasi dicatat ke `operasi_jurnal` dengan kategori `sistem`

### E — Testing
- [ ] `test_pcnu_dapat_buat_pleno_dalam_scope` ✓
- [ ] `test_pcnu_tidak_dapat_buat_pleno_luar_scope` ✓
- [ ] `test_pleno_final_tidak_dapat_diubah` ✓
- [ ] `test_pleno_final_tidak_dapat_tambah_keputusan` ✓
- [ ] `test_peserta_tolak_wajib_catatan` ✓
- [ ] `test_finalisasi_hanya_pwnu` ✓
- [ ] `test_pcnu_tidak_dapat_eskalasi` ✓
- [ ] `test_eskalasi_tanpa_pleno_ditolak` ✓
- [ ] `test_eskalasi_level_turun_ditolak` ✓
- [ ] `test_eskalasi_dicatat_jurnal` ✓

---

## M08 — SURAT MENYURAT

### A — Database
- [ ] Migration `operasi_surat_keluar` berhasil: semua kolom sesuai SQL dump
- [ ] Migration `dokumen_surat_paraf` berhasil: kolom `urutan` INT NOT NULL DEFAULT 1, FK ke `operasi_surat_keluar` dan `auth_users`
- [ ] Migration `dokumen_surat_tembusan` berhasil
- [ ] Migration `master_surat_jenis` berhasil: ENUM `kategori` (`UMUM`,`OPERASI`,`LOGISTIK`,`ASET`,`ORGANISASI`)
- [ ] Migration `master_surat_template` berhasil
- [ ] Migration `master_jabatan_penandatangan` berhasil
- [ ] ENUM `status_paraf` di `dokumen_surat_paraf`: `menunggu`, `disetujui`, `ditolak`

### B — Backend
- [ ] `DokumenSuratUtama` model: semua relasi terdefinisi
- [ ] `DokumenSuratUtama::paraf()` hasMany `DokumenSuratParaf` ordered by `urutan ASC`
- [ ] `DokumenSuratUtama::parafAktif()` hasOne: paraf dengan `urutan` terendah yang `status_paraf='menunggu'`
- [ ] `DokumenSuratUtama::tembusan()` hasMany `DokumenSuratTembusan`
- [ ] `NomorSuratService::generate()`: parsing `format_nomor` dari `master_surat_jenis`, mengisi sequence dan tahun
- [ ] `NomorSuratService` menghasilkan nomor yang unik — cek duplikat sebelum assign
- [ ] `SuratService::prosesParaf()` berjalan dalam `DB::transaction()`:
  - [ ] Jika `disetujui` + ada paraf berikutnya: aktifkan paraf urutan selanjutnya
  - [ ] Jika `disetujui` + tidak ada paraf lagi: ubah status surat ke `approved`
  - [ ] Jika `ditolak`: ubah status surat ke `draft`, reset semua paraf setelahnya ke `menunggu`
- [ ] `SuratPdfService::generate()`: generate PDF via dompdf, simpan ke `storage/app/public/surat/`
- [ ] `ParafSuratRequest`: `catatan` required jika `status_paraf = 'ditolak'`
- [ ] `SuratPolicy`: create, update (hanya draft), paraf (hanya pengguna yang tepat di urutan aktif), finalisasi ([1,2])
- [ ] Logika paraf: pengguna hanya bisa paraf jika `id_pengguna` cocok dengan paraf aktif

### C — Frontend Web
- [ ] Halaman `governance/surat/index`: list surat, filter jenis dan status, pagination
- [ ] Halaman `governance/surat/create`: form surat, pilih jenis, preview format nomor
- [ ] Halaman `governance/surat/show`: preview mirip surat resmi, timeline paraf, tembusan
- [ ] Halaman `governance/surat/edit`: hanya tersedia jika status `draft`
- [ ] Halaman `governance/surat/paraf`: form paraf untuk pengguna yang berwenang (urutan aktif)
- [ ] Template PDF `pdf/surat.blade.php`: format surat resmi dengan kop, nomor, isi, ttd
- [ ] Surat FINALIZED: watermark "FINAL", semua form disabled, tombol "Download PDF" tampil
- [ ] Timeline paraf: avatar + nama + status + waktu paraf per urutan
- [ ] Tombol "Generate PDF" HANYA tampil saat status = `finalized`

### D — Security
- [ ] Pengguna tidak berwenang tidak dapat paraf surat orang lain
- [ ] Pengguna hanya bisa paraf jika urutan parafnya adalah yang aktif saat ini
- [ ] Surat FINALIZED tidak dapat diedit oleh siapapun
- [ ] Nomor surat tidak dapat diubah setelah surat dibuat
- [ ] PDF hanya bisa didownload oleh pengguna yang berwenang (scope surat)
- [ ] Setiap aksi paraf (disetujui/ditolak) dicatat ke `operasi_jurnal`

### E — Testing
- [ ] `test_nomor_surat_generate_otomatis_unik` ✓
- [ ] `test_paraf_berurutan_tidak_bisa_lompat` ✓
- [ ] `test_paraf_disetujui_aktifkan_berikutnya` ✓
- [ ] `test_semua_paraf_disetujui_status_approved` ✓
- [ ] `test_paraf_ditolak_reset_surat_ke_draft` ✓
- [ ] `test_paraf_ditolak_tanpa_catatan_ditolak_validasi` ✓
- [ ] `test_surat_finalized_tidak_dapat_diedit` ✓
- [ ] `test_pdf_terbuat_setelah_finalisasi` ✓
- [ ] `test_pengguna_tidak_berwenang_tidak_dapat_paraf` ✓

---

## M09 — ASSIGNMENT & OTORITAS KONTEKSTUAL

### A — Database
- [ ] Migration `operasi_penugasan` berhasil: PK `id_incident_assignment`, ENUM `peran_otoritas`, FK ke insiden dan auth_users
- [ ] Migration `operasi_otoritas_kontekstual` berhasil
- [ ] ENUM `peran_otoritas` terdefinisi: `komandan_insiden`,`trc`,`relawan`,`medis`,`logistik`,`operator`
- [ ] Kolom `dihapus_pada` ada di `operasi_penugasan`
- [ ] Kolom `asal_lingkup` dan `tujuan_lingkup` VARCHAR(100) terdefinisi

### B — Backend
- [ ] `OperasiPenugasan` model: `$primaryKey='id_incident_assignment'`, `use SoftDeletes`
- [ ] `OperasiPenugasan::scopeAktif()`: filter `waktu_selesai IS NULL`
- [ ] Relasi ke `OperasiInsiden`, `AuthUser as pengguna`, `AuthUser as penugasOleh`
- [ ] `BaseNuriskPolicy::hasAssignment()` query ke `operasi_penugasan` — wajib gunakan tabel ini
- [ ] `PenugasanPolicy`: create (scope), akhiri (scope atau komandan_insiden aktif)
- [ ] `StorePenugasanRequest`: `peran_otoritas` enum valid, `waktu_selesai` after `waktu_mulai`

### C — Frontend Web
- [ ] Halaman `operasi/penugasan/index`: list penugasan aktif per insiden, badge peran
- [ ] Halaman `operasi/penugasan/create`: form penugasan, dropdown pengguna, pilih peran
- [ ] Halaman `operasi/penugasan/show`: detail penugasan, tombol "Akhiri Penugasan"
- [ ] Kolom `asal_lingkup` dan `tujuan_lingkup` tampil jika cross-region

### D — Security
- [ ] PCNU tidak dapat menugaskan personel ke insiden di luar scope-nya
- [ ] Cross-region assignment: `auth_users.id_unit` TIDAK berubah — diverifikasi di test
- [ ] Penugasan aktif yang sudah berakhir (`waktu_selesai` terisi) tidak dihitung sebagai aktif
- [ ] `ditugaskan_oleh` selalu diisi dengan `id_pengguna` yang sedang login

### E — Testing
- [ ] `test_pcnu_dapat_menugaskan_relawan_dalam_scope` ✓
- [ ] `test_cross_region_assignment_tercatat_asal_tujuan` ✓
- [ ] `test_organisasi_asal_tidak_berubah_setelah_assignment` ✓
- [ ] `test_penugasan_berakhir_saat_waktu_selesai_diisi` ✓
- [ ] `test_relawan_ditugaskan_dapat_buat_sitrep` ✓
- [ ] `test_relawan_tidak_ditugaskan_tidak_dapat_buat_sitrep` ✓

---

## M10 — MOBILISASI PERSONEL & KLASTER

### A — Database
- [ ] Migration `operasi_mobilisasi_personil` berhasil: ENUM `status_kehadiran`
- [ ] Migration `operasi_klaster` berhasil: ENUM `status_klaster`, `prioritas`
- [ ] Migration `operasi_klaster_koordinator` berhasil: FK ke `operasi_klaster` dan `auth_users`
- [ ] ENUM `status_kehadiran`: `menuju_lokasi`, `di_lokasi`, `kembali`, `izin`
- [ ] ENUM `status_klaster`: `nonaktif`, `aktif`, `selesai`
- [ ] Kolom `progres_persen` DECIMAL(5,2) DEFAULT 0.00 terdefinisi

### B — Backend
- [ ] Semua model klaster dan mobilisasi tersedia
- [ ] `OperasiKlaster::koordinator()` hasMany `OperasiKlasterKoordinator`
- [ ] `OperasiKlasterKoordinator`: FK `id_pleno_penunjukan` ke `operasi_pleno_keputusan`
- [ ] Penunjukan koordinator wajib memiliki `id_pleno_penunjukan` yang valid
- [ ] Klaster `selesai` tidak dapat diubah ke `aktif` — validasi di Service

### C — Frontend Web
- [ ] Halaman `operasi/klaster/index`: 6 klaster dengan badge status dan progres bar
- [ ] Halaman `operasi/klaster/show`: detail klaster + koordinator + indikator
- [ ] Halaman `operasi/mobilisasi/index`: daftar personel aktif + status kehadiran, dapat diupdate

### D — Security
- [ ] Koordinator klaster hanya bisa diubah oleh id_peran ∈ [1,2,3] dalam scope
- [ ] Update `progres_persen` hanya bisa oleh koordinator klaster atau pcnu/pwnu

### E — Testing
- [ ] `test_6_klaster_tersedia_per_insiden` ✓
- [ ] `test_koordinator_wajib_pleno_penunjukan` ✓
- [ ] `test_klaster_selesai_tidak_dapat_aktif_kembali` ✓
- [ ] `test_status_kehadiran_dapat_diupdate` ✓

---

## M11 — SHIFT / PERIODE OPERASIONAL

### A — Database
- [ ] Migration `operasi_periode` berhasil: FK `id_pleno_keputusan` ke `operasi_pleno_keputusan`
- [ ] ENUM `status_periode`: `berjalan`, `selesai`, `diperpanjang`
- [ ] Kolom `tanggal_mulai` DATE NOT NULL, `tanggal_selesai` DATE NOT NULL

### B — Backend
- [ ] `OperasiPeriode` model: `$primaryKey='id_periode_operasi'`, `public $timestamps = false`
- [ ] Relasi `belongsTo OperasiPlanoKeputusan via id_pleno_keputusan`
- [ ] `StorePeriodeRequest`: `tanggal_selesai` wajib `after_or_equal:tanggal_mulai`, `id_pleno_keputusan` wajib exists
- [ ] Periode `selesai` tidak dapat diubah — validasi di Policy

### C — Frontend Web
- [ ] Halaman `operasi/periode/index`: timeline periode per insiden
- [ ] Halaman `operasi/periode/create`: form dengan dropdown keputusan pleno

### D — Security
- [ ] Periode baru wajib ada `id_pleno_keputusan` yang valid
- [ ] Perpanjangan periode menghasilkan periode baru (bukan edit periode lama)

### E — Testing
- [ ] `test_periode_wajib_keputusan_pleno` ✓
- [ ] `test_tanggal_selesai_tidak_boleh_sebelum_mulai` ✓
- [ ] `test_periode_selesai_tidak_dapat_diubah` ✓

---

## M12 — LOGISTIK

### A — Database
- [ ] Migration `logistik_gudang` berhasil: kolom `id_pcnu` NULL berarti gudang PWNU
- [ ] Migration `logistik_barang_katalog` berhasil: FK ke `logistik_kategori` dan `master_satuan`
- [ ] Migration `logistik_stok` berhasil: FK ke `operasi_posaju` dan `logistik_gudang`
- [ ] Migration `logistik_mutasi` berhasil: kolom `uuid_mutasi` CHAR(36) NOT NULL UNIQUE
- [ ] Migration `logistik_permintaan` berhasil: ENUM `status_permintaan`, FK valid
- [ ] Migration `logistik_perencanaan` berhasil
- [ ] ENUM `tipe_mutasi`: `masuk`, `keluar`, `penyesuaian`
- [ ] ENUM `status_permintaan`: `draft`,`diajukan`,`disetujui`,`ditolak`,`dikirim`,`selesai`
- [ ] Kolom `dihapus_pada` ada di `logistik_permintaan`
- [ ] Trigger `tr_execute_logistik_stok_update` aktif: INSERT ke `logistik_mutasi` → update `logistik_stok.jumlah_tersedia`
- [ ] Trigger `tr_logistik_mutasi_integrity_guard` aktif: keluar > stok → SIGNAL error
- [ ] Trigger `tr_validate_stock_ownership` aktif: gudang PCNU-A tidak suplai insiden PCNU-B → SIGNAL error
- [ ] Trigger `tr_validate_logistik_request_scope` aktif: posaju tujuan harus dalam insiden yang sama

### B — Backend
- [ ] **KRITIS**: Tidak ada mutator `setJumlahTersediaAttribute()` di `LogistikStok` — nilai HANYA diubah via trigger
- [ ] `LogistikMutasi::boot()` auto-generate `uuid_mutasi` via `Str::uuid()` saat creating
- [ ] `LogistikMutasiService::catat()` berjalan dalam `DB::transaction()`:
  - [ ] Validasi `jumlah > 0`
  - [ ] Jika `keluar`: cek stok cukup di aplikasi level (backup sebelum trigger)
  - [ ] INSERT ke `logistik_mutasi` (trigger DB otomatis update stok)
  - [ ] Catat `operasi_jurnal` via `JurnalService::catat()`
- [ ] Tidak ada route `PATCH /logistik/stok/{id}` yang mengubah `jumlah_tersedia` langsung
- [ ] `LogistikGudangPolicy::view()`: PCNU hanya akses gudang dengan `id_pcnu = default_scope_id`
- [ ] `LogistikPermintaanPolicy::approve()`: id_peran ∈ [1,2,3]
- [ ] ENUM `prioritas` permintaan: `biasa`, `mendesak`, `darurat` (beda dengan prioritas insiden)
- [ ] ENUM `prioritas` perencanaan: `darurat`, `mendesak`, `normal`

### C — Frontend Web
- [ ] Halaman `logistik/gudang/index`: list gudang per scope, filter aktif
- [ ] Halaman `logistik/gudang/show`: tabel stok, highlight merah jika stok kritis
- [ ] Halaman `logistik/mutasi/index`: log mutasi chronologis per stok/gudang
- [ ] Halaman `logistik/mutasi/create`: form: pilih tipe, jumlah (tampilkan stok saat ini), asal_tujuan
- [ ] Halaman `logistik/permintaan/index`: kanban board (`draft|diajukan|disetujui|dikirim|selesai`)
- [ ] Halaman `logistik/permintaan/create`: form permintaan barang
- [ ] Stok kritis: highlight merah jika `jumlah_tersedia < threshold`
- [ ] Form mutasi menampilkan `jumlah_tersedia` saat ini sebagai referensi user

### D — Security
- [ ] PCNU tidak dapat akses gudang milik PCNU lain (404 atau 403)
- [ ] PWNU dapat akses semua gudang
- [ ] Mutasi keluar melebihi stok: ditolak oleh trigger DB dengan error yang jelas di UI
- [ ] Gudang PCNU-A tidak bisa suplai insiden PCNU-B: ditolak oleh trigger DB
- [ ] Semua mutasi logistik signifikan dicatat ke `operasi_jurnal` kategori `logistik`
- [ ] `uuid_mutasi` selalu unik — tidak pernah duplikat

### E — Testing
- [ ] `test_mutasi_masuk_menambah_jumlah_tersedia` ✓ (via trigger)
- [ ] `test_mutasi_keluar_mengurangi_jumlah_tersedia` ✓ (via trigger)
- [ ] `test_mutasi_keluar_melebihi_stok_ditolak` ✓ (trigger + Service)
- [ ] `test_mutasi_penyesuaian_set_nilai_absolut` ✓
- [ ] `test_uuid_mutasi_unik` ✓
- [ ] `test_update_langsung_stok_tidak_tersedia_via_route` ✓
- [ ] `test_mutasi_dicatat_jurnal` ✓
- [ ] `test_pcnu_tidak_dapat_akses_gudang_lain` ✓
- [ ] `test_gudang_pcnu_a_tidak_suplai_insiden_pcnu_b` ✓ (trigger DB)
- [ ] `test_alur_permintaan_lengkap_draft_ke_selesai` ✓

---

## M13 — RELAWAN

### A — Database
- [ ] Migration `relawan_pendaftaran` berhasil: UNIQUE constraint `(id_pengguna, id_relawan_kebutuhan)`
- [ ] Migration `relawan_penugasan` berhasil
- [ ] Kolom soft delete (jika ada) terdefinisi dengan benar

### B — Backend
- [ ] `RelawanPendaftaran` model: relasi ke `auth_users`, keahlian
- [ ] Validasi UNIQUE constraint ditangani di FormRequest (exists + unique rule) sebelum DB error
- [ ] `RelawanPendaftaranPolicy::approve()`: hanya id_peran ∈ [1,2,3] dalam scope
- [ ] Relawan belum terverifikasi (status bukan `aktif`) tidak dapat ditugaskan — dicek di `PenugasanPolicy`

### C — Frontend Web
- [ ] Halaman `relawan/pendaftaran/index`: list pendaftaran, filter status
- [ ] Halaman `relawan/pendaftaran/create`: form daftar untuk kebutuhan relawan
- [ ] Halaman `relawan/pendaftaran/show`: detail + form approval untuk PCNU/PWNU

### D — Security
- [ ] Relawan tidak dapat mendaftar dua kali untuk kebutuhan yang sama
- [ ] PCNU hanya approve pendaftaran dalam scope sendiri
- [ ] Cross-region: `auth_users.id_unit` tidak berubah setelah assignment
- [ ] Keahlian relawan tampil di profil pengguna

### E — Testing
- [ ] `test_relawan_dapat_mendaftar` ✓
- [ ] `test_daftar_ganda_kebutuhan_sama_ditolak` ✓
- [ ] `test_relawan_belum_terverifikasi_tidak_dapat_ditugaskan` ✓
- [ ] `test_pcnu_approve_dalam_scope` ✓
- [ ] `test_cross_region_tidak_ubah_organisasi_asal` ✓

---

## M14 — ASET

### A — Database
- [ ] Migration `aset_unit` berhasil: ENUM `kondisi_fisik` (`baik`,`rusak_ringan`,`rusak_berat`), FK ke `aset_master_status`
- [ ] Migration `aset_penggunaan` berhasil: FK ke `aset_unit`, `operasi_insiden`, `auth_users`
- [ ] Trigger `tr_prevent_double_booking_aset` aktif: INSERT saat `id_status != 1` → SIGNAL error; jika iya set `id_status=2`
- [ ] Trigger `tr_aset_return_to_available` aktif: UPDATE `waktu_kembali` NULL→NOT NULL → set `id_status=1`

### B — Backend
- [ ] `AsetUnit` model: `$primaryKey='id_unit_aset'`, relasi ke `AsetMasterStatus`, `AsetMasterJenis`
- [ ] `AsetPenggunaan` model: relasi ke `AsetUnit`, `OperasiInsiden`, `AuthUser`
- [ ] `AsetPolicy::pinjam()`: id_peran ∈ [1,2,3], aset harus `id_status=1`
- [ ] `PinjamAsetRequest`: `id_insiden` exists, `tujuan_penggunaan` required
- [ ] `KembalikanAsetRequest`: `kondisi_akhir` nullable string

### C — Frontend Web
- [ ] Halaman `aset/index`: tabel aset + badge status + filter status dan kondisi
- [ ] Halaman `aset/show`: detail aset + histori peminjaman
- [ ] Halaman `aset/create`: daftarkan aset baru
- [ ] Halaman `aset/pinjam`: form peminjaman, hanya aset dengan status Tersedia yang dapat dipilih

### D — Security
- [ ] Aset `id_status != 1` tidak dapat dipinjam (trigger DB + validasi Policy)
- [ ] Double-booking dicegah oleh trigger `tr_prevent_double_booking_aset`
- [ ] PCNU hanya akses aset dengan `id_pemilik_unit` sesuai scope
- [ ] Pengembalian aset dicatat ke `operasi_jurnal` kategori `aset`

### E — Testing
- [ ] `test_aset_tersedia_dapat_dipinjam` ✓
- [ ] `test_aset_tidak_tersedia_tidak_dapat_dipinjam` ✓ (trigger DB)
- [ ] `test_pinjam_otomatis_set_status_dalam_tugas` ✓ (trigger DB)
- [ ] `test_kembalikan_otomatis_set_status_tersedia` ✓ (trigger DB)
- [ ] `test_kondisi_akhir_tersimpan_saat_kembali` ✓

---

## M15 — POS AJU

### A — Database
- [ ] Migration `operasi_posaju` berhasil: kolom status, koordinat GPS, FK ke insiden
- [ ] Migration `operasi_posaju_komandan` berhasil: FK `id_pleno_penunjukan` ke `operasi_pleno_keputusan` dan FK ke `auth_users`
- [ ] Status pos aju terdefinisi sesuai SQL dump

### B — Backend
- [ ] `OperasiPosaju` model: relasi ke `OperasiInsiden`, `OperasiPosajuKomandan`, `LogistikStok`
- [ ] `OperasiPosajuKomandan`: FK `id_pleno_penunjukan` wajib tidak null
- [ ] `PosajuPolicy::tutup()`: hanya id_peran ∈ [1,2] atau komandan insiden aktif
- [ ] Pos aju DITUTUP: Policy mengembalikan false untuk semua aksi kecuali view
- [ ] `StorePosajuRequest`: validasi koordinat GPS wajib, `id_pleno_keputusan` wajib exists

### C — Frontend Web
- [ ] Halaman `operasi/posaju/index`: list pos aju per insiden + peta mini Leaflet dengan marker
- [ ] Halaman `operasi/posaju/show`: detail + stok + komandan + personel di lokasi
- [ ] Halaman `operasi/posaju/create`: form + pilih lokasi di peta Leaflet
- [ ] Pos aju DITUTUP: semua form disabled, badge "DITUTUP" merah

### D — Security
- [ ] Pos aju DITUTUP tidak dapat diaktifkan kembali — Policy + tidak ada route untuk ini
- [ ] Pembukaan pos aju wajib ada keputusan pleno — FK + FormRequest validation
- [ ] Komandan pos aju wajib ditunjuk via pleno — FK `id_pleno_penunjukan` required
- [ ] Stok pos aju berasal dari gudang scope yang sesuai insiden (trigger `tr_validate_stock_ownership`)

### E — Testing
- [ ] `test_posaju_wajib_keputusan_pleno` ✓
- [ ] `test_komandan_posaju_wajib_pleno_penunjukan` ✓
- [ ] `test_posaju_ditutup_tidak_dapat_aktif_kembali` ✓
- [ ] `test_marker_posaju_tampil_di_api` ✓

---

## M16 — PENGUNGSIAN & PENERIMA MANFAAT

### A — Database
- [ ] Migration `master_penerima_manfaat` berhasil: ENUM `tipe_penerima`, kolom `nik_penerima`
- [ ] ENUM `tipe_penerima`: `individu`,`kk`,`kelompok`,`posko`,`desa`,`lembaga`

### B — Backend
- [ ] `MasterPenerimaBanfaat` model: `$table='master_penerima_manfaat'`, `$primaryKey='id_penerima'`
- [ ] `StorePenerimaBanfaatRequest`: `nik_penerima` required jika `tipe_penerima = 'individu'` (digits:16)
- [ ] `PenerimaBanfaatPolicy`: view dan create sesuai role

### C — Frontend Web
- [ ] Halaman `pengungsian/penerima/index`: list penerima manfaat, filter tipe
- [ ] Halaman `pengungsian/penerima/create`: form dengan conditional field NIK
- [ ] Halaman `pengungsian/penerima/show`: detail penerima manfaat

### D — Security
- [ ] Data pengungsi tidak tampil di public UI
- [ ] PCNU hanya akses data pengungsi dalam scope insiden sendiri

### E — Testing
- [ ] `test_individu_wajib_nik` ✓
- [ ] `test_tipe_penerima_valid` ✓
- [ ] `test_data_penerima_dapat_disimpan` ✓

---

## M17 — JURNAL OPERASI & AUDIT TRAIL

### A — Database
- [ ] Migration `operasi_jurnal` berhasil: ENUM `kategori_event`, kolom `id_referensi` + `tabel_referensi`
- [ ] ENUM `kategori_event`: `sistem`,`laporan`,`aktivasi`,`respon`,`penugasan`,`logistik`,`aset`,`personil`,`posko`,`selesai`
- [ ] **Tidak ada kolom `dihapus_pada`** di `operasi_jurnal` — jurnal tidak boleh dihapus

### B — Backend
- [ ] `OperasiJurnal` model: `const CREATED_AT='waktu_event'`, `const UPDATED_AT=null`, `public $timestamps=false`
- [ ] `OperasiJurnal::entitasReferensi()`: method manual (bukan relasi Eloquent) dengan map tabel ke Model class
- [ ] `JurnalService::catat()` tersedia:
  - [ ] Parameter: `idInsiden`, `kategori`, `judul`, `aktor`, `deskripsi`, `idReferensi`, `tabelReferensi`
  - [ ] Menggunakan `OperasiJurnal::create()` langsung (TIDAK dalam transaction — best effort)
- [ ] Semua service domain menggunakan `JurnalService::catat()` untuk event penting
- [ ] Tidak ada route `DELETE /jurnal/*` terdaftar
- [ ] `JurnalPolicy`: tidak ada aksi `delete`
- [ ] `StoreJurnalRequest`: `kategori_event` hanya nilai enum yang valid

### C — Frontend Web
- [ ] Halaman `operasi/jurnal/index`: timeline chronologis, filter per kategori, pagination
- [ ] Halaman `operasi/jurnal/create`: form manual jurnal oleh operator
- [ ] Icon berbeda per `kategori_event` di timeline UI

### D — Security
- [ ] Tidak ada route yang memungkinkan delete jurnal
- [ ] Jurnal hanya bisa ditulis oleh pengguna yang login (`id_pengguna` dari auth)
- [ ] Jurnal cross-insiden: PCNU hanya melihat jurnal insiden dalam scope-nya

### E — Testing
- [ ] `test_transisi_status_insiden_otomatis_catat_jurnal` ✓
- [ ] `test_mutasi_logistik_otomatis_catat_jurnal` ✓
- [ ] `test_route_delete_jurnal_tidak_tersedia` ✓ (assertRouteDoesNotExist atau assertNotFound)
- [ ] `test_filter_jurnal_per_kategori_berfungsi` ✓
- [ ] `test_manual_jurnal_dapat_dibuat_oleh_operator` ✓

---

## M18 — COMMAND CENTER & DASHBOARD

### A — Database
Tidak ada migration baru — menggunakan tabel yang sudah ada (read-only).

### B — Backend
- [ ] `DashboardController::index()`: aggregasi data per role dengan scope wilayah benar
- [ ] `CommandCenterController::index()`: render halaman command center
- [ ] `CommandCenterApiController`: 4 endpoint AJAX terdefinisi
- [ ] Semua query di API controller menggunakan scope wilayah yang benar
- [ ] Query aggregasi dashboard menggunakan `DB::select()` untuk performa optimal
- [ ] Response API: JSON yang efisien (hanya field yang diperlukan, bukan full model)
- [ ] Cache: data statis dashboard di-cache, data real-time tidak di-cache

### C — Frontend Web
- [ ] Halaman `dashboard/index`: ringkasan per role, card statistik
- [ ] Halaman `dashboard/command-center`: map Leaflet.js sebagai elemen utama
- [ ] Peta Leaflet: base layer OpenStreetMap, marker clustering, custom icon per jenis bencana
- [ ] Popup marker: `kode_kejadian`, `status_insiden`, `jenis_bencana`, `waktu_mulai`
- [ ] Layer control: filter marker per status insiden
- [ ] AJAX polling 30 detik berjalan: `setInterval(fn, 30000)` dengan `fetch()`
- [ ] Loading spinner tampil saat polling berlangsung
- [ ] Panel stok kritis: list barang dengan jumlah rendah
- [ ] Panel jurnal terbaru: 10 event terakhir

### D — Security
- [ ] Publik tidak dapat akses command center (redirect ke login)
- [ ] PCNU hanya melihat data dalam scope wilayahnya di command center
- [ ] API command center mengembalikan 401 untuk unauthenticated request
- [ ] Tidak ada write operation dari command center — semua route GET/API GET only

### E — Testing
- [ ] `test_pwnu_dapat_akses_command_center` ✓
- [ ] `test_pcnu_melihat_data_scope_sendiri` ✓
- [ ] `test_publik_tidak_dapat_akses_command_center` ✓
- [ ] `test_api_insiden_aktif_hanya_respon_dan_pemulihan` ✓
- [ ] `test_api_stok_kritis_sesuai_scope` ✓

---

## M19 — PUBLIC UI & LAPORAN PUBLIK

### A — Database
Tidak ada migration baru — read-only + INSERT ke `laporan_kejadian` (sudah ada dari M04).

### B — Backend
- [ ] `PublikPetaController`: query insiden aktif tanpa data sensitif
- [ ] `PublikInfoController`: query informasi umum insiden
- [ ] API publik: response tidak mengekspos data logistik, personel, koordinat presisi
- [ ] Throttling: route laporan publik di-rate-limit (misal: 10 request/menit per IP)

### C — Frontend Web
- [ ] Halaman `publik/peta`: peta Leaflet dengan marker insiden aktif, dapat diakses tanpa login
- [ ] Halaman `publik/info`: list insiden aktif, informasi umum (tanpa detail sensitif)
- [ ] Halaman `publik/laporan/create`: form laporan, peta klik untuk koordinat
- [ ] Halaman `publik/laporan/sukses`: konfirmasi pengiriman dengan pesan tindak lanjut
- [ ] Layout publik (`layouts/public.blade.php`): bersih, logo NURISK/NU, footer kontak

### D — Security
- [ ] API publik tidak pernah mengekspos: data logistik, nama relawan, koordinat presisi pos aju
- [ ] Form laporan publik dilindungi dari spam via rate limiting
- [ ] CSRF protection aktif di form laporan publik (Laravel Blade `@csrf`)
- [ ] Data yang tampil di peta publik: hanya insiden `respon` dan `pemulihan`

### E — Testing
- [ ] `test_publik_dapat_akses_peta_tanpa_login` ✓
- [ ] `test_api_publik_tidak_ekspos_data_sensitif` ✓
- [ ] `test_form_laporan_tanpa_login` ✓
- [ ] `test_laporan_tersimpan_status_menunggu` ✓
- [ ] `test_koordinat_invalid_ditolak` ✓

---

---

## CHECKLIST GLOBAL SEBELUM RELEASE

Sebelum aplikasi dinyatakan siap production, checklist berikut WAJIB terpenuhi:

### Infrastruktur
- [ ] Database MySQL production sudah di-setup dengan SQL dump v37 di-import
- [ ] `php artisan migrate` berhasil tanpa error di production server
- [ ] `php artisan db:seed` berhasil mengisi semua master data
- [ ] Storage link terbuat: `php artisan storage:link`
- [ ] Queue worker berjalan (jika ada job async)
- [ ] `.env` production sudah dikonfigurasi: `APP_ENV=production`, `APP_DEBUG=false`

### Security Pre-Release
- [ ] `APP_DEBUG=false` di production
- [ ] Semua route yang tidak diperlukan di-disable
- [ ] HTTPS aktif
- [ ] CSRF protection aktif di semua form
- [ ] Semua file upload divalidasi mime type dan ukuran
- [ ] Rate limiting aktif di route publik
- [ ] `php artisan route:list` tidak menampilkan route berbahaya

### Performa Pre-Release
- [ ] `php artisan config:cache` berhasil
- [ ] `php artisan route:cache` berhasil
- [ ] `php artisan view:cache` berhasil
- [ ] Tidak ada query N+1 yang terdeteksi (gunakan Laravel Debugbar di staging)
- [ ] Semua halaman index menggunakan pagination
- [ ] Master data ter-cache dengan baik

### Testing Pre-Release
- [ ] `php artisan test` — semua test passing (0 failures, 0 errors)
- [ ] Coverage minimum 70% untuk Controller dan Policy
- [ ] Tidak ada test yang di-skip dengan alasan "complex"
- [ ] Test dijalankan dengan MySQL (bukan SQLite)

### Dokumentasi Pre-Release
- [ ] Semua dokumen pra-produksi di `/docs` tersedia dan up-to-date
- [ ] `IMPLEMENTATION_BACKLOG.md` sudah diupdate status setiap modul
- [ ] Catatan sinkronisasi SQL vs PRD sudah terdokumentasi

---

## CHANGELOG DEFINITION OF DONE

| Versi | Tanggal | Perubahan | Disetujui Oleh |
|-------|---------|-----------|----------------|
| 1.0 | 16 Juni 2026 | Dokumen awal — mencakup 19 modul | Technical Lead |

> **Setiap perubahan pada dokumen ini harus melalui persetujuan Technical Lead dan
> dicatat di tabel changelog di atas.**
