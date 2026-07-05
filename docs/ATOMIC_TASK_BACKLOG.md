# ATOMIC_TASK_BACKLOG.md — NURISK
# Backlog Tugas Atomik Terperinci

Dokumen ini mendefinisikan backlog tugas tingkat rendah (atomic tasks) untuk pengembangan NURISK. Setiap tugas dipecah menjadi unit terkecil agar dapat diimplementasikan secara terstruktur, diuji secara independen, dan memenuhi standar Definition of Done (DoD).

---

## 1. Domain: Authorization (AUTH)

### AUTH-001: Model `AuthUser` & `AuthRole` & Relasi
* **Nama Task:** Pembuatan Model `AuthUser` dan `AuthRole` beserta Relasinya.
* **Tujuan:** Mendefinisikan entitas database `auth_users` dan `auth_roles` sebagai model Eloquent dengan konfigurasi PK kustom.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§3.3 & §8.5), `docs/IMPLEMENTATION_BACKLOG.md` (M01).
* **File Output:** 
  * `app/Models/AuthUser.php`
  * `app/Models/AuthRole.php`
* **Kriteria Selesai:**
  * Model `AuthUser` mewarisi `Authenticatable`.
  * `$table` diatur ke `'auth_users'`, `$primaryKey` diatur ke `'id_pengguna'`.
  * Timestamps diset `CREATED_AT = 'dibuat_pada'` dan `UPDATED_AT = 'diperbarui_pada'`.
  * Override `getAuthPassword()` untuk mengembalikan kolom `kata_sandi`.
  * Relasi `peran()` (BelongsTo ke `AuthRole` via `id_peran`) terdefinisi dengan benar.
* **Larangan:** Jangan gunakan kolom `password` atau primary key `id` default.
* **Estimasi:** 2 Jam
* **Dependensi:** Tidak ada.

### AUTH-002: Factory & Model Mapping `AuthUser` & `AuthRole`
* **Nama Task:** Migrasi Database dan Factory untuk `AuthUser` & `AuthRole`.
* **Tujuan:** Membuat model mapping ke database existing dan data factory untuk testing.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§2.1, §3.3).
* **File Output:**
  * `database/migrations/2026_06_16_000001_create_auth_roles_table.php`
  * `database/migrations/2026_06_16_000002_create_auth_users_table.php`
  * `database/factories/AuthUserFactory.php`
* **Kriteria Selesai:**
  * Verifikasi kompatibilitas model Eloquent dengan skema tabel `auth_roles` dengan PK `id_peran`.
  * Verifikasi kompatibilitas model Eloquent dengan skema tabel `auth_users` dengan PK `id_pengguna` dan FK `id_peran`.
  * Kolom `status_akun` berupa ENUM (`menunggu`, `aktif`, `nonaktif`, `suspend`) dengan default `menunggu`.
  * Factory menghasilkan data testing dengan password terenkripsi.
* **Larangan:** Pastikan model menggunakan tipe data PK `INT UNSIGNED` untuk `auth_roles` dan `BIGINT UNSIGNED` untuk `auth_users`.
* **Estimasi:** 2 Jam
* **Dependensi:** AUTH-001

### AUTH-003: Seeder `AuthRole` & Default Users
* **Nama Task:** Penyusunan Database Seeder Peran dan Akun Pengembang.
* **Tujuan:** Mengisi tabel `auth_roles` dengan 5 role PRD dan akun administrator default.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M01, M03).
* **File Output:**
  * `database/seeders/AuthRoleSeeder.php`
  * `database/seeders/AuthUserSeeder.php`
* **Kriteria Selesai:**
  * Role terdaftar: `super_admin`, `pwnu`, `pcnu`, `relawan`, `publik`.
  * Seeder user membuat minimal 1 akun untuk tiap role untuk keperluan testing.
* **Larangan:** Dilarang menambahkan role di luar 5 role yang sudah ditentukan.
* **Estimasi:** 1 Jam
* **Dependensi:** AUTH-002

### AUTH-004: Policy `AuthUserPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Akun Pengguna.
* **Tujuan:** Membatasi hak akses CRUD data pengguna berdasarkan role dan status akun.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M01.6).
* **File Output:**
  * `app/Policies/AuthUserPolicy.php`
* **Kriteria Selesai:**
  * Hanya `super_admin` yang dapat mengubah `id_peran` dan `status_akun`.
  * Pengguna hanya bisa memperbarui profilnya sendiri (selain role/status).
* **Larangan:** Jangan mengizinkan role non-admin mengubah status akun menjadi `aktif`.
* **Estimasi:** 2 Jam
* **Dependensi:** AUTH-003

### AUTH-005: Form Request `LoginRequest` & `RegisterPublikRequest`
* **Nama Task:** Pembuatan Form Request Validasi Login dan Registrasi.
* **Tujuan:** Mengamankan input data dari pengguna sebelum diproses oleh controller.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M01.8).
* **File Output:**
  * `app/Http/Requests/Auth/LoginRequest.php`
  * `app/Http/Requests/Auth/RegisterPublikRequest.php`
* **Kriteria Selesai:**
  * `LoginRequest` memvalidasi `no_hp` (required) dan `kata_sandi` (min:8).
  * `RegisterPublikRequest` memvalidasi keunikan `no_hp` dan validitas format NIK (16 digit jika ada).
* **Larangan:** Jangan memvalidasi email sebagai input login utama (login menggunakan `no_hp`).
* **Estimasi:** 1.5 Jam
* **Dependensi:** AUTH-003

### AUTH-006: Controller `LoginController` & `RegisterPublikController`
* **Nama Task:** Implementasi Controller Autentikasi dan Registrasi Publik.
* **Tujuan:** Mengatur alur login pengguna menggunakan `no_hp` dan pembuatan akun baru.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M01.7).
* **File Output:**
  * `app/Http/Controllers/Auth/LoginController.php`
  * `app/Http/Controllers/Auth/RegisterPublikController.php`
* **Kriteria Selesai:**
  * Metode login memverifikasi status akun (hanya status `aktif` yang bisa login).
  * Registrasi publik otomatis mengatur status akun menjadi `menunggu`.
* **Larangan:** Jangan izinkan login jika `status_akun` bernilai `menunggu`, `nonaktif`, atau `suspend`.
* **Estimasi:** 3 Jam
* **Dependensi:** AUTH-005

### AUTH-007: Route Web untuk Autentikasi
* **Nama Task:** Pendaftaran Routing Autentikasi Web.
* **Tujuan:** Menyediakan endpoint URL untuk login, register, dan logout.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M01.10).
* **File Output:**
  * `routes/web.php` (bagian Autentikasi)
* **Kriteria Selesai:**
  * URL `/login` dan `/daftar` dilindungi middleware `guest`.
  * URL `/logout` dilindungi middleware `auth`.
* **Larangan:** Hindari pembukaan route login untuk pengguna yang sudah terautentikasi.
* **Estimasi:** 0.5 Jam
* **Dependensi:** AUTH-006

### AUTH-008: Blade Views Login & Register
* **Nama Task:** Pembuatan Halaman Tampilan Login dan Registrasi.
* **Tujuan:** Menyediakan antarmuka pengguna (UI) untuk interaksi login dan pendaftaran.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M01.9), `docs/UI_RULES.md`.
* **File Output:**
  * `resources/views/auth/login.blade.php`
  * `resources/views/auth/register.blade.php`
* **Kriteria Selesai:**
  * Form login menggunakan input bertipe text untuk `no_hp` dan password untuk `kata_sandi`.
  * Menampilkan pesan error validasi dengan jelas sesuai UI Rules.
* **Larangan:** Jangan menggunakan framework CSS selain Tailwind CSS yang sudah dikonfigurasi.
* **Estimasi:** 3 Jam
* **Dependensi:** AUTH-007

### AUTH-009: Feature Test & Authorization Test Autentikasi
* **Nama Task:** Penulisan Feature Test untuk Proses Autentikasi.
* **Tujuan:** Memastikan secara otomatis bahwa fitur login dan register berfungsi dan aman.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M01.12), `docs/TESTING_RULES.md`.
* **File Output:**
  * `tests/Feature/Auth/LoginTest.php`
  * `tests/Feature/Auth/RegisterTest.php`
* **Kriteria Selesai:**
  * Test mencakup: login sukses, login gagal sandi salah, login gagal status menunggu/suspend, dan register sukses.
* **Larangan:** Jangan mengabaikan assertion status kode HTTP pada test.
* **Estimasi:** 3 Jam
* **Dependensi:** AUTH-008

---

## 2. Domain: Organisasi (ORG)

### ORG-001: Model `JabatanPosisi` & `PenggunaJabatan` & Relasi
* **Nama Task:** Pembuatan Model `JabatanPosisi` dan `PenggunaJabatan` beserta Relasinya.
* **Tujuan:** Mendefinisikan entitas database `master_jabatan` dan `pengguna_jabatan` di Eloquent.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§2.1, §3.3, §8.5), `docs/IMPLEMENTATION_BACKLOG.md` (M02.5).
* **File Output:**
  * `app/Models/JabatanPosisi.php`
  * `app/Models/PenggunaJabatan.php`
* **Kriteria Selesai:**
  * Model `JabatanPosisi` memetakan tabel `master_jabatan` dengan PK `id_jabatan_posisi`.
  * Model `PenggunaJabatan` memetakan tabel `pengguna_jabatan`.
  * Relasi `jabatan()` pada `AuthUser` terdefinisi.
* **Larangan:** Jangan menggunakan kolom timestamp `dibuat_pada`/`diperbarui_pada` pada model master jika di skema SQL diset `timestamps = false`.
* **Estimasi:** 2 Jam
* **Dependensi:** AUTH-001

### ORG-002: Migration, Factory & Seeder `master_jabatan`
* **Nama Task:** Migrasi dan Seeder untuk Struktur Jabatan.
* **Tujuan:** Membuat tabel jabatan struktural NU dan mengisi data 15 jabatan default.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M02.4, M02.7).
* **File Output:**
  * `database/migrations/2026_06_16_000003_create_master_jabatan_table.php`
  * `database/migrations/2026_06_16_000004_create_pengguna_jabatan_table.php`
  * `database/seeders/JabatanPosisiSeeder.php`
* **Kriteria Selesai:**
  * Tabel `master_jabatan` terbuat dengan PK `id_jabatan_posisi`.
  * Seeder sukses mengimpor 15 jabatan default (misal: Ketua Tanfidziyah, Komandan Posko, dll.).
* **Larangan:** Dilarang mengabaikan relasi FK dari `pengguna_jabatan` ke `auth_users` and `master_jabatan`.
* **Estimasi:** 2 Jam
* **Dependensi:** ORG-001

### ORG-003: Policy `JabatanPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Pengelolaan Jabatan.
* **Tujuan:** Membatasi kewenangan penugasan jabatan struktural.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M02.6).
* **File Output:**
  * `app/Policies/JabatanPolicy.php`
* **Kriteria Selesai:**
  * Hanya `super_admin` yang dapat mengelola master jabatan.
  * `pwnu` dan `pcnu` hanya dapat menetapkan jabatan struktural di bawah scope wilayah mereka.
* **Larangan:** Jangan izinkan role `relawan` atau `publik` mengakses pengelolaan jabatan.
* **Estimasi:** 2 Jam
* **Dependensi:** ORG-002

### ORG-004: Form Request `StoreJabatanRequest`
* **Nama Task:** Validasi Input Pengisian & Penugasan Jabatan.
* **Tujuan:** Memastikan data penugasan jabatan struktural valid sebelum disimpan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M02.8).
* **File Output:**
  * `app/Http/Requests/Admin/StoreJabatanRequest.php`
* **Kriteria Selesai:**
  * Memvalidasi nama jabatan, deskripsi, dan status aktif.
* **Larangan:** Jangan mengizinkan nama jabatan kosong.
* **Estimasi:** 1 Jam
* **Dependensi:** ORG-002

### ORG-005: Controller `JabatanController`
* **Nama Task:** Implementasi Controller Pengelolaan Jabatan.
* **Tujuan:** Mengatur logika CRUD data master jabatan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M02.7).
* **File Output:**
  * `app/Http/Controllers/Admin/JabatanController.php`
* **Kriteria Selesai:**
  * Berhasil melakukan input, edit, dan hapus data jabatan.
* **Larangan:** Pastikan pengecekan otorisasi menggunakan `$this->authorize()` atau `Gate::authorize()`.
* **Estimasi:** 2 Jam
* **Dependensi:** ORG-004

### ORG-006: Route Web/API Jabatan
* **Nama Task:** Definisikan Routing Pengelolaan Jabatan.
* **Tujuan:** Menyediakan endpoint untuk CRUD jabatan di panel admin.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M02.10).
* **File Output:**
  * `routes/web.php` (bagian Jabatan)
* **Kriteria Selesai:**
  * Route resource `/admin/jabatan` terdaftar di bawah middleware `auth` dan `role:super_admin`.
* **Larangan:** Jangan mengekspos route jabatan ke publik.
* **Estimasi:** 0.5 Jam
* **Dependensi:** ORG-005

### ORG-007: Blade Views CRUD Jabatan
* **Nama Task:** Pembuatan Tampilan Web Pengelolaan Jabatan.
* **Tujuan:** Menyediakan form input, edit, dan tabel daftar jabatan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M02.9).
* **File Output:**
  * `resources/views/admin/jabatan/index.blade.php`
  * `resources/views/admin/jabatan/create.blade.php`
  * `resources/views/admin/jabatan/edit.blade.php`
* **Kriteria Selesai:**
  * Antarmuka CRUD jabatan responsif dan menampilkan tombol navigasi dengan benar.
* **Larangan:** Hindari inline styling CSS. Gunakan utilitas Tailwind.
* **Estimasi:** 3 Jam
* **Dependensi:** ORG-006

### ORG-008: Feature Test & Otorisasi Jabatan
* **Nama Task:** Penulisan Integration & Authorization Test Modul Jabatan.
* **Tujuan:** Menjamin keandalan fungsionalitas pengelolaan jabatan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M02.12).
* **File Output:**
  * `tests/Feature/Admin/JabatanTest.php`
* **Kriteria Selesai:**
  * Test mencakup: super admin berhasil CRUD, non-admin diblokir dengan status 403.
* **Larangan:** Dilarang mengabaikan skenario testing negatif (akses tidak sah).
* **Estimasi:** 2.5 Jam
* **Dependensi:** ORG-007

---

## 3. Domain: Wilayah (WIL)

### WIL-001: Model Wilayah & Relasi Scope
* **Nama Task:** Implementasi Model Wilayah & Scope Penanganan.
* **Tujuan:** Menyediakan model representasi data wilayah dan keterkaitannya dengan pengguna.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§7.1), `docs/IMPLEMENTATION_BACKLOG.md` (M02).
* **File Output:**
  * `app/Models/WilayahScope.php`
* **Kriteria Selesai:**
  * Model mendukung pemetaan level scope (`pwnu`, `pcnu`, `mwc`, `ranting`).
* **Larangan:** Jangan mengkoreksi nama enum default_scope_type.
* **Estimasi:** 2 Jam
* **Dependensi:** AUTH-001

### WIL-002: Seeder Wilayah Dasar Jawa Tengah
* **Nama Task:** Penyusunan Seeder Wilayah Kabupaten/Kota di Jawa Tengah.
* **Tujuan:** Memasukkan daftar PCNU/Kabupaten standar ke database.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M03).
* **File Output:**
  * `database/seeders/WilayahSeeder.php`
* **Kriteria Selesai:**
  * Data master wilayah kabupaten Jawa Tengah berhasil terisi.
* **Larangan:** Jangan mengisi data wilayah di luar lingkup operasional PWNU Jawa Tengah.
* **Estimasi:** 2 Jam
* **Dependensi:** WIL-001

### WIL-003: Form Request & API Resource Wilayah
* **Nama Task:** Pembuatan API Resource Wilayah.
* **Tujuan:** Memformat representasi JSON wilayah untuk dropdown AJAX.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M03.11).
* **File Output:**
  * `app/Http/Resources/WilayahResource.php`
* **Kriteria Selesai:**
  * Output API bersih berisi ID, nama wilayah, dan tipe scope.
* **Larangan:** Jangan menyertakan kolom internal database yang tidak diperlukan pada output API.
* **Estimasi:** 1 Jam
* **Dependensi:** WIL-002

### WIL-004: Controller & Route API Wilayah
* **Nama Task:** Pembuatan API Controller dan Routing Wilayah.
* **Tujuan:** Menyediakan endpoint untuk pencarian wilayah secara dinamis.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M03.10).
* **File Output:**
  * `app/Http/Controllers/Api/WilayahApiController.php`
  * `routes/api.php` (bagian Wilayah)
* **Kriteria Selesai:**
  * Request ke `/api/wilayah` mengembalikan daftar wilayah terfilter.
* **Larangan:** Jangan gunakan stateful session pada route API.
* **Estimasi:** 2 Jam
* **Dependensi:** WIL-003

### WIL-005: Feature Test API Wilayah
* **Nama Task:** Feature Test Autocomplete/Dropdown Wilayah.
* **Tujuan:** Mengetes endpoint wilayah dari sisi performa dan output data.
* **Referensi Dokumen:** `docs/TESTING_RULES.md`.
* **File Output:**
  * `tests/Feature/Api/WilayahApiTest.php`
* **Kriteria Selesai:**
  * Mengembalikan JSON struktur valid 200 OK.
* **Larangan:** Jangan menggunakan database production untuk testing.
* **Estimasi:** 2 Jam
* **Dependensi:** WIL-004

---

## 4. Domain: Insiden (INS)

### INS-001: Model `OperasiInsiden` & Relasi & RiwayatStatus
* **Nama Task:** Pembuatan Model `OperasiInsiden` dan `RiwayatStatusInsiden` beserta Relasi.
* **Tujuan:** Membuat representasi data insiden kebencanaan di Laravel.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§3.3, §8.2), `docs/IMPLEMENTATION_BACKLOG.md` (M04.5).
* **File Output:**
  * `app/Models/OperasiInsiden.php`
  * `app/Models/RiwayatStatusInsiden.php`
* **Kriteria Selesai:**
  * Primary key diatur ke `'id_insiden'`.
  * Mengaktifkan trait `SoftDeletes` dengan `DELETED_AT = 'dihapus_pada'`.
  * Relasi `riwayatStatus()`, `jurnals()`, `sitreps()` dikonfigurasi lengkap.
* **Larangan:** Jangan mengubah nama kolom `dihapus_pada` menjadi `deleted_at`.
* **Estimasi:** 3 Jam
* **Dependensi:** AUTH-001

### INS-002: Migration, Factory & Seeder `operasi_insiden`
* **Nama Task:** Migrasi Skema Tabel Insiden dan Riwayat Status.
* **Tujuan:** Menyediakan skema tabel fisik untuk mencatat insiden.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§4.2, §5), `docs/IMPLEMENTATION_BACKLOG.md` (M04.4).
* **File Output:**
  * `database/migrations/2026_06_16_000005_create_operasi_insiden_table.php`
  * `database/migrations/2026_06_16_000006_create_riwayat_status_insiden_table.php`
  * `database/factories/OperasiInsidenFactory.php`
* **Kriteria Selesai:**
  * Tabel `operasi_insiden` memiliki constraint PK `id_insiden`.
  * Kolom `kode_kejadian` diset UNIQUE.
  * Tambahkan trigger `tr_validate_temporal_incident` dan `tr_lock_incident_data` sesuai spek.
* **Larangan:** Dilarang menghapus trigger temporal database secara manual di migration.
* **Estimasi:** 3.5 Jam
* **Dependensi:** INS-001

### INS-003: Policy `InsidenPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Akses Insiden.
* **Tujuan:** Menegakkan aturan akses data insiden sesuai batasan wilayah (scope).
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M04.6).
* **File Output:**
  * `app/Policies/InsidenPolicy.php`
* **Kriteria Selesai:**
  * PCNU hanya bisa memodifikasi insiden di wilayahnya sendiri.
  * Insiden dengan `is_locked = 1` menolak hak akses update.
* **Larangan:** Jangan mengabaikan pemeriksaan status `is_locked`.
* **Estimasi:** 2.5 Jam
* **Dependensi:** INS-002

### INS-004: Form Request `StoreInsidenRequest`
* **Nama Task:** Validasi Form Pembuatan dan Pembaruan Insiden.
* **Tujuan:** Memvalidasi input pembuatan laporan dan data detail insiden baru.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M04.8).
* **File Output:**
  * `app/Http/Requests/Operasi/StoreInsidenRequest.php`
  * `app/Http/Requests/Operasi/UpdateInsidenRequest.php`
* **Kriteria Selesai:**
  * Validasi mandatory: `nama_kejadian`, `id_jenis_bencana`, `waktu_mulai`.
* **Larangan:** Jangan melewatkan validasi tanggal mulai tidak boleh mendahului waktu saat ini/masa depan secara ekstrem.
* **Estimasi:** 1.5 Jam
* **Dependensi:** INS-002

### INS-005: Service `InsidenService` (Transisi Status & Lock)
* **Nama Task:** Pembuatan Service Bisnis Logika Insiden.
* **Tujuan:** Memisahkan logika transisi status insiden dan penyimpanan riwayat dari controller.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M04.7).
* **File Output:**
  * `app/Services/InsidenService.php`
* **Kriteria Selesai:**
  * Transisi status berjalan di dalam `DB::transaction()`.
  * Mengunci data insiden (`is_locked = 1`) ketika status diubah ke `selesai`.
* **Larangan:** Jangan melakukan update data langsung tanpa transaksi database.
* **Estimasi:** 3 Jam
* **Dependensi:** INS-004

### INS-006: Controller `InsidenController` & `InsidenStatusController`
* **Nama Task:** Implementasi Controller Manajemen Insiden.
* **Tujuan:** Menangani HTTP request untuk CRUD insiden dan perubahan statusnya.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M04.7).
* **File Output:**
  * `app/Http/Controllers/Operasi/InsidenController.php`
  * `app/Http/Controllers/Operasi/InsidenStatusController.php`
* **Kriteria Selesai:**
  * Controller memanggil `InsidenService` untuk pengubahan status.
  * Melakukan filter index berdasarkan scope wilayah pengguna login.
* **Larangan:** Dilarang menempatkan logika database query yang rumit langsung di metode controller.
* **Estimasi:** 3 Jam
* **Dependensi:** INS-005

### INS-007: Route Web Insiden
* **Nama Task:** Konfigurasi Routing Web Manajemen Insiden.
* **Tujuan:** Mendaftarkan URL operasional insiden.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M04.10).
* **File Output:**
  * `routes/web.php` (bagian Insiden)
* **Kriteria Selesai:**
  * Route resource `/insiden` dilindungi oleh middleware auth dan role check.
* **Larangan:** Hindari pembukaan route edit/delete untuk insiden yang sudah berstatus selesai.
* **Estimasi:** 0.5 Jam
* **Dependensi:** INS-006

### INS-008: Blade Views Manajemen Insiden
* **Nama Task:** Pembuatan Halaman CRUD Insiden Bencana.
* **Tujuan:** Menyediakan UI visual pemantauan insiden dengan tab-based layout.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M04.9), `docs/UI_RULES.md`.
* **File Output:**
  * `resources/views/operasi/insiden/index.blade.php`
  * `resources/views/operasi/insiden/show.blade.php`
  * `resources/views/operasi/insiden/create.blade.php`
  * `resources/views/operasi/insiden/edit.blade.php`
* **Kriteria Selesai:**
  * Tampilkan badge status berwarna sesuai ketentuan.
  * Menonaktifkan tombol/form edit jika insiden terdeteksi locked.
* **Larangan:** Jangan membiarkan halaman show insiden berat saat memuat relasi (gunakan eager loading).
* **Estimasi:** 4 Jam
* **Dependensi:** INS-007

### INS-009: Feature Test & Otorisasi Insiden
* **Nama Task:** Penulisan Feature Test Menyeluruh Domain Insiden.
* **Tujuan:** Menguji alur bisnis insiden dari pembuatan hingga penutupan dan validasi scope wilayah.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M04.12).
* **File Output:**
  * `tests/Feature/Insiden/InsidenCrudTest.php`
  * `tests/Feature/Insiden/InsidenStatusTest.php`
* **Kriteria Selesai:**
  * Test memastikan trigger database bekerja menolak update data terkunci.
* **Larangan:** Jangan mengabaikan pengujian terhadap trigger `tr_validate_temporal_incident`.
* **Estimasi:** 3.5 Jam
* **Dependensi:** INS-008

---

## 5. Domain: Assessment (ASM)

### ASM-001: Model `AssessmentUtama` & Relasi
* **Nama Task:** Pembuatan Model `AssessmentUtama` beserta Relasi Pendukungnya.
* **Tujuan:** Memodelkan data kaji cepat bencana (assessment) di Laravel.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§8.3), `docs/IMPLEMENTATION_BACKLOG.md` (M05.5).
* **File Output:**
  * `app/Models/AssessmentUtama.php`
  * `app/Models/AssessmentDampakManusia.php`
  * `app/Models/AssessmentKebutuhanMendesak.php`
* **Kriteria Selesai:**
  * PK diset `id_assessment_utama`.
  * Relasi `dampakManusia()` (HasOne ke `AssessmentDampakManusia`) terdefinisi.
  * Relasi `kebutuhanMendesak()` (HasOne ke `AssessmentKebutuhanMendesak`) terdefinisi.
* **Larangan:** Jangan perbaiki typo kolom `waktu_assesment` di database; ikuti nama kolom fisik database v37.
* **Estimasi:** 2.5 Jam
* **Dependensi:** INS-001

### ASM-002: Migration, Factory & Seeder Assessment
* **Nama Task:** Migrasi dan Factory Modul Assessment.
* **Tujuan:** Membuat tabel database pendataan dampak dan kebutuhan mendesak.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§4.2, §6), `docs/IMPLEMENTATION_BACKLOG.md` (M05.4).
* **File Output:**
  * `database/migrations/2026_06_16_000007_create_assessment_utama_table.php`
  * `database/migrations/2026_06_16_000008_create_assessment_dampak_manusia_table.php`
  * `database/migrations/2026_06_16_000009_create_assessment_kebutuhan_mendesak_table.php`
  * `database/factories/AssessmentUtamaFactory.php`
* **Kriteria Selesai:**
  * Verifikasi kompatibilitas model Eloquent dengan skema tabel berhasil dengan constraint foreign key yang tepat.
  * Trigger database `tr_single_latest_assessment` terpasang untuk mengelola kolom `is_latest`.
* **Larangan:** Dilarang menghapus trigger otomatis `tr_single_latest_assessment` karena mengelola state `is_latest` secara internal.
* **Estimasi:** 3.5 Jam
* **Dependensi:** ASM-001

### ASM-003: Policy `AssessmentPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Assessment.
* **Tujuan:** Mencegah entry assessment ilegal pada insiden yang tidak masuk scope wilayah user.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M05.6).
* **File Output:**
  * `app/Policies/AssessmentPolicy.php`
* **Kriteria Selesai:**
  * Membatasi entry data hanya untuk insiden aktif (bukan status `selesai` atau `draf`).
* **Larangan:** Jangan mengizinkan penghapusan data assessment yang terhubung ke sitrep aktif.
* **Estimasi:** 2 Jam
* **Dependensi:** ASM-002

### ASM-004: Form Request `StoreAssessmentRequest`
* **Nama Task:** Validasi Form Input Assessment Lapangan.
* **Tujuan:** Menyeleksi data input koordinat kaji dan jumlah korban/dampak.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M05.8).
* **File Output:**
  * `app/Http/Requests/Operasi/StoreAssessmentRequest.php`
* **Kriteria Selesai:**
  * Validasi koordinat lintang/bujur berada dalam wilayah Indonesia.
* **Larangan:** Jangan meloloskan koordinat bernilai di luar rentang geografis Indonesia.
* **Estimasi:** 1.5 Jam
* **Dependensi:** ASM-002

### ASM-005: Service `AssessmentService` (State Manager)
* **Nama Task:** Pembuatan Logic Service Assessment.
* **Tujuan:** Mengelola penyimpanan data relasional korban dan kebutuhan dalam satu transaksi.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M05.5).
* **File Output:**
  * `app/Services/AssessmentService.php`
* **Kriteria Selesai:**
  * Menyimpan data utama, dampak manusia, dan kebutuhan mendesak secara atomic.
* **Larangan:** Jangan menulis kueri insert terpisah tanpa penanganan `try-catch` rollback transaksi.
* **Estimasi:** 2.5 Jam
* **Dependensi:** ASM-004

### ASM-006: Controller `AssessmentController` (Nested)
* **Nama Task:** Implementasi Controller Nested Assessment.
* **Tujuan:** Menyediakan logika controller di bawah route insiden.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M05.7).
* **File Output:**
  * `app/Http/Controllers/Operasi/AssessmentController.php`
* **Kriteria Selesai:**
  * Endpoint nested `/insiden/{insiden}/assessment` berjalan normal.
* **Larangan:** Jangan membuat route assessment independen (tanpa parameter insiden).
* **Estimasi:** 2 Jam
* **Dependensi:** ASM-005

### ASM-007: Route Web Assessment
* **Nama Task:** Pendaftaran Routing Web Assessment.
* **Tujuan:** Mendaftarkan nested routes untuk CRUD assessment.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M05.7).
* **File Output:**
  * `routes/web.php` (bagian Assessment)
* **Kriteria Selesai:**
  * Route resource nested `insiden.assessment` berhasil didaftarkan.
* **Larangan:** Jangan melewatkan middleware auth dari grup route ini.
* **Estimasi:** 0.5 Jam
* **Dependensi:** ASM-006

### ASM-008: Blade Views Input Assessment
* **Nama Task:** Pembuatan Halaman Input dan Detail Assessment.
* **Tujuan:** Menyediakan UI input formulir kaji cepat awal NU.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M05.9).
* **File Output:**
  * `resources/views/operasi/assessment/create.blade.php`
  * `resources/views/operasi/assessment/show.blade.php`
* **Kriteria Selesai:**
  * Menyediakan form input terbagi atas data umum, dampak korban, dan kebutuhan logistik mendesak.
* **Larangan:** Jangan memisahkan input data dampak dan kebutuhan ke halaman web yang berbeda.
* **Estimasi:** 3.5 Jam
* **Dependensi:** ASM-007

### ASM-009: Feature Test `Assessment`
* **Nama Task:** Pengujian Fitur Otomatis Modul Assessment.
* **Tujuan:** Memastikan pengisian kaji cepat tervalidasi dan `is_latest` terkelola otomatis.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M05.12).
* **File Output:**
  * `tests/Feature/Operasi/AssessmentTest.php`
* **Kriteria Selesai:**
  * Test meloloskan pengujian trigger `tr_single_latest_assessment`.
* **Larangan:** Jangan mengabaikan pengujian terhadap validation error input koordinat salah.
* **Estimasi:** 3 Jam
* **Dependensi:** ASM-008

---

## 6. Domain: Sitrep (SIT)

### SIT-001: Model `OperasiSitrep` & Relasi
* **Nama Task:** Pembuatan Model `OperasiSitrep` beserta Relasinya.
* **Tujuan:** Memodelkan laporan situasi berkala (Sitrep) untuk insiden aktif.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§3.3, §8.2), `docs/IMPLEMENTATION_BACKLOG.md` (M06.5).
* **File Output:**
  * `app/Models/OperasiSitrep.php`
* **Kriteria Selesai:**
  * PK diset `id_sitrep`.
  * Menghubungkan relasi `insiden()` (BelongsTo ke `OperasiInsiden`).
* **Larangan:** Dilarang keras memodifikasi struktur string JSON `snapshot_dampak` langsung dari model tanpa validasi JSON.
* **Estimasi:** 2 Jam
* **Dependensi:** INS-001

### SIT-002: Migration, Factory & Seeder `operasi_sitrep`
* **Nama Task:** Pembuatan Migrasi Tabel Sitrep.
* **Tujuan:** Menyediakan struktur data penyimpanan laporan situasi.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§4.2, §5), `docs/IMPLEMENTATION_BACKLOG.md` (M06.4).
* **File Output:**
  * `database/migrations/2026_06_16_000010_create_operasi_sitrep_table.php`
  * `database/factories/OperasiSitrepFactory.php`
* **Kriteria Selesai:**
  * Kolom `nomor_sitrep` unik per `id_insiden`.
  * Pemasangan trigger `tr_auto_snapshot_sitrep` untuk snapshot dampak otomatis saat insert.
* **Larangan:** Jangan melewatkan constraint integrasi hash audit `hash_snapshot`.
* **Estimasi:** 3 Jam
* **Dependensi:** SIT-001

### SIT-003: Policy `SitrepPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Sitrep.
* **Tujuan:** Membatasi otorisasi finalisasi sitrep hanya untuk staf berwenang.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M06.6).
* **File Output:**
  * `app/Policies/SitrepPolicy.php`
* **Kriteria Selesai:**
  * Hak akses `finalisasi` hanya untuk role `super_admin`, `pwnu`, atau `pcnu` yang masuk scope wilayah.
* **Larangan:** Jangan izinkan modifikasi apa pun jika status sitrep sudah `final`.
* **Estimasi:** 2 Jam
* **Dependensi:** SIT-002

### SIT-004: Form Request `StoreSitrepRequest`
* **Nama Task:** Form Request Validasi Sitrep.
* **Tujuan:** Memvalidasi input data ringkasan situasi lapangan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M06.8).
* **File Output:**
  * `app/Http/Requests/Operasi/StoreSitrepRequest.php`
* **Kriteria Selesai:**
  * Input wajib: `id_insiden`, `keterangan_situasi`, `id_assessment_basis`.
* **Larangan:** Jangan mengizinkan rujukan ke `id_assessment_basis` yang tidak sah atau dari insiden lain.
* **Estimasi:** 1.5 Jam
* **Dependensi:** SIT-002

### SIT-005: Service `SitrepPdfService`
* **Nama Task:** Implementasi Pembuat Dokumen PDF Sitrep.
* **Tujuan:** Membentuk file cetak PDF Sitrep otomatis menggunakan library Dompdf.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M06.5).
* **File Output:**
  * `app/Services/SitrepPdfService.php`
* **Kriteria Selesai:**
  * Output file PDF tersimpan di path `storage/app/public/sitrep/`.
* **Larangan:** Jangan gunakan library eksternal berbayar atau API cloud untuk konversi PDF.
* **Estimasi:** 3 Jam
* **Dependensi:** SIT-004

### SIT-006: Controller `SitrepController`
* **Nama Task:** Implementasi Controller Manajemen Sitrep.
* **Tujuan:** Mengelola input, peninjauan, dan ekspor sitrep.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M06.7).
* **File Output:**
  * `app/Http/Controllers/Operasi/SitrepController.php`
* **Kriteria Selesai:**
  * Menangani pemanggilan generator PDF saat aksi finalisasi sitrep dipicu.
* **Larangan:** Jangan menulis logic generate PDF langsung di method controller.
* **Estimasi:** 2.5 Jam
* **Dependensi:** SIT-005

### SIT-007: Route Web Sitrep
* **Nama Task:** Pendaftaran Routing Web Sitrep.
* **Tujuan:** Menyediakan jalur akses menu sitrep.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M06.10).
* **File Output:**
  * `routes/web.php` (bagian Sitrep)
* **Kriteria Selesai:**
  * Pendaftaran route resource `/sitrep` dan route khusus `/sitrep/{sitrep}/finalisasi`.
* **Larangan:** Jangan biarkan endpoint finalisasi terekspos tanpa perlindungan middleware otorisasi.
* **Estimasi:** 0.5 Jam
* **Dependensi:** SIT-006

### SIT-008: Blade Views Sitrep
* **Nama Task:** Pembuatan Antarmuka Penyusunan dan Detail Sitrep.
* **Tujuan:** Menyediakan UI visual penyuntingan laporan berkala kebencanaan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M06.9).
* **File Output:**
  * `resources/views/operasi/sitrep/index.blade.php`
  * `resources/views/operasi/sitrep/create.blade.php`
  * `resources/views/operasi/sitrep/show.blade.php`
* **Kriteria Selesai:**
  * Terdapat tombol download PDF jika status laporan sudah `final`.
* **Larangan:** Jangan merender data sitrep mentah tanpa formatting string JSON snapshot.
* **Estimasi:** 3.5 Jam
* **Dependensi:** SIT-007

### SIT-009: Feature Test `Sitrep`
* **Nama Task:** Pengujian Fungsional dan Integritas Laporan Sitrep.
* **Tujuan:** Memastikan trigger snapshot bekerja dan format PDF digenerate dengan benar.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M06.12).
* **File Output:**
  * `tests/Feature/Operasi/SitrepTest.php`
* **Kriteria Selesai:**
  * Lulus pengujian integrasi audit integrity hash.
* **Larangan:** Jangan lewatkan pengecekan keabsahan file PDF hasil generator pada disk penyimpanan lokal.
* **Estimasi:** 3 Jam
* **Dependensi:** SIT-008

---

## 7. Domain: Pos Aju (POS)

### POS-001: Model `OperasiPosaju` & Relasi
* **Nama Task:** Pembuatan Model `OperasiPosaju` dan `OperasiPosajuKomandan` beserta Relasinya.
* **Tujuan:** Memodelkan struktur data Posko Lapangan (Pos Aju) dan rekam penugasan komandan pos.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§3.3, §8.1), `docs/IMPLEMENTATION_BACKLOG.md` (M15.5).
* **File Output:**
  * `app/Models/OperasiPosaju.php`
  * `app/Models/OperasiPosajuKomandan.php`
* **Kriteria Selesai:**
  * PK diset `id_posaju`.
  * Relasi `komandan()` (HasMany ke `OperasiPosajuKomandan`) terdefinisi.
* **Larangan:** Jangan merancang relasi komandan secara langsung ke user tanpa melalui tabel penugasan perantara.
* **Estimasi:** 2.5 Jam
* **Dependensi:** INS-001

### POS-002: Migration, Factory & Seeder Pos Aju
* **Nama Task:** Migrasi Database Struktur Pos Aju.
* **Tujuan:** Membuat tabel penyimpan data koordinat posko dan komandan teraktif.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M15.4).
* **File Output:**
  * `database/migrations/2026_06_16_000011_create_operasi_posaju_table.php`
  * `database/migrations/2026_06_16_000012_create_operasi_posaju_komandan_table.php`
  * `database/factories/OperasiPosajuFactory.php`
* **Kriteria Selesai:**
  * Tabel sukses dibuat dengan foreign key mengikat ke `operasi_insiden` dan `auth_users`.
* **Larangan:** Jangan mengizinkan komandan pos aju terdaftar tanpa memiliki penunjukan yang sah via pleno.
* **Estimasi:** 3 Jam
* **Dependensi:** POS-001

### POS-003: Policy `PosajuPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Pos Aju.
* **Tujuan:** Membatasi pembukaan posko aju hanya berdasarkan wilayah penugasan sah.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M15.5).
* **File Output:**
  * `app/Policies/PosajuPolicy.php`
* **Kriteria Selesai:**
  * Mencegah registrasi pos aju baru jika status insiden terkait sudah ditutup/selesai.
* **Larangan:** Jangan izinkan relawan biasa menutup posko aju secara mandiri.
* **Estimasi:** 2 Jam
* **Dependensi:** POS-002

### POS-004: Form Request `StorePosajuRequest`
* **Nama Task:** Form Request Validasi Pos Aju.
* **Tujuan:** Memvalidasi data nama posko, koordinat lokasi, dan nomor pleno rujukan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M15.4).
* **File Output:**
  * `app/Http/Requests/Operasi/StorePosajuRequest.php`
* **Kriteria Selesai:**
  * Melakukan verifikasi keberadaan `id_pleno_penunjukan` yang sah di database.
* **Larangan:** Jangan lewatkan validasi batas geografis latitude/longitude pos aju.
* **Estimasi:** 1.5 Jam
* **Dependensi:** POS-002

### POS-005: Controller `PosajuController` (Web/API)
* **Classification:** **Optional** (Web UI) / **Required** (API Layer)
* **Status:** `[API Complete]` / `[Web Pending]`
* **Nama Task:** Implementasi Controller Pos Aju.
* **Tujuan:** Mengelola data pembuatan posko, pergantian komandan, dan penutupan posko.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M15.5).
* **File Output:**
  - Web Controller: `app/Http/Controllers/Operasi/PosajuController.php` (Web Pending)
  - API Controller: `app/Http/Controllers/Api/Operasi/OperasiPosajuController.php` (API Complete)
* **Kriteria Selesai:**
  * Logika pergantian komandan mencatat histori baru pada tabel `operasi_posaju_komandan`.
* **Larangan:** Jangan menghapus fisik record komandan lama (pertahankan histori).
* **Estimasi:** 2.5 Jam
* **Dependensi:** POS-004

### POS-006: Route Web/API Pos Aju
* **Classification:** **Optional** (Web UI) / **Required** (API Layer)
* **Status:** `[API Complete]` / `[Web Pending]`
* **Nama Task:** Konfigurasi Routing Web/API Pos Aju.
* **Tujuan:** Menyediakan endpoint administrasi pos aju lapangan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M15.5).
* **File Output:**
  - Web Routes: `routes/web.php` (Web Pending)
  - API Routes: `routes/api.php` (API Complete)
* **Kriteria Selesai:**
  * Route resource `/posaju` dideklarasikan lengkap di bawah grup autentikasi.
* **Larangan:** Jangan biarkan endpoint pergantian komandan diakses tanpa izin otorisasi.
* **Estimasi:** 0.5 Jam
* **Dependensi:** POS-005

### POS-007: Blade Views Pos Aju
* **Classification:** **Optional** (Web UI)
* **Status:** `[Web Pending]` / `[Not Required For Mobile]`
* **Nama Task:** Pembuatan Halaman Manajemen Pos Aju.
* **Tujuan:** Menyediakan UI pendaftaran bagi relawan dan seleksi admin.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M15.5).
* **File Output:**
  * `resources/views/operasi/posaju/index.blade.php`
  * `resources/views/operasi/posaju/create.blade.php`
  * `resources/views/operasi/posaju/show.blade.php`
* **Kriteria Selesai:**
  * Menampilkan riwayat komandan posko dalam bentuk tabel kronologis.
* **Larangan:** Dilarang mengabaikan rendering penanda koordinat posko pada peta internal.
* **Estimasi:** 3.5 Jam
* **Dependensi:** POS-006

### POS-008: Feature Test Pos Aju
* **Nama Task:** Pengujian Otomatis Fitur Pos Aju.
* **Tujuan:** Mengetes kewajiban referensi pleno saat penunjukan komandan pos.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M15.5).
* **File Output:**
  * `tests/Feature/Operasi/PosajuTest.php`
* **Kriteria Selesai:**
  * Pengujian meloloskan validasi relasi pleno penunjukan dan testing status ditutup.
* **Larangan:** Dilarang melewati pengujian validasi kecocokan wilayah kerja posko.
* **Estimasi:** 2.5 Jam
* **Dependensi:** POS-007

---

## 8. Domain: Logistik (LOG)

### LOG-001: Model Logistik & Relasi
* **Nama Task:** Pembuatan Model Domain Logistik & Relasi.
* **Tujuan:** Mendefinisikan struktur model pengelolaan stok, mutasi gudang, katalog barang, dan permintaan.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§3.3, §8.4), `docs/IMPLEMENTATION_BACKLOG.md` (M12.5).
* **File Output:**
  * `app/Models/LogistikGudang.php`
  * `app/Models/LogistikBarangKatalog.php`
  * `app/Models/LogistikStok.php`
  * `app/Models/LogistikMutasi.php`
  * `app/Models/LogistikPermintaan.php`
* **Kriteria Selesai:**
  * PK diset sesuai skema (contoh: `id_stok` pada `LogistikStok`).
  * Relasi `mutasis()` pada `LogistikStok` dan `stok()` pada `LogistikMutasi` terhubung.
* **Larangan:** Jangan mengizinkan manipulasi kolom stok langsung dari model Eloquent tanpa mutasi.
* **Estimasi:** 4 Jam
* **Dependensi:** INS-001

### LOG-002: Migration, Factory & Seeder Logistik
* **Nama Task:** Migrasi Skema Tabel Logistik & Seed Kategori.
* **Tujuan:** Menyiapkan penyimpanan fisik data inventaris kebencanaan NU.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M12.4).
* **File Output:**
  * `database/migrations/2026_06_16_000013_create_logistik_gudang_table.php`
  * `database/migrations/2026_06_16_000014_create_logistik_barang_katalog_table.php`
  * `database/migrations/2026_06_16_000015_create_logistik_stok_table.php`
  * `database/migrations/2026_06_16_000016_create_logistik_mutasi_table.php`
  * `database/migrations/2026_06_16_000017_create_logistik_permintaan_table.php`
  * `database/seeders/LogistikKategoriSeeder.php`
* **Kriteria Selesai:**
  * Terpasangnya trigger otomatis `tr_execute_logistik_stok_update` dan `tr_logistik_mutasi_integrity_guard` di database.
* **Larangan:** Dilarang keras melakukan bypass trigger database v37 yang mengontrol integritas stok logistik.
* **Estimasi:** 4.5 Jam
* **Dependensi:** LOG-001

### LOG-003: Policy `LogistikPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Logistik.
* **Tujuan:** Membatasi kewenangan mutasi keluar barang hanya untuk pengurus logistik resmi.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M12.5).
* **File Output:**
  * `app/Policies/LogistikPolicy.php`
* **Kriteria Selesai:**
  * Membatasi akses mutasi keluar hanya jika pengguna memiliki peran koordinator logistik di pos/gudang terkait.
* **Larangan:** Jangan biarkan pengguna lintas regional mengajukan mutasi keluar dari gudang PCNU daerah lain.
* **Estimasi:** 2.5 Jam
* **Dependensi:** LOG-002

### LOG-004: Form Request `StoreMutasiRequest` & `StorePermintaanRequest`
* **Nama Task:** Form Request Validasi Transaksi Logistik.
* **Tujuan:** Menjamin keabsahan input jumlah barang, tipe mutasi, dan target gudang.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M12.5).
* **File Output:**
  * `app/Http/Requests/Logistik/StoreMutasiRequest.php`
  * `app/Http/Requests/Logistik/StorePermintaanRequest.php`
* **Kriteria Selesai:**
  * `StoreMutasiRequest` memvalidasi `tipe_mutasi` ENUM (`masuk`, `keluar`, `penyesuaian`).
  * `StorePermintaanRequest` memvalidasi input array barang.
* **Larangan:** Jangan biarkan input jumlah barang bernilai minus atau nol.
* **Estimasi:** 2 Jam
* **Dependensi:** LOG-002

### LOG-005: Service `LogistikMutasiService` (Wrapper Transaksi Aman)
* **Nama Task:** Pembuatan Service Eksekusi Mutasi Stok.
* **Tujuan:** Menyediakan wrapper logika penyimpanan mutasi logistik untuk mencegah race conditions.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M12.5).
* **File Output:**
  * `app/Services/LogistikMutasiService.php`
* **Kriteria Selesai:**
  * Generate UUID transaksi otomatis (`uuid_mutasi`) untuk integrity audit.
  * Penulisan log transaksi berjalan di dalam `DB::transaction()`.
* **Larangan:** Dilarang membiarkan penulisan data mutasi tanpa try-catch recovery.
* **Estimasi:** 3 Jam
* **Dependensi:** LOG-004

### LOG-006: Controller Logistik
* **Nama Task:** Implementasi Controller Manajemen Stok & Mutasi.
* **Tujuan:** Menghubungkan antarmuka CRUD barang dengan mutasi logistik.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M12.5).
* **File Output:**
  * `app/Http/Controllers/Logistik/LogistikStokController.php`
  * `app/Http/Controllers/Logistik/LogistikMutasiController.php`
  * `app/Http/Controllers/Logistik/LogistikPermintaanController.php`
* **Kriteria Selesai:**
  * Controller sukses mendelegasikan proses penyesuaian stok ke `LogistikMutasiService`.
* **Larangan:** Jangan mengizinkan manipulasi stok tanpa pencatatan `id_mutasi`.
* **Estimasi:** 3.5 Jam
* **Dependensi:** LOG-005

### LOG-007: Route Web Logistik
* **Nama Task:** Konfigurasi Routing Web Manajemen Inventori.
* **Tujuan:** Menyediakan menu navigasi logistik.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M12.5).
* **File Output:**
  * `routes/web.php` (bagian Logistik)
* **Kriteria Selesai:**
  * Menghubungkan resource `/logistik/stok`, `/logistik/mutasi`, dan `/logistik/permintaan`.
* **Larangan:** Jangan mengekspos rute mutasi internal ke level pengguna publik.
* **Estimasi:** 0.5 Jam
* **Dependensi:** LOG-006

### LOG-008: Blade Views Logistik
* **Nama Task:** Pembuatan Antarmuka Pengelolaan Stok dan Permintaan.
* **Tujuan:** Menyediakan visualisasi mutasi barang masuk/keluar.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M12.5).
* **File Output:**
  * `resources/views/logistik/stok.blade.php`
  * `resources/views/logistik/mutasi.blade.php`
  * `resources/views/logistik/permintaan.blade.php`
* **Kriteria Selesai:**
  * Dilengkapi tombol filter pencarian katalog barang dan ringkasan kuantitas stok terupdate.
* **Larangan:** Hindari merender daftar ribuan stok tanpa pagination yang memadai.
* **Estimasi:** 4 Jam
* **Dependensi:** LOG-007

### LOG-009: Feature Test `Logistik`
* **Nama Task:** Pengujian Otomatis Mutasi Logistik dan Pembatasan Stok.
* **Tujuan:** Menguji keandalan trigger database penolak stok minus.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M12.5).
* **File Output:**
  * `tests/Feature/Logistik/LogistikMutationTest.php`
* **Kriteria Selesai:**
  * Lulus pengetesan mutasi keluar melebihi stok yang menghasilkan signal error DB.
* **Larangan:** Jangan mengabaikan pengujian terhadap keunikan UUID transaksi mutasi.
* **Estimasi:** 3 Jam
* **Dependensi:** LOG-008

---

## 9. Domain: Aset (AST)

### AST-001: Model `AsetUnit` & `AsetPenggunaan` & Relasi
* **Nama Task:** Pembuatan Model Aset dan Peminjaman Aset.
* **Tujuan:** Memodelkan entitas inventaris sarana kebencanaan.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§3.3, §8.1), `docs/IMPLEMENTATION_BACKLOG.md` (M14.5).
* **File Output:**
  * `app/Models/AsetUnit.php`
  * `app/Models/AsetPenggunaan.php`
* **Kriteria Selesai:**
  * PK diset `id_aset` dan `id_penggunaan`.
  * Mendefinisikan relasi `aset()` pada `AsetPenggunaan` ke `AsetUnit`.
* **Larangan:** Jangan mengizinkan manipulasi status aset secara langsung melompati log penggunaan.
* **Estimasi:** 2.5 Jam
* **Dependensi:** INS-001

### AST-002: Migration, Factory & Seeder Aset
* **Nama Task:** Migrasi dan Seed Master Status Aset.
* **Tujuan:** Membuat tabel aset serta mengisi referensi status standar.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M14.4).
* **File Output:**
  * `database/migrations/2026_06_16_000018_create_aset_unit_table.php`
  * `database/migrations/2026_06_16_000019_create_aset_penggunaan_table.php`
  * `database/seeders/AsetMasterStatusSeeder.php`
* **Kriteria Selesai:**
  * Terpasangnya trigger `tr_prevent_double_booking_aset` dan `tr_aset_return_to_available` pada skema database.
* **Larangan:** Jangan mengabaikan constraint status integer 1 (tersedia) sampai 5 (hilang).
* **Estimasi:** 3 Jam
* **Dependensi:** AST-002

### AST-003: Policy `AsetPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Aset.
* **Tujuan:** Menjaga aset dari peminjaman oleh unit regional lain tanpa persetujuan pemilik.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M14.5).
* **File Output:**
  * `app/Policies/AsetPolicy.php`
* **Kriteria Selesai:**
  * Otorisasi peminjaman divalidasi berdasarkan kecocokan unit regional.
* **Larangan:** Jangan izinkan peminjaman jika status aset bukan "Tersedia".
* **Estimasi:** 2 Jam
* **Dependensi:** AST-002

### AST-004: Form Request `StoreAsetUnitRequest` & `StoreAsetPenggunaanRequest`
* **Nama Task:** Form Request Validasi Aset.
* **Tujuan:** Memvalidasi input data unit aset baru dan data peminjaman aset.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M14.4).
* **File Output:**
  * `app/Http/Requests/Aset/StoreAsetUnitRequest.php`
  * `app/Http/Requests/Aset/StoreAsetPenggunaanRequest.php`
* **Kriteria Selesai:**
  * Mengharuskan pengisian `kode_aset`, `nama_aset`, dan `id_status`.
* **Larangan:** Dilarang meloloskan input peminjaman tanpa menyertakan `waktu_pinjam`.
* **Estimasi:** 1.5 Jam
* **Dependensi:** AST-002

### AST-005: Controller Aset
* **Nama Task:** Implementasi Controller Aset dan Penggunaan.
* **Tujuan:** Menangani transaksi peminjaman dan pengembalian fisik aset.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M14.5).
* **File Output:**
  * `app/Http/Controllers/Aset/AsetUnitController.php`
  * `app/Http/Controllers/Aset/AsetPenggunaanController.php`
* **Kriteria Selesai:**
  * Proses pengembalian mengisi kolom `waktu_kembali` dan memicu trigger ketersediaan otomatis.
* **Larangan:** Jangan menggunakan update manual SQL bypass trigger untuk mengubah status ketersediaan.
* **Estimasi:** 2.5 Jam
* **Dependensi:** AST-004

### AST-006: Route Web Aset
* **Nama Task:** Konfigurasi Routing Web Aset.
* **Tujuan:** Menyediakan jalur akses menu manajemen aset.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M14.5).
* **File Output:**
  * `routes/web.php` (bagian Aset)
* **Kriteria Selesai:**
  * Rute resource `/aset` dan sub-rute peminjaman terdaftar di bawah middleware otorisasi.
* **Larangan:** Jangan mengekspos route peminjaman aset ke level publik.
* **Estimasi:** 0.5 Jam
* **Dependensi:** AST-005

### AST-007: Blade Views Aset
* **Nama Task:** Pembuatan Halaman Tampilan & Layout Cetak Aset.
* **Tujuan:** Menyediakan UI kontrol inventaris aset NU.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M14.5).
* **File Output:**
  * `resources/views/aset/index.blade.php`
  * `resources/views/aset/show.blade.php`
  * `resources/views/aset/pinjam.blade.php`
* **Kriteria Selesai:**
  * Menampilkan badge visual kondisi fisik (`baik`, `rusak_ringan`, `rusak_berat`).
* **Larangan:** Dilarang menyembunyikan tombol return jika masa peminjaman terdeteksi overdue.
* **Estimasi:** 3.5 Jam
* **Dependensi:** AST-006

### AST-008: Feature Test `Aset`
* **Nama Task:** Pengujian Fitur Otomatis Keamanan Booking Aset.
* **Tujuan:** Menguji trigger double booking pencegah peminjaman ganda.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M14.5).
* **File Output:**
  * `tests/Feature/Aset/AsetBookingTest.php`
* **Kriteria Selesai:**
  * Lulus pengetesan peminjaman aset yang sedang bertugas menghasilkan error DB.
* **Larangan:** Jangan mengabaikan pengujian skenario pengembalian aset yang mengubah status otomatis.
* **Estimasi:** 2.5 Jam
* **Dependensi:** AST-007

---

## 10. Domain: Relawan (REL)

### REL-001: Model `RelawanPendaftaran` & `RelawanPenugasan` & Relasi
* **Nama Task:** Pembuatan Model Relawan dan Relasinya.
* **Tujuan:** Memodelkan pendaftaran keikutsertaan relawan kebencanaan.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§3.3, §8.1), `docs/IMPLEMENTATION_BACKLOG.md` (M13.5).
* **File Output:**
  * `app/Models/RelawanPendaftaran.php`
  * `app/Models/RelawanPenugasan.php`
* **Kriteria Selesai:**
  * Relasi `pendaftar()` (BelongsTo ke `AuthUser`) dikonfigurasi.
* **Larangan:** Jangan memetakan penugasan langsung tanpa melalui status pendaftaran yang disetujui.
* **Estimasi:** 2 Jam
* **Dependensi:** AUTH-001

### REL-002: Migration, Factory & Seeder Relawan
* **Nama Task:** Migrasi Database Relawan.
* **Tujuan:** Menyediakan tabel penyimpanan pendaftaran dan penugasan relawan aktif.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M13.4).
* **File Output:**
  * `database/migrations/2026_06_16_000020_create_relawan_pendaftaran_table.php`
  * `database/migrations/2026_06_16_000021_create_relawan_penugasan_table.php`
  * `database/factories/RelawanPendaftaranFactory.php`
* **Kriteria Selesai:**
  * Tabel pendaftaran memiliki UNIQUE constraint `(id_pengguna, id_relawan_kebutuhan)`.
* **Larangan:** Jangan mengizinkan pengisian data penugasan relawan yang belum terverifikasi profilnya.
* **Estimasi:** 3 Jam
* **Dependensi:** REL-001

### REL-003: Policy `RelawanPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Relawan.
* **Tujuan:** Membatasi verifikasi pendaftaran relawan hanya untuk staf PWNU/PCNU.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M13.5).
* **File Output:**
  * `app/Policies/RelawanPolicy.php`
* **Kriteria Selesai:**
  * Melarang relawan biasa menyetujui pendaftaran dirinya sendiri.
* **Larangan:** Dilarang meloloskan otorisasi tanpa mengecek scope PCNU pendaftar.
* **Estimasi:** 2 Jam
* **Dependensi:** REL-002

### REL-004: Form Request `StoreRelawanPendaftaranRequest`
* **Nama Task:** Form Request Validasi Pendaftaran Relawan.
* **Tujuan:** Memvalidasi input kesediaan waktu dan komitmen relawan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M13.5).
* **File Output:**
  * `app/Http/Requests/Relawan/StoreRelawanPendaftaranRequest.php`
* **Kriteria Selesai:**
  * Memastikan validitas rujukan kebutuhan operasional insiden.
* **Larangan:** Jangan biarkan pendaftaran kosong tanpa menyertakan deskripsi motivasi/keahlian pendukung.
* **Estimasi:** 1.5 Jam
* **Dependensi:** REL-002

### REL-005: Controller Relawan (Web/API)
* **Classification:** **Optional** (Web UI) / **Required** (API Layer)
* **Status:** `[API Complete]` / `[Web Pending]`
* **Nama Task:** Implementasi Controller Pendaftaran & Penugasan Relawan.
* **Tujuan:** Mengelola status kelulusan berkas pendaftaran relawan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M13.5).
* **File Output:**
  - Web Controller: `app/Http/Controllers/Relawan/RelawanController.php` (Web Pending)
  - API Controllers: (API Complete)
    * `app/Http/Controllers/Api/Relawan/RelawanPendaftaranController.php`
    * `app/Http/Controllers/Api/Relawan/RelawanPenugasanController.php`
    * `app/Http/Controllers/Api/Relawan/RelawanProfilController.php`
* **Kriteria Selesai:**
  * Proses approval otomatis mengisi relasi penugasan relawan aktif.
* **Larangan:** Jangan merubah status penugasan relawan secara langsung tanpa persetujuan tertulis.
* **Estimasi:** 2.5 Jam
* **Dependensi:** REL-004

### REL-006: Route Web/API Relawan
* **Classification:** **Optional** (Web UI) / **Required** (API Layer)
* **Status:** `[API Complete]` / `[Web Pending]`
* **Nama Task:** Konfigurasi Routing Web/API Relawan.
* **Tujuan:** Menyediakan endpoint pendaftaran relawan bagi masyarakat umum NU.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M13.5).
* **File Output:**
  - Web Routes: `routes/web.php` (Web Pending)
  - API Routes: `routes/api.php` (API Complete)
* **Kriteria Selesai:**
  * Rute publik `/relawan/daftar` dan rute admin internal `/admin/relawan` terpisah.
* **Larangan:** Jangan mencampur route admin verifikasi dengan route pendaftaran publik.
* **Estimasi:** 0.5 Jam
* **Dependensi:** REL-005

### REL-007: Blade Views Relawan
* **Classification:** **Optional** (Web UI)
* **Status:** `[Web Pending]` / `[Not Required For Mobile]`
* **Nama Task:** Pembuatan Halaman Manajemen Relawan.
* **Tujuan:** Menyediakan UI pendaftaran bagi relawan dan seleksi admin.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M13.5).
* **File Output:**
  * `resources/views/relawan/index.blade.php`
  * `resources/views/relawan/show.blade.php`
  * `resources/views/relawan/pendaftaran.blade.php`
* **Kriteria Selesai:**
  * Form seleksi menampilkan daftar keahlian relawan (sync `auth_pengguna_keahlian`).
* **Larangan:** Jangan menampilkan data NIK relawan secara vulgar di daftar umum (masking data NIK).
* **Estimasi:** 3.5 Jam
* **Dependensi:** REL-006

### REL-008: Feature Test `Relawan`
* **Nama Task:** Pengujian Otomatis Registrasi Relawan.
* **Tujuan:** Mengetes alur validasi pendaftaran relawan baru.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M13.5).
* **File Output:**
  * `tests/Feature/Relawan/RelawanFlowTest.php`
* **Kriteria Selesai:**
  * Pengujian meloloskan pengecekan kekhususan UNIQUE constraint pendaftaran ganda.
* **Larangan:** Jangan melewatkan assertions status code respon HTTP pada kegagalan duplikasi pendaftaran.
* **Estimasi:** 2.5 Jam
* **Dependensi:** REL-007

---

## 11. Domain: Klaster (KLS)

### KLS-001: Model `OperasiKlaster` & Relasi
* **Nama Task:** Pembuatan Model Klaster Operasional & Relasi.
* **Tujuan:** Memodelkan 6 klaster kebencanaan NU (Klaster Kesehatan, Logistik, dll).
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§3.3, §8.1), `docs/IMPLEMENTATION_BACKLOG.md` (M10.4).
* **File Output:**
  * `app/Models/OperasiKlaster.php`
  * `app/Models/OperasiKlasterKoordinator.php`
* **Kriteria Selesai:**
  * PK diset `id_klaster`.
  * Relasi `koordinator()` (HasMany ke `OperasiKlasterKoordinator`) terdefinisi.
* **Larangan:** Jangan mengizinkan penetapan koordinasi klaster di luar 6 klaster resmi NU.
* **Estimasi:** 2 Jam
* **Dependensi:** INS-001

### KLS-002: Migration, Factory & Seeder Klaster
* **Nama Task:** Migrasi dan Seed Referensi Klaster Kebencanaan.
* **Tujuan:** Membuat tabel klaster dan mengimpor 6 klaster default.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M10.4).
* **File Output:**
  * `database/migrations/2026_06_16_000022_create_operasi_klaster_table.php`
  * `database/migrations/2026_06_16_000023_create_operasi_klaster_koordinator_table.php`
  * `database/seeders/OperasiKlasterSeeder.php`
* **Kriteria Selesai:**
  * Tabel terbuat dan database terisi 6 klaster operasional default NU.
* **Larangan:** Dilarang keras merubah nama klaster standar tanpa koordinasi tim kebencanaan PWNU.
* **Estimasi:** 2.5 Jam
* **Dependensi:** KLS-001

### KLS-003: Policy `KlasterPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Klaster.
* **Tujuan:** Membatasi pengelolaan klaster berdasarkan pleno terkait.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M10.4).
* **File Output:**
  * `app/Policies/KlasterPolicy.php`
* **Kriteria Selesai:**
  * Mengharuskan penunjukan koordinator didasari oleh pleno keputusan sah.
* **Larangan:** Jangan izinkan komandan posko menunjuk koordinator klaster secara lisan (wajib input pleno).
* **Estimasi:** 2 Jam
* **Dependensi:** KLS-002

### KLS-004: Form Request `UpdateKlasterRequest`
* **Nama Task:** Form Request Validasi Klaster.
* **Tujuan:** Memvalidasi isian progres target klaster di lapangan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M10.4).
* **File Output:**
  * `app/Http/Requests/Operasi/UpdateKlasterRequest.php`
* **Kriteria Selesai:**
  * Memvalidasi persentase progres (`progres_persen` antara 0 hingga 100).
* **Larangan:** Jangan biarkan input progres di luar rentang angka persen yang valid.
* **Estimasi:** 1 Jam
* **Dependensi:** KLS-002

### KLS-005: Controller Klaster (Web/API)
* **Classification:** **Optional** (Web UI) / **Required** (API Layer)
* **Status:** `[API Complete]` / `[Web Pending]`
* **Nama Task:** Implementasi Controller Klaster Operasional.
* **Tujuan:** Menangani pembaharuan progres kinerja klaster oleh koordinator.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M10.4).
* **File Output:**
  - Web Controller: `app/Http/Controllers/Operasi/KlasterController.php` (Web Pending)
  - API Controllers: (API Complete)
    * `app/Http/Controllers/Api/Operasi/OperasiKlasterController.php`
    * `app/Http/Controllers/Api/Operasi/OperasiTugasController.php`
* **Kriteria Selesai:**
  * Logika penyimpanan mengupdate kolom `status_klaster` (`aktif`, `selesai`, `nonaktif`).
* **Larangan:** Jangan izinkan pengeditan progres klaster jika status klaster sudah `selesai`.
* **Estimasi:** 2.5 Jam
* **Dependensi:** KLS-004

### KLS-006: Route Web/API Klaster
* **Classification:** **Optional** (Web UI) / **Required** (API Layer)
* **Status:** `[API Complete]` / `[Web Pending]`
* **Nama Task:** Konfigurasi Routing Web/API Klaster.
* **Tujuan:** Menyediakan jalur akses dashboard klaster kebencanaan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M10.4).
* **File Output:**
  - Web Routes: `routes/web.php` (Web Pending)
  - API Routes: `routes/api.php` (API Complete)
* **Kriteria Selesai:**
  * Route resource `/klaster` dideklarasikan lengkap di bawah grup otorisasi.
* **Larangan:** Jangan biarkan route update klaster diakses tanpa verifikasi status koordinator aktif.
* **Estimasi:** 0.5 Jam
* **Dependensi:** KLS-005

### KLS-007: Blade Views Klaster
* **Classification:** **Optional** (Web UI)
* **Status:** `[Web Pending]` / `[Not Required For Mobile]`
* **Nama Task:** Pembuatan Halaman Dashboard Klaster Bencana.
* **Tujuan:** Menyediakan UI visual pemantauan grafik progres 6 klaster.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M10.4).
* **File Output:**
  * `resources/views/klaster/index.blade.php`
  * `resources/views/klaster/show.blade.php`
* **Kriteria Selesai:**
  * Tampilan memuat progres bar interaktif dan log koordinator teraktif.
* **Larangan:** Hindari inline javascript yang rumit; gunakan script terpisah.
* **Estimasi:** 3.5 Jam
* **Dependensi:** KLS-006

### KLS-008: Feature Test `Klaster`
* **Nama Task:** Pengujian Otomatis Integrasi Klaster.
* **Tujuan:** Menjamin kevalidan transisi status klaster dari nonaktif ke selesai.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M10.4).
* **File Output:**
  * `tests/Feature/Operasi/KlasterTest.php`
* **Kriteria Selesai:**
  * Lulus pengujian validasi otorisasi koordinator klaster yang ditunjuk.
* **Larangan:** Dilarang mengabaikan pengetesan batas nilai progres 100%.
* **Estimasi:** 2.5 Jam
* **Dependensi:** KLS-007

---

## 12. Domain: Feedback Klaster (FBK)

### FBK-001: Model `OperasiKlasterFeedback` & Relasi
* **Nama Task:** Pembuatan Model Feedback Klaster & Relasi.
* **Tujuan:** Memodelkan evaluasi umpan balik kinerja klaster dari masyarakat atau koordinator pos.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§2.1, §3.3), `docs/IMPLEMENTATION_BACKLOG.md` (M17).
* **File Output:**
  * `app/Models/OperasiKlasterFeedback.php`
* **Kriteria Selesai:**
  * Model terhubung ke `operasi_klaster_feedback` dengan relasi ke `OperasiKlaster` dan `AuthUser`.
* **Larangan:** Jangan mengabaikan foreign key relasi ke pengguna pemberi umpan balik.
* **Estimasi:** 2 Jam
* **Dependensi:** KLS-001

### FBK-002: Factory & Model Mapping Feedback Klaster
* **Nama Task:** Pembuatan Skema Tabel Feedback Klaster.
* **Tujuan:** Menyiapkan penyimpanan evaluasi masukan per klaster operasional.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17).
* **File Output:**
  * `database/migrations/2026_06_16_000024_create_operasi_klaster_feedback_table.php`
  * `database/factories/OperasiKlasterFeedbackFactory.php`
* **Kriteria Selesai:**
  * Tabel `operasi_klaster_feedback` terbuat lengkap dengan kolom audit.
* **Larangan:** Jangan implementasi sebelum mendapat konfirmasi skema tabel formal dari Lead DBA.
* **Estimasi:** 2.5 Jam
* **Dependensi:** FBK-001

### FBK-003: Policy `FeedbackKlasterPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Feedback Klaster.
* **Tujuan:** Membatasi akses pengisian feedback hanya untuk personel terverifikasi di insiden terkait.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17).
* **File Output:**
  * `app/Policies/FeedbackKlasterPolicy.php`
* **Kriteria Selesai:**
  * Membatasi pengisian feedback untuk insiden yang sudah berstatus `selesai`.
* **Larangan:** Jangan izinkan user luar scope wilayah mengisi feedback sembarangan.
* **Estimasi:** 2 Jam
* **Dependensi:** FBK-002

### FBK-004: Form Request `StoreFeedbackKlasterRequest`
* **Nama Task:** Form Request Validasi Feedback.
* **Tujuan:** Memvalidasi input rating evaluasi dan komentar.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17).
* **File Output:**
  * `app/Http/Requests/Operasi/StoreFeedbackKlasterRequest.php`
* **Kriteria Selesai:**
  * Memvalidasi rating bernilai integer antara 1 sampai 5.
* **Larangan:** Jangan biarkan input komentar kosong tanpa isi deskripsi masukan.
* **Estimasi:** 1 Jam
* **Dependensi:** FBK-002

### FBK-005: Controller Feedback Klaster
* **Nama Task:** Implementasi Controller Feedback.
* **Tujuan:** Menyimpan data evaluasi masukan kinerja klaster.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17).
* **File Output:**
  * `app/Http/Controllers/Operasi/FeedbackKlasterController.php`
* **Kriteria Selesai:**
  * Data masukan tersimpan aman ke tabel `operasi_klaster_feedback`.
* **Larangan:** Jangan gunakan hard delete pada data feedback jika terjadi salah input (gunakan flag status).
* **Estimasi:** 2 Jam
* **Dependensi:** FBK-004

### FBK-006: Route Web/API Feedback Klaster
* **Nama Task:** Konfigurasi Routing Web/API Feedback.
* **Tujuan:** Menyediakan jalur akses pengiriman evaluasi klaster.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17).
* **File Output:**
  * `routes/web.php` (bagian Feedback)
* **Kriteria Selesai:**
  * Menghubungkan route submit `/klaster/feedback` di bawah otorisasi.
* **Larangan:** Jangan membuka endpoint feedback secara bebas tanpa rate limiter.
* **Estimasi:** 0.5 Jam
* **Dependensi:** FBK-005

### FBK-007: Blade Views Feedback Klaster
* **Nama Task:** Pembuatan Halaman Evaluasi Kinerja Klaster.
* **Tujuan:** Menyediakan form input masukan penilaian kinerja klaster.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17).
* **File Output:**
  * `resources/views/klaster/feedback.blade.php`
* **Kriteria Selesai:**
  * Tampilan interaktif bintang rating 1-5 dengan form komentar terstruktur.
* **Larangan:** Hindari kerumitan form pengisian; buat sesederhana mungkin.
* **Estimasi:** 3 Jam
* **Dependensi:** FBK-006

### FBK-008: Feature Test Feedback Klaster
* **Nama Task:** Pengujian Otomatis Fitur Feedback Klaster.
* **Tujuan:** Mengetes integritas data rating dan kewenangan otorisasi input.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17).
* **File Output:**
  * `tests/Feature/Operasi/FeedbackKlasterTest.php`
* **Kriteria Selesai:**
  * Tes sukses menguji input rating tidak valid dan otorisasi regional.
* **Larangan:** Dilarang mengabaikan pengujian skenario pengisian ganda (double submit).
* **Estimasi:** 2.5 Jam
* **Dependensi:** FBK-007

---

## 13. Domain: Gap Kebutuhan (GAP)

### GAP-001: Model `OperasiGapKebutuhan` & Relasi
* **Nama Task:** Pembuatan Model Gap Kebutuhan & Relasi.
* **Tujuan:** Memodelkan kesenjangan antara kebutuhan darurat korban dengan stok logistik tersedia.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§2.1, §3.3), `docs/IMPLEMENTATION_BACKLOG.md` (M18).
* **File Output:**
  * `app/Models/OperasiGapKebutuhan.php`
* **Kriteria Selesai:**
  * Relasi ke model `OperasiInsiden` dan `LogistikBarangKatalog` terhubung dengan benar.
* **Larangan:** Jangan mengizinkan pembentukan nilai gap minus jika stok di gudang berlebih.
* **Estimasi:** 2 Jam
* **Dependensi:** LOG-001

### GAP-002: Factory & Model Mapping Gap Kebutuhan
* **Nama Task:** Pembuatan Skema Tabel Gap Kebutuhan Bencana.
* **Tujuan:** Menyediakan tabel penyimpanan kalkulasi selisih stok logistik lapangan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M18).
* **File Output:**
  * `database/migrations/2026_06_16_000025_create_operasi_gap_kebutuhan_table.php`
  * `database/factories/OperasiGapKebutuhanFactory.php`
* **Kriteria Selesai:**
  * Tabel `operasi_gap_kebutuhan` terbuat lengkap dengan primary key unik.
* **Larangan:** Jangan implementasi sebelum mendapat konfirmasi skema tabel formal dari Lead DBA.
* **Estimasi:** 2.5 Jam
* **Dependensi:** GAP-001

### GAP-003: Policy `GapKebutuhanPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Gap Kebutuhan.
* **Tujuan:** Membatasi kalkulasi gap hanya untuk administrator regional logistik.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M18).
* **File Output:**
  * `app/Policies/GapKebutuhanPolicy.php`
* **Kriteria Selesai:**
  * Otorisasi mengecek kewenangan akses database logistik regional terkait.
* **Larangan:** Jangan izinkan relawan non-logistik mengubah status penyelesaian gap.
* **Estimasi:** 2 Jam
* **Dependensi:** GAP-002

### GAP-004: Form Request `StoreGapKebutuhanRequest`
* **Nama Task:** Form Request Validasi Gap Kebutuhan.
* **Tujuan:** Memvalidasi input kalkulasi manual gap.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M18).
* **File Output:**
  * `app/Http/Requests/Operasi/StoreGapKebutuhanRequest.php`
* **Kriteria Selesai:**
  * Memvalidasi jumlah kebutuhan riil dan jumlah ketersediaan logistik.
* **Larangan:** Jangan biarkan input angka kosong atau string pada kuantitas logistik.
* **Estimasi:** 1 Jam
* **Dependensi:** GAP-002

### GAP-005: Controller Gap Kebutuhan
* **Nama Task:** Implementasi Controller Gap Kebutuhan Bencana.
* **Tujuan:** Menyediakan logika perhitungan selisih barang logistik secara otomatis.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M18).
* **File Output:**
  * `app/Http/Controllers/Operasi/GapKebutuhanController.php`
* **Kriteria Selesai:**
  * Logic controller sukses menghitung selisih kebutuhan vs stok tersedia.
* **Larangan:** Jangan biarkan proses perhitungan gap dijalankan tanpa caching di server.
* **Estimasi:** 2.5 Jam
* **Dependensi:** GAP-004

### GAP-006: Route Web/API Gap Kebutuhan
* **Nama Task:** Konfigurasi Routing Web/API Gap Kebutuhan.
* **Tujuan:** Menyediakan endpoint visualisasi status pemenuhan kebutuhan logistik.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M18).
* **File Output:**
  * `routes/web.php` (bagian Gap Kebutuhan)
* **Kriteria Selesai:**
  * Route resource `/gap-kebutuhan` terdaftar di bawah otorisasi logistik.
* **Larangan:** Jangan membuka data internal kesenjangan logistik darurat ke konsumsi publik bebas.
* **Estimasi:** 0.5 Jam
* **Dependensi:** GAP-005

### GAP-007: Blade Views Gap Kebutuhan
* **Nama Task:** Pembuatan Halaman Pemantauan Gap Kebutuhan.
* **Tujuan:** Menyediakan UI representasi stok kritis yang belum terpenuhi bagi pengambil kebijakan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M18).
* **File Output:**
  * `resources/views/logistik/gap.blade.php`
* **Kriteria Selesai:**
  * UI memuat indikator warna visual untuk tingkat kerawanan kelangkaan barang (kritis, sedang, aman).
* **Larangan:** Dilarang merender visualisasi yang lambat saat memuat banyak data (lakukan filter pagination).
* **Estimasi:** 3.5 Jam
* **Dependensi:** GAP-006

### GAP-008: Feature Test Gap Kebutuhan
* **Nama Task:** Pengujian Otomatis Fitur Kalkulasi Gap.
* **Tujuan:** Mengetes keakuratan rumus pengurangan kebutuhan lapangan vs stok gudang.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M18).
* **File Output:**
  * `tests/Feature/Logistik/GapKebutuhanTest.php`
* **Kriteria Selesai:**
  * Lulus pengujian akurasi selisih hitung matematis.
* **Larangan:** Jangan mengabaikan skenario pengetesan data kosong (stok nol).
* **Estimasi:** 2.5 Jam
* **Dependensi:** GAP-007

---

## 14. Domain: Pleno (PLN)

### PLN-001: Model Pleno & Relasi
* **Nama Task:** Pembuatan Model Domain Pleno & Relasi.
* **Tujuan:** Memodelkan sistem musyawarah pengambilan keputusan insiden, peserta pleno, eskalasi tingkat bencana, dan aktivasi respon.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§3.3, §8.1), `docs/IMPLEMENTATION_BACKLOG.md` (M07.5).
* **File Output:**
  * `app/Models/OperasiPleno.php`
  * `app/Models/OperasiPlenoKeputusan.php`
  * `app/Models/OperasiPlenoPeserta.php`
  * `app/Models/OperasiEskalasi.php`
  * `app/Models/OperasiAktivasi.php`
* **Kriteria Selesai:**
  * PK diatur sesuai skema (contoh: `id_pleno`).
  * Relasi `peserta()` and `keputusan()` dikonfigurasi lengkap.
* **Larangan:** Jangan mengizinkan modifikasi data pleno jika status keputusan diset `final`.
* **Estimasi:** 4.5 Jam
* **Dependensi:** INS-001

### PLN-002: Migration, Factory & Seeder Pleno
* **Nama Task:** Migrasi Database Otoritas Pleno.
* **Tujuan:** Membuat tabel database pendukung musyawarah penanganan bencana.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M07.4).
* **File Output:**
  * `database/migrations/2026_06_16_000026_create_operasi_pleno_table.php`
  * `database/migrations/2026_06_16_000027_create_operasi_pleno_keputusan_table.php`
  * `database/migrations/2026_06_16_000028_create_operasi_pleno_peserta_table.php`
  * `database/migrations/2026_06_16_000029_create_operasi_eskalasi_table.php`
  * `database/migrations/2026_06_16_000030_create_operasi_aktivasi_table.php`
  * `database/factories/OperasiPlenoFactory.php`
* **Kriteria Selesai:**
  * Kolom `level_baru` pada tabel eskalasi dibatasi ENUM (`lokal`, `pcnu`, `pwnu`, `nasional`).
* **Larangan:** Dilarang keras mengabaikan constraint foreign key dari eskalasi ke tabel pleno penunjuk.
* **Estimasi:** 4.5 Jam
* **Dependensi:** PLN-001

### PLN-003: Policy `PlenoPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Pleno.
* **Tujuan:** Mengamankan keputusan pleno dari manipulasi oleh peserta luar rapat.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M07.5).
* **File Output:**
  * `app/Policies/PlenoPolicy.php`
* **Kriteria Selesai:**
  * Hanya `pwnu` atau `pcnu` pimpinan rapat yang dapat merubah status keputusan menjadi `final`.
* **Larangan:** Jangan izinkan relawan memiliki hak suara atau mengubah putusan pleno.
* **Estimasi:** 2.5 Jam
* **Dependensi:** PLN-002

### PLN-004: Form Request `StorePlenoRequest` & `StoreKeputusanRequest`
* **Nama Task:** Form Request Validasi Pleno & Keputusan.
* **Tujuan:** Menyeleksi kelayakan data masukan agenda pleno.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M07.4).
* **File Output:**
  * `app/Http/Requests/Operasi/StorePlenoRequest.php`
  * `app/Http/Requests/Operasi/StoreKeputusanRequest.php`
* **Kriteria Selesai:**
  * Memastikan deskripsi keputusan dan daftar peserta terisi.
* **Larangan:** Jangan membiarkan data pleno disimpan tanpa menyertakan minimal 1 agenda pembahasan.
* **Estimasi:** 2 Jam
* **Dependensi:** PLN-002

### PLN-005: Controller Pleno & Eskalasi
* **Nama Task:** Implementasi Controller Sidang Pleno.
* **Tujuan:** Menjalankan pencatatan jalannya rapat pleno kebencanaan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M07.5).
* **File Output:**
  * `app/Http/Controllers/Operasi/PlenoController.php`
  * `app/Http/Controllers/Operasi/EskalasiController.php`
* **Kriteria Selesai:**
  * Fitur voting peserta merubah kolom persetujuan (`setuju`, `tolak`, `abstain`) secara terintegrasi.
* **Larangan:** Jangan membiarkan eskalasi ditransfer ke level lebih rendah (level baru wajib lebih tinggi dari sebelumnya).
* **Estimasi:** 3.5 Jam
* **Dependensi:** PLN-004

### PLN-006: Route Web Pleno
* **Nama Task:** Konfigurasi Routing Web Pleno.
* **Tujuan:** Menyediakan link administrasi pleno internal.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M07.5).
* **File Output:**
  * `routes/web.php` (bagian Pleno)
* **Kriteria Selesai:**
  * Route resource `/pleno` dideklarasikan lengkap di bawah grup otorisasi.
* **Larangan:** Dilarang keras mengekspos detail voting pleno internal ke portal umum.
* **Estimasi:** 0.5 Jam
* **Dependensi:** PLN-005

### PLN-007: Blade Views Pleno & Eskalasi
* **Nama Task:** Pembuatan Antarmuka Dokumentasi Sidang Pleno.
* **Tujuan:** Menyediakan UI input notulensi dan daftar hadir pleno.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M07.5).
* **File Output:**
  * `resources/views/pleno/index.blade.php`
  * `resources/views/pleno/show.blade.php`
  * `resources/views/pleno/create.blade.php`
  * `resources/views/pleno/peserta.blade.php`
* **Kriteria Selesai:**
  * Visualisasi persentase setuju/tolak voting peserta terrender interaktif.
* **Larangan:** Dilarang menghilangkan form catatan peserta jika status persetujuan dipilih `tolak`.
* **Estimasi:** 4 Jam
* **Dependensi:** PLN-006

### PLN-008: Feature Test Pleno
* **Nama Task:** Pengujian Otomatis Sistem Pleno.
* **Tujuan:** Menguji aturan immutabilitas pleno berstatus final.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M07.5).
* **File Output:**
  * `tests/Feature/Operasi/PlenoTest.php`
* **Kriteria Selesai:**
  * Test meloloskan pengujian voting peserta dan penolakan edit pasca finalisasi.
* **Larangan:** Jangan mengabaikan pengujian terhadap aturan level eskalasi naik.
* **Estimasi:** 3 Jam
* **Dependensi:** PLN-007

---

## 15. Domain: Surat Menyurat (SRT)

### SRT-001: Model `OperasiSuratKeluar` & Relasi
* **Nama Task:** Pembuatan Model Surat Keluar & Relasi.
* **Tujuan:** Memodelkan surat resmi kebencanaan NU beserta alur persetujuan paraf berurutan.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§3.3, §8.1), `docs/IMPLEMENTATION_BACKLOG.md` (M08.5).
* **File Output:**
  * `app/Models/OperasiSuratKeluar.php`
  * `app/Models/DokumenSuratParaf.php`
  * `app/Models/DokumenSuratTembusan.php`
* **Kriteria Selesai:**
  * PK diset `id_surat` pada model utama.
  * Relasi `parafs()` (HasMany ke `DokumenSuratParaf` terurut berdasarkan kolom `urutan`) terdefinisi.
* **Larangan:** Jangan merancang persetujuan paraf tanpa urutan bertingkat yang baku.
* **Estimasi:** 3 Jam
* **Dependensi:** PLN-001

### SRT-002: Migration, Factory & Seeder Surat Menyurat
* **Nama Task:** Migrasi Database Struktur Dokumen Surat.
* **Tujuan:** Menyediakan tabel penyimpanan naskah dinas kebencanaan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M08.4).
* **File Output:**
  * `database/migrations/2026_06_16_000031_create_operasi_surat_keluar_table.php`
  * `database/migrations/2026_06_16_000032_create_dokumen_surat_paraf_table.php`
  * `database/migrations/2026_06_16_000033_create_dokumen_surat_tembusan_table.php`
  * `database/factories/OperasiSuratKeluarFactory.php`
* **Kriteria Selesai:**
  * Tabel sukses dibuat dengan foreign key terhubung ke `master_surat_jenis` dan `master_jabatan_penandatangan`.
* **Larangan:** Jangan mengizinkan tipe data kolom status paraf menyimpang dari ENUM (`menunggu`, `disetujui`, `ditolak`).
* **Estimasi:** 3.5 Jam
* **Dependensi:** SRT-001

### SRT-003: Policy `SuratPolicy`
* **Nama Task:** Pembuatan Policy Otorisasi Surat Menyurat.
* **Tujuan:** Menjamin keabsahan penandatangan surat berdasarkan kewenangan jabatannya.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M08.5).
* **File Output:**
  * `app/Policies/SuratPolicy.php`
* **Kriteria Selesai:**
  * Hanya pemegang jabatan penandatangan terdaftar yang boleh menandatangani surat.
* **Larangan:** Jangan izinkan modifikasi isi naskah surat jika status surat sudah `finalized`.
* **Estimasi:** 2.5 Jam
* **Dependensi:** SRT-002

### SRT-004: Form Request `StoreSuratRequest` & `ParafSuratRequest`
* **Nama Task:** Form Request Validasi Pengajuan Surat.
* **Tujuan:** Memvalidasi kelayakan naskah dinas kebencanaan.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M08.5).
* **File Output:**
  * `app/Http/Requests/Surat/StoreSuratRequest.php`
  * `app/Http/Requests/Surat/ParafSuratRequest.php`
* **Kriteria Selesai:**
  * Memastikan isian perihal, tujuan surat, dan pilihan template surat valid.
* **Larangan:** Jangan meloloskan pengajuan surat jika rujukan jenis surat tidak terdaftar.
* **Estimasi:** 2 Jam
* **Dependensi:** SRT-002

### SRT-005: Service `NomorSuratService` & `SuratPdfService`
* **Nama Task:** Pembuatan Service Penomoran Otomatis & Ekspor PDF Surat.
* **Tujuan:** Menghasilkan nomor surat unik sesuai format klasifikasi dan merender cetakan PDF resmi.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M08.5).
* **File Output:**
  * `app/Services/NomorSuratService.php`
  * `app/Services/SuratPdfService.php`
* **Kriteria Selesai:**
  * Nomor surat digenerate urut berdasarkan format klasifikasi jenis surat.
  * PDF tersimpan otomatis di path `storage/app/public/surat/` setelah status menjadi finalized.
* **Larangan:** Jangan mengizinkan duplikasi nomor surat resmi dalam kategori yang sama.
* **Estimasi:** 3.5 Jam
* **Dependensi:** SRT-004

### SRT-006: Controller Surat
* **Nama Task:** Implementasi Controller Surat Menyurat.
* **Tujuan:** Mengelola alur paraf bertingkat dan penerbitan nomor surat resmi.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M08.5).
* **File Output:**
  * `app/Http/Controllers/Surat/SuratController.php`
  * `app/Http/Controllers/Surat/ParafSuratController.php`
* **Kriteria Selesai:**
  * Aksi penolakan paraf (`ditolak`) mengembalikan status surat ke draft dan mereset paraf setelahnya ke status menunggu.
* **Larangan:** Jangan merubah status penandatanganan di luar alur sekuensial paraf yang telah diatur.
* **Estimasi:** 3 Jam
* **Dependensi:** SRT-005

### SRT-007: Route Web Surat Menyurat
* **Nama Task:** Konfigurasi Routing Web Surat Menyurat.
* **Tujuan:** Menyediakan link administrasi naskah dinas organisasi.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M08.5).
* **File Output:**
  * `routes/web.php` (bagian Surat)
* **Kriteria Selesai:**
  * Menghubungkan endpoint resource `/surat` dan route khusus `/surat/{surat}/paraf`.
* **Larangan:** Hindari pembukaan route akses surat internal tanpa pengamanan login.
* **Estimasi:** 0.5 Jam
* **Dependensi:** SRT-006

### SRT-008: Blade Views Surat & Cetakan PDF
* **Nama Task:** Pembuatan Halaman Tampilan & Layout Cetak Surat.
* **Tujuan:** Menyediakan UI penulisan draft surat dan tampilan layout cetak PDF resmi NU.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M08.5).
* **File Output:**
  * `resources/views/surat/index.blade.php`
  * `resources/views/surat/create.blade.php`
  * `resources/views/surat/paraf.blade.php`
  * `resources/views/pdf/surat.blade.php`
* **Kriteria Selesai:**
  * PDF memuat kop surat standar PWNU/PCNU Jateng dengan letak nomor surat yang presisi.
* **Larangan:** Dilarang keras merubah layout kop surat tanpa acuan PRD resmi.
* **Estimasi:** 4 Jam
* **Dependensi:** SRT-007

### SRT-009: Feature Test Surat Menyurat
* **Nama Task:** Pengujian Fitur Otomatis Alur Surat Menyurat.
* **Tujuan:** Menguji kepatuhan urutan paraf bertingkat dan penolakan reset status.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M08.5).
* **File Output:**
  * `tests/Feature/Surat/SuratWorkflowTest.php`
* **Kriteria Selesai:**
  * Tes sukses menguji reset paraf saat terjadi status ditolak dan keunikan nomor surat terbit.
* **Larangan:** Dilarang mengabaikan verifikasi keberadaan file PDF pada disk storage.
* **Estimasi:** 3 Jam
* **Dependensi:** SRT-008

---

## 16. Domain: Audit Trail (AUD)

### AUD-001: Model `OperasiJurnal` & Relasi
* **Nama Task:** Pembuatan Model Jurnal Aktivitas & Relasi.
* **Tujuan:** Memodelkan pencatatan riwayat kronologis (audit trail) segala peristiwa operasional di lapangan.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§7.21, §8.2), `docs/IMPLEMENTATION_BACKLOG.md` (M17.5).
* **File Output:**
  * `app/Models/OperasiJurnal.php`
* **Kriteria Selesai:**
  * Model terhubung ke tabel `operasi_jurnal`.
  * Menghubungkan relasi `insiden()` (BelongsTo ke `OperasiInsiden`).
* **Larangan:** Jangan mengizinkan proses update atau delete record jurnal dari aplikasi (jurnal bersifat append-only).
* **Estimasi:** 2 Jam
* **Dependensi:** INS-001

### AUD-002: Factory & Model Mapping Audit Trail
* **Nama Task:** Pembuatan Skema Tabel Audit Trail Jurnal.
* **Tujuan:** Menyediakan tabel penyimpanan riwayat event operasional kebencanaan.
* **Referensi Dokumen:** `docs/DATABASE_CONVENTION.md` (§2.1, §5.1).
* **File Output:**
  * `database/migrations/2026_06_16_000034_create_operasi_jurnal_table.php`
  * `database/factories/OperasiJurnalFactory.php`
* **Kriteria Selesai:**
  * Kolom `kategori_event` dibatasi ENUM (`sistem`, `laporan`, `aktivasi`, `respon`, `penugasan`, `logistik`, `aset`, `personil`, `posko`, `selesai`).
* **Larangan:** Jangan menyimpang dari struktur tipe data ENUM kategori event yang ditentukan.
* **Estimasi:** 2.5 Jam
* **Dependensi:** AUD-001

### AUD-003: Service `AuditService`
* **Nama Task:** Implementasi Service Logger Audit Trail.
* **Tujuan:** Menyediakan helper global untuk mempermudah pencatatan log peristiwa operasional di semua modul.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17.5).
* **File Output:**
  * `app/Services/AuditService.php`
* **Kriteria Selesai:**
  * Metode `logEvent(int $idInsiden, string $kategori, string $deskripsi, ?int $idAktor)` menyimpan log secara aman ke database.
* **Larangan:** Jangan sampai error logging menghentikan jalannya transaksi bisnis utama (lakukan proteksi try-catch).
* **Estimasi:** 2 Jam
* **Dependensi:** AUD-002

### AUD-004: Controller Jurnal Operasi
* **Nama Task:** Implementasi Controller Audit Trail Jurnal.
* **Tujuan:** Menyediakan data kronologis event per insiden untuk kepentingan monitoring internal.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17.5).
* **File Output:**
  * `app/Http/Controllers/Operasi/JurnalController.php`
* **Kriteria Selesai:**
  * Controller mengembalikan data log terurut kronologis (`ORDER BY dibuat_pada DESC`).
* **Larangan:** Jangan menampilkan log sistem internal yang bersifat rahasia kepada user non-admin.
* **Estimasi:** 2 Jam
* **Dependensi:** AUD-003

### AUD-005: Route Web Jurnal
* **Nama Task:** Konfigurasi Routing Web Jurnal Operasi.
* **Tujuan:** Menyediakan rute akses menu jurnal audit per insiden.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17.5).
* **File Output:**
  * `routes/web.php` (bagian Jurnal)
* **Kriteria Selesai:**
  * Endpoint `/insiden/{insiden}/jurnal` didaftarkan di bawah otorisasi.
* **Larangan:** Jangan biarkan rute log audit diakses secara bebas tanpa login.
* **Estimasi:** 0.5 Jam
* **Dependensi:** AUD-004

### AUD-006: Blade Views Jurnal Operasi
* **Nama Task:** Pembuatan Halaman Kronologis Aktivitas (Timeline).
* **Tujuan:** Menyediakan UI berbentuk timeline visual peristiwa penanganan bencana.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17.5).
* **File Output:**
  * `resources/views/operasi/jurnal/timeline.blade.php`
* **Kriteria Selesai:**
  * Menampilkan ikon penanda kategori event yang berbeda.
* **Larangan:** Dilarang merender halaman timeline tanpa pagination (maksimal 20 item per halaman).
* **Estimasi:** 3 Jam
* **Dependensi:** AUD-005

### AUD-007: Feature Test `AuditTrail`
* **Nama Task:** Pengujian Otomatis Sistem Logging Jurnal.
* **Tujuan:** Mengetes otomatisasi logger mencatat event saat terjadi perubahan status insiden.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M17.5).
* **File Output:**
  * `tests/Feature/Operasi/AuditTrailTest.php`
* **Kriteria Selesai:**
  * Test memverifikasi pemanggilan `AuditService` berjalan sukses saat transisi status insiden dipicu.
* **Larangan:** Jangan mengabaikan assert keberadaan baris database baru di tabel `operasi_jurnal`.
* **Estimasi:** 2.5 Jam
* **Dependensi:** AUD-006

---

## 17. Domain: File Storage (FIL)

### FIL-001: Service `FileStorageService`
* **Nama Task:** Implementasi Service Pengunggah Berkas.
* **Tujuan:** Menyediakan handler upload file terpusat yang aman sesuai aturan.
* **Referensi Dokumen:** `docs/FILE_STORAGE_RULES.md`.
* **File Output:**
  * `app/Services/FileStorageService.php`
* **Kriteria Selesai:**
  * Memvalidasi ukuran maksimum berkas (maksimal 5MB untuk dokumen, 10MB untuk foto kejadian).
  * Menyimpan file dengan nama acak/hash unik untuk mencegah overwriting.
* **Larangan:** Jangan pernah menyimpan file langsung di folder public tanpa sanitasi ekstensi (hanya perbolehkan pdf, jpg, jpeg, png, webp).
* **Estimasi:** 3 Jam
* **Dependensi:** AUTH-001

### FIL-002: Form Request File Validation Rules Helper
* **Nama Task:** Pembuatan Trait Validasi Upload Berkas.
* **Tujuan:** Mempermudah form request melakukan validasi berkas lampiran.
* **Referensi Dokumen:** `docs/FILE_STORAGE_RULES.md`.
* **File Output:**
  * `app/Http/Requests/Traits/HasFileValidation.php`
* **Kriteria Selesai:**
  * Trait menyediakan fungsi rule validasi file standar untuk form request.
* **Larangan:** Jangan meloloskan file biner executable (.exe, .sh, .php, dll).
* **Estimasi:** 1.5 Jam
* **Dependensi:** FIL-001

### FIL-003: Controller & Route File Streaming (Private Storage Bypass)
* **Nama Task:** Pembuatan Controller Stream File Privat.
* **Tujuan:** Menyediakan akses stream berkas aman (PDF Sitrep, Surat Keluar) yang tersimpan di private disk storage.
* **Referensi Dokumen:** `docs/FILE_STORAGE_RULES.md`.
* **File Output:**
  * `app/Http/Controllers/FileStreamController.php`
  * `routes/web.php` (bagian Streaming File)
* **Kriteria Selesai:**
  * Endpoint `/storage/secure/{file}` memverifikasi kepemilikan dan hak akses user sebelum streaming byte file.
* **Larangan:** Jangan mengekspos link file naskah dinas privat secara publik langsung (selalu gunakan controller screening).
* **Estimasi:** 2.5 Jam
* **Dependensi:** FIL-002

### FIL-004: Feature Test `FileStorage`
* **Nama Task:** Pengujian Otomatis Upload Berkas dan Keamanan.
* **Tujuan:** Menguji blokir upload file berbahaya (ekstensi .php/malware) dan bypass otorisasi stream.
* **Referensi Dokumen:** `docs/FILE_STORAGE_RULES.md`.
* **File Output:**
  * `tests/Feature/System/FileStorageTest.php`
* **Kriteria Selesai:**
  * Tes sukses menolak upload ekstensi ilegal dan mengembalikan status 403 pada stream tanpa otorisasi.
* **Larangan:** Dilarang mengabaikan pengujian assert status storage disk pura-pura (gunakan `Storage::fake()`).
* **Estimasi:** 2.5 Jam
* **Dependensi:** FIL-003

---

## 18. Domain: API (API)

### API-001: API Resources for Dashboard & Mobile
* **Nama Task:** Pembuatan API Resources Formatter JSON.
* **Tujuan:** Menyediakan spesifikasi format keluaran JSON standar untuk integrasi eksternal atau Ajax command center.
* **Referensi Dokumen:** `docs/API_CONTRACT.md`.
* **File Output:**
  * `app/Http/Resources/InsidenApiResource.php`
  * `app/Http/Resources/SitrepApiResource.php`
  * `app/Http/Resources/StokLogistikApiResource.php`
* **Kriteria Selesai:**
  * JSON output memenuhi format kontrak API yang disetujui.
* **Larangan:** Jangan menyertakan kata sandi atau salt token pengguna di dalam API Resource.
* **Estimasi:** 3 Jam
* **Dependensi:** INS-001, SIT-001, LOG-001

### API-002: API Authentication (Sanctum Tokens)
* **Nama Task:** Konfigurasi Pengaman API Token (Sanctum).
* **Tujuan:** Mengamankan akses endpoint API internal kebencanaan.
* **Referensi Dokumen:** `docs/API_CONTRACT.md`.
* **File Output:**
  * `config/sanctum.php`
  * `app/Http/Controllers/Api/AuthApiController.php`
* **Kriteria Selesai:**
  * Menghasilkan personal access token yang valid saat request login API sukses.
* **Larangan:** Jangan biarkan API dikonsumsi publik tanpa pembatasan rate-limiting (throttling).
* **Estimasi:** 2.5 Jam
* **Dependensi:** API-001

### API-003: API Controllers & Routing (under `/api/*`)
* **Nama Task:** Implementasi Endpoint API Kebencanaan.
* **Tujuan:** Menyediakan link endpoint operasional untuk integrasi aplikasi mobile atau command center.
* **Referensi Dokumen:** `docs/API_CONTRACT.md`.
* **File Output:**
  * `app/Http/Controllers/Api/InsidenApiController.php`
  * `routes/api.php`
* **Kriteria Selesai:**
  * Endpoint `/api/insiden` dan `/api/insiden/{id}/riwayat-status` mengembalikan JSON terformat 200 OK.
* **Larangan:** Jangan mencampur route API di dalam file routing `web.php`.
* **Estimasi:** 3.5 Jam
* **Dependensi:** API-002

### API-004: API Feature Tests
* **Nama Task:** Pengujian Otomatis Keandalan Endpoint API.
* **Tujuan:** Memastikan respon API sesuai dengan HTTP header dan spesifikasi kontrak JSON.
* **Referensi Dokumen:** `docs/TESTING_RULES.md`, `docs/API_CONTRACT.md`.
* **File Output:**
  * `tests/Feature/Api/InsidenApiTest.php`
* **Kriteria Selesai:**
  * Lulus pengujian header response `Content-Type: application/json` dan response code 200/401/403.
* **Larangan:** Jangan melakukan skip tests pada assertion struktur JSON output.
* **Estimasi:** 3 Jam
* **Dependensi:** API-003

---

## 19. Domain: Dashboard Internal (DAS)

### DAS-001: Dashboard internal query logic
* **Nama Task:** Pembuatan Query Agregasi Dashboard Sesuai Scope Wilayah.
* **Tujuan:** Menyediakan logika kueri data ringkasan kasus kebencanaan aktif untuk admin internal.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M19).
* **File Output:**
  * `app/Queries/DashboardQuery.php`
* **Kriteria Selesai:**
  * Kueri memfilter jumlah insiden aktif, jumlah personil bertugas, dan status logistik kritis terikat scope wilayah pengguna login.
* **Larangan:** Jangan membiarkan PCNU melihat total agregasi data internal PCNU daerah lain.
* **Estimasi:** 3 Jam
* **Dependensi:** INS-001, LOG-001

### DAS-002: DashboardController
* **Nama Task:** Implementasi Dashboard Controller.
* **Tujuan:** Menghubungkan visualisasi dashboard internal dengan data hasil kueri agregasi.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M19.6).
* **File Output:**
  * `app/Http/Controllers/DashboardController.php`
* **Kriteria Selesai:**
  * Controller sukses merender view dashboard internal dengan membawa data statistik terfilter.
* **Larangan:** Hindari proses kueri langsung (tanpa cache) pada halaman dashboard jika traffic di server padat.
* **Estimasi:** 2.5 Jam
* **Dependensi:** DAS-001

### DAS-003: Dashboard Route Web
* **Nama Task:** Pendaftaran Routing Web Dashboard.
* **Tujuan:** Menyediakan jalur akses menu dashboard internal.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M19).
* **File Output:**
  * `routes/web.php` (bagian Dashboard)
* **Kriteria Selesai:**
  * Route `/dashboard` terdaftar di bawah middleware `auth` dan akun aktif check.
* **Larangan:** Jangan izinkan user publik (non-login) mengakses URL dashboard internal.
* **Estimasi:** 0.5 Jam
* **Dependensi:** DAS-002

### DAS-004: Blade Views Dashboard Internal
* **Nama Task:** Pembuatan Halaman Dashboard Statistik Pengguna.
* **Tujuan:** Menyediakan antarmuka grafik panel monitoring kasus kebencanaan NU.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M19.8), `docs/UI_RULES.md`.
* **File Output:**
  * `resources/views/dashboard/index.blade.php`
* **Kriteria Selesai:**
  * Menampilkan widget angka total insiden, jumlah relawan bertugas, dan peringatan stok logistik menipis.
* **Larangan:** Jangan memuat library grafik eksternal yang lambat dimuat.
* **Estimasi:** 4 Jam
* **Dependensi:** DAS-003

### DAS-005: Feature Test Dashboard
* **Nama Task:** Pengujian Otomatis Otorisasi Agregasi Dashboard.
* **Tujuan:** Mengetes pembatasan tampilan data statistik berdasarkan role wilayah pengguna.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M19.10).
* **File Output:**
  * `tests/Feature/Dashboard/DashboardAccessTest.php`
* **Kriteria Selesai:**
  * Test memastikan PCNU hanya mendapat kembalian data statistik scope kawasannya sendiri.
* **Larangan:** Dilarang melompati testing otorisasi negatif (publik dilarang masuk).
* **Estimasi:** 2.5 Jam
* **Dependensi:** DAS-004

---

## 20. Domain: Portal Publik (PUB)

### PUB-001: Public Map & Laporan Kejadian Form Route
* **Nama Task:** Pendaftaran Routing Portal Publik Terbuka.
* **Tujuan:** Menyediakan link form laporan kebencanaan untuk masyarakat umum.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M04.10, M19).
* **File Output:**
  * `routes/web.php` (bagian Publik Portal)
* **Kriteria Selesai:**
  * Route `/` (home portal) dan `/lapor` terdaftar bebas tanpa middleware login.
* **Larangan:** Jangan menerapkan middleware auth pada endpoint pengiriman laporan masyarakat umum.
* **Estimasi:** 0.5 Jam
* **Dependensi:** INS-001

### PUB-002: PublicController
* **Nama Task:** Implementasi Controller Portal Publik.
* **Tujuan:** Mengelola perolehan data koordinat peta insiden aktif untuk konsumsi umum.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M19).
* **File Output:**
  * `app/Http/Controllers/PublicController.php`
* **Kriteria Selesai:**
  * Metode controller mengembalikan data koordinat khusus insiden berstatus `respon` atau `pemulihan`.
* **Larangan:** Jangan pernah mengekspos insiden berstatus `draft` atau `dibatalkan` ke publik.
* **Estimasi:** 2.5 Jam
* **Dependensi:** PUB-001

### PUB-003: Blade Views Portal Publik & Leaflet Map
* **Nama Task:** Pembuatan Halaman Peta Publik Kebencanaan NU.
* **Tujuan:** Menyediakan UI interaktif peta Leaflet.js yang menunjukkan titik bencana aktif bagi masyarakat umum.
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M19.8), `docs/UI_RULES.md`.
* **File Output:**
  * `resources/views/publik/peta.blade.php`
  * `resources/views/publik/laporan.blade.php`
* **Kriteria Selesai:**
  * Peta berhasil memetakan marker koordinat bencana dari database menggunakan library Leaflet.js.
  * Form laporan publik berfungsi mengirim data laporan awal kejadian.
* **Larangan:** Jangan memuat Google Maps API berbayar; gunakan OpenStreetMap standar gratis.
* **Estimasi:** 4 Jam
* **Dependensi:** PUB-002

### PUB-004: Feature Test Portal Publik
* **Nama Task:** Pengujian Fitur Otomatis Portal Publik.
* **Tujuan:** Mengetes aksesbilitas menu peta dan form laporan publik oleh pengguna tamu (guest).
* **Referensi Dokumen:** `docs/IMPLEMENTATION_BACKLOG.md` (M04.12).
* **File Output:**
  * `tests/Feature/Publik/PortalPublikTest.php`
* **Kriteria Selesai:**
  * Lulus pengujian pengiriman laporan kebencanaan oleh tamu yang menghasilkan status menunggu validasi.
* **Larangan:** Jangan mengabaikan assert status validasi koordinat laporan masuk di luar rentang geografis.
* **Estimasi:** 3 Jam
* **Dependensi:** PUB-003
