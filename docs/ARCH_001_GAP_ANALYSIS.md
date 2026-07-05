# ARCH-001: GAP ANALYSIS — Architecture Realignment, API-First Transition & Documentation Synchronization

Dokumen ini menganalisis kondisi arsitektur riil pada *codebase* NURISK saat ini dibandingkan dengan dokumentasi yang direncanakan sebelumnya, serta merinci transisi resmi menuju **Hybrid Monolith Architecture**.

---

## 1. Current Architecture (Status Aktual Codebase)

Berikut adalah pemetaan komponen *codebase* NURISK riil berdasarkan file fisik yang ada di dalam repositori:

### 1.1. Domain: Auth & Security
* **Model**: 
  - [AuthUser.php](file:///home/londo/nurisk/app/Models/AuthUser.php) (extends Authenticatable, PK: `id_pengguna`, username: `no_hp`, kata sandi: `kata_sandi`)
  - [AuthRole.php](file:///home/londo/nurisk/app/Models/AuthRole.php) (PK: `id_peran`)
  - [AuthPenggunaProfil.php](file:///home/londo/nurisk/app/Models/AuthPenggunaProfil.php) (PK: `id_pengguna`)
  - [AuthPenggunaKeahlian.php](file:///home/londo/nurisk/app/Models/AuthPenggunaKeahlian.php) (pivot table representation)
  - [AuthKeahlianMaster.php](file:///home/londo/nurisk/app/Models/AuthKeahlianMaster.php)
* **Migration**:
  - `database/migrations/0001_01_01_000000_create_users_table.php` (hanya membuat tabel standard `users`, `password_reset_tokens`, `sessions`)
  - **Catatan**: Tabel `auth_users`, `auth_roles`, `auth_pengguna_profil` tidak memiliki berkas migrasi fisik (dibuat secara dinamis di level testing).
* **Policy**: Tidak ditemukan `AuthUserPolicy.php` fisik di folder `app/Policies`.
* **Service**: 
  - [AuthenticationService.php](file:///home/londo/nurisk/app/Services/Auth/AuthenticationService.php)
  - [AuthorizationContextService.php](file:///home/londo/nurisk/app/Services/Auth/AuthorizationContextService.php)
* **Request**: 
  - [LoginRequest.php](file:///home/londo/nurisk/app/Http/Requests/Auth/LoginRequest.php) (validasi `no_hp` dan `kata_sandi`)
  - [ProfileUpdateRequest.php](file:///home/londo/nurisk/app/Http/Requests/ProfileUpdateRequest.php)
* **Controller**:
  - [LoginController.php](file:///home/londo/nurisk/app/Http/Controllers/Auth/LoginController.php)
  - Breeze auth controllers (RegisteredUserController, ConfirmablePasswordController, AuthenticatedSessionController, EmailVerificationNotificationController, NewPasswordController, EmailVerificationPromptController, PasswordResetLinkController, PasswordController, VerifyEmailController)
* **API Controller**: Tidak ada (auth murni menggunakan stateful session/cookie).
* **Resource**: Tidak ada.
* **Web View**: Blade templates di `resources/views/auth/*` (login, register, confirm-password, forgot-password, reset-password, verify-email) serta `profile/edit.blade.php`.
* **Feature Test**:
  - `tests/Feature/Auth/AuthenticationTest.php`
  - `tests/Feature/Auth/LoginControllerTest.php`
  - `tests/Feature/Auth/LoginRouteTest.php`
  - `tests/Feature/Auth/RegistrationTest.php`
  - `tests/Feature/Auth/LogoutTest.php`
  - `tests/Feature/Auth/PasswordResetTest.php`
  - `tests/Feature/Auth/EmailVerificationTest.php`
  - `tests/Feature/Auth/PasswordConfirmationTest.php`
  - `tests/Feature/Auth/PasswordUpdateTest.php`
* **Unit Test**:
  - `tests/Unit/AuthenticationServiceTest.php`
  - `tests/Unit/AuthModelTest.php`
  - `tests/Unit/AuthProviderTest.php`
  - `tests/Unit/AuthFactoryTest.php`
  - `tests/Unit/LoginRequestTest.php`

### 1.2. Domain: Organisasi & Wilayah
* **Model**:
  - [OrganisasiPcnu.php](file:///home/londo/nurisk/app/Models/OrganisasiPcnu.php)
  - [OrganisasiMwc.php](file:///home/londo/nurisk/app/Models/OrganisasiMwc.php)
  - [OrganisasiRanting.php](file:///home/londo/nurisk/app/Models/OrganisasiRanting.php)
  - [OrganisasiUnit.php](file:///home/londo/nurisk/app/Models/OrganisasiUnit.php)
  - [WilayahKabupaten.php](file:///home/londo/nurisk/app/Models/WilayahKabupaten.php)
  - [WilayahKecamatan.php](file:///home/londo/nurisk/app/Models/WilayahKecamatan.php)
  - [WilayahDesa.php](file:///home/londo/nurisk/app/Models/WilayahDesa.php)
  - [WilayahScope.php](file:///home/londo/nurisk/app/Models/WilayahScope.php)
* **Migration**:
  - `database/migrations/2026_06_16_000005_create_bencana_master_jenis_table.php` (termasuk master data lainnya yang digabung)
  - **Catatan**: Tabel `organisasi_*` dan `wilayah_*` tidak memiliki migrasi fisik (dibuat dinamis di testing).
* **Policy**: Tidak ada.
* **Service**: [MasterDataService.php](file:///home/londo/nurisk/app/Services/MasterData/MasterDataService.php)
* **Request**: Tidak ada.
* **Controller**: Tidak ada.
* **API Controller**: [WilayahApiController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/WilayahApiController.php) (endpoint dropdown wilayah)
* **Resource**:
  - [WilayahKabupatenResource.php](file:///home/londo/nurisk/app/Http/Resources/WilayahKabupatenResource.php)
  - [WilayahKecamatanResource.php](file:///home/londo/nurisk/app/Http/Resources/WilayahKecamatanResource.php)
  - [WilayahDesaResource.php](file:///home/londo/nurisk/app/Http/Resources/WilayahDesaResource.php)
* **Web View**: Tidak ada.
* **Feature Test**:
  - `tests/Feature/Api/WilayahApiTest.php`
  - `tests/Feature/Seeder/WilayahSeederTest.php`
* **Unit Test**:
  - `tests/Unit/WilayahModelTest.php`
  - `tests/Unit/Models/WilayahScopeTest.php`
  - `tests/Unit/OrganisasiModelTest.php`
  - `tests/Unit/MasterDataServiceTest.php`

### 1.3. Domain: Jabatan Struktural
* **Model**:
  - [JabatanPosisi.php](file:///home/londo/nurisk/app/Models/JabatanPosisi.php) (`master_jabatan`)
  - [PenggunaJabatan.php](file:///home/londo/nurisk/app/Models/PenggunaJabatan.php) (`pengguna_jabatan`)
* **Migration**:
  - [2026_06_16_000003_create_master_jabatan_table.php](file:///home/londo/nurisk/database/migrations/2026_06_16_000003_create_master_jabatan_table.php)
  - [2026_06_16_000004_create_pengguna_jabatan_table.php](file:///home/londo/nurisk/database/migrations/2026_06_16_000004_create_pengguna_jabatan_table.php)
* **Policy**: [JabatanPolicy.php](file:///home/londo/nurisk/app/Policies/JabatanPolicy.php)
* **Service**: Tidak ada (menggunakan DB transaction langsung di Controller).
* **Request**:
  - [StoreJabatanRequest.php](file:///home/londo/nurisk/app/Http/Requests/Admin/StoreJabatanRequest.php)
  - [UpdateJabatanRequest.php](file:///home/londo/nurisk/app/Http/Requests/Admin/UpdateJabatanRequest.php)
* **Controller**: [JabatanController.php](file:///home/londo/nurisk/app/Http/Controllers/Admin/JabatanController.php) (web panel admin)
* **API Controller**: Tidak ada.
* **Resource**:  Tidak ada.
* **Web View**: Blade templates di `resources/views/admin/jabatan/*` (index, create, edit)
* **Feature Test**:
  - `tests/Feature/Admin/JabatanTest.php`
  - `tests/Feature/Seeder/JabatanPosisiSeederTest.php`
* **Unit Test**:
  - `tests/Unit/Models/AuthUserJabatanRelationTest.php`
  - `tests/Unit/Models/JabatanPosisiModelTest.php`
  - `tests/Unit/Models/PenggunaJabatanModelTest.php`

### 1.4. Domain: Insiden
* **Model**:
  - [OperasiInsiden.php](file:///home/londo/nurisk/app/Models/OperasiInsiden.php) (`operasi_insiden`)
  - [RiwayatStatusInsiden.php](file:///home/londo/nurisk/app/Models/RiwayatStatusInsiden.php) (`riwayat_status_insiden`)
  - [BencanaMasterJenis.php](file:///home/londo/nurisk/app/Models/BencanaMasterJenis.php) (`bencana_master_jenis`)
* **Migration**:
  - [2026_06_16_000005_create_bencana_master_jenis_table.php](file:///home/londo/nurisk/database/migrations/2026_06_16_000005_create_bencana_master_jenis_table.php)
  - [2026_06_16_000006_create_operasi_insiden_table.php](file:///home/londo/nurisk/database/migrations/2026_06_16_000006_create_operasi_insiden_table.php)
  - [2026_06_16_000007_create_riwayat_status_insiden_table.php](file:///home/londo/nurisk/database/migrations/2026_06_16_000007_create_riwayat_status_insiden_table.php)
* **Policy**: [InsidenPolicy.php](file:///home/londo/nurisk/app/Policies/InsidenPolicy.php)
* **Service**: [InsidenService.php](file:///home/londo/nurisk/app/Services/InsidenService.php) (transisi status & locking)
* **Request**:
  - [StoreInsidenRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/StoreInsidenRequest.php)
  - [UpdateInsidenRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/UpdateInsidenRequest.php)
  - [UbahStatusInsidenRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/UbahStatusInsidenRequest.php)
* **Controller**:
  - [InsidenController.php](file:///home/londo/nurisk/app/Http/Controllers/Operasi/InsidenController.php) (Web CRUD)
  - [InsidenStatusController.php](file:///home/londo/nurisk/app/Http/Controllers/Operasi/InsidenStatusController.php) (Web update status)
* **API Controller**: Tidak ada.
* **Resource**: Tidak ada.
* **Web View**: Blade templates di `resources/views/operasi/insiden/*` (index, show, create, edit)
* **Feature Test**:
  - `tests/Feature/Insiden/InsidenCrudTest.php`
  - `tests/Feature/Insiden/InsidenStatusTest.php`
* **Unit Test**:
  - `tests/Unit/Operasi/OperasiModelsTest.php` (unit tests model insiden dasar)
  - `tests/Unit/Operasi/OperasiServiceTest.php`

### 1.5. Domain: Pos Aju
* **Model**: [OperasiPosaju.php](file:///home/londo/nurisk/app/Models/OperasiPosaju.php)
* **Migration**: None (skema dideklarasikan dinamis di `CreatesOperasiSchema.php`).
* **Policy**: [OperasiPosajuPolicy.php](file:///home/londo/nurisk/app/Policies/OperasiPosajuPolicy.php)
* **Service**: [OperasiPosajuService.php](file:///home/londo/nurisk/app/Services/Operasi/OperasiPosajuService.php)
* **Request**:
  - [StorePosajuRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/StorePosajuRequest.php)
  - [UpdatePosajuRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/UpdatePosajuRequest.php)
  - [ActivatePosajuRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/ActivatePosajuRequest.php)
  - [ClosePosajuRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/ClosePosajuRequest.php)
  - [ExtendPosajuRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/ExtendPosajuRequest.php)
* **Controller**: Tidak ada.
* **API Controller**: [OperasiPosajuController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Operasi/OperasiPosajuController.php)
* **Resource**:
  - [OperasiPosajuResource.php](file:///home/londo/nurisk/app/Http/Resources/Operasi/OperasiPosajuResource.php)
  - [OperasiPosajuCollection.php](file:///home/londo/nurisk/app/Http/Resources/Operasi/OperasiPosajuCollection.php)
* **Web View**: Tidak ada.
* **Feature Test**:
  - `tests/Feature/Operasi/HttpPosajuTest.php` (API Integration test)
  - `tests/Feature/Operasi/OperasiPosajuPolicyTest.php`
  - `tests/Feature/Operasi/PosajuResourceTest.php`
* **Unit Test**: Tidak ada.

### 1.6. Domain: Klaster & Tugas
* **Model**:
  - [OperasiKlaster.php](file:///home/londo/nurisk/app/Models/OperasiKlaster.php)
  - [OperasiTugas.php](file:///home/londo/nurisk/app/Models/OperasiTugas.php)
* **Migration**: None (skema dideklarasikan dinamis di `CreatesOperasiSchema.php`).
* **Policy**:
  - [OperasiKlasterPolicy.php](file:///home/londo/nurisk/app/Policies/OperasiKlasterPolicy.php)
  - [OperasiTugasPolicy.php](file:///home/londo/nurisk/app/Policies/OperasiTugasPolicy.php)
* **Service**:
  - [OperasiKlasterService.php](file:///home/londo/nurisk/app/Services/Operasi/OperasiKlasterService.php)
  - [OperasiTugasService.php](file:///home/londo/nurisk/app/Services/Operasi/OperasiTugasService.php)
* **Request**:
  - [StoreKlasterRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/StoreKlasterRequest.php)
  - [CompleteKlasterRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/CompleteKlasterRequest.php)
  - [UpdateKlasterProgressRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/UpdateKlasterProgressRequest.php)
  - [StoreTugasRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/StoreTugasRequest.php)
  - [StartTugasRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/StartTugasRequest.php)
  - [PauseTugasRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/PauseTugasRequest.php)
  - [CompleteTugasRequest.php](file:///home/londo/nurisk/app/Http/Requests/Operasi/CompleteTugasRequest.php)
* **Controller**: Tidak ada.
* **API Controller**:
  - [OperasiKlasterController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Operasi/OperasiKlasterController.php)
  - [OperasiTugasController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Operasi/OperasiTugasController.php)
* **Resource**:
  - [OperasiKlasterResource.php](file:///home/londo/nurisk/app/Http/Resources/Operasi/OperasiKlasterResource.php)
  - [OperasiKlasterCollection.php](file:///home/londo/nurisk/app/Http/Resources/Operasi/OperasiKlasterCollection.php)
  - [OperasiTugasResource.php](file:///home/londo/nurisk/app/Http/Resources/Operasi/OperasiTugasResource.php)
  - [OperasiTugasCollection.php](file:///home/londo/nurisk/app/Http/Resources/Operasi/OperasiTugasCollection.php)
* **Web View**: Tidak ada.
* **Feature Test**:
  - `tests/Feature/Operasi/HttpKlasterTest.php`
  - `tests/Feature/Operasi/HttpTugasTest.php`
  - `tests/Feature/Operasi/OperasiKlasterPolicyTest.php`
  - `tests/Feature/Operasi/OperasiTugasPolicyTest.php`
  - `tests/Feature/Operasi/KlasterResourceTest.php`
  - `tests/Feature/Operasi/TugasResourceTest.php`
  - `tests/Feature/Operasi/OperasiHotfixTest.php`
* **Unit Test**: Tidak ada.

### 1.7. Domain: Relawan
* **Model**:
  - [RelawanKebutuhan.php](file:///home/londo/nurisk/app/Models/RelawanKebutuhan.php)
  - [RelawanPendaftaran.php](file:///home/londo/nurisk/app/Models/RelawanPendaftaran.php)
  - [RelawanPenugasan.php](file:///home/londo/nurisk/app/Models/RelawanPenugasan.php)
  - [RelawanShift.php](file:///home/londo/nurisk/app/Models/RelawanShift.php)
* **Migration**: None (skema dideklarasikan dinamis di `CreatesRelawanSchema.php`).
* **Policy**:
  - [RelawanKebutuhanPolicy.php](file:///home/londo/nurisk/app/Policies/RelawanKebutuhanPolicy.php)
  - [RelawanPendaftaranPolicy.php](file:///home/londo/nurisk/app/Policies/RelawanPendaftaranPolicy.php)
  - [RelawanPenugasanPolicy.php](file:///home/londo/nurisk/app/Policies/RelawanPenugasanPolicy.php)
  - [RelawanProfilPolicy.php](file:///home/londo/nurisk/app/Policies/RelawanProfilPolicy.php)
* **Service**: [RelawanService.php](file:///home/londo/nurisk/app/Services/Relawan/RelawanService.php)
* **Request**:
  - [StoreRelawanKebutuhanRequest.php](file:///home/londo/nurisk/app/Http/Requests/Relawan/StoreRelawanKebutuhanRequest.php)
  - [DaftarRelawanRequest.php](file:///home/londo/nurisk/app/Http/Requests/Relawan/DaftarRelawanRequest.php)
  - [ApproveRelawanRequest.php](file:///home/londo/nurisk/app/Http/Requests/Relawan/ApproveRelawanRequest.php)
  - [RejectRelawanRequest.php](file:///home/londo/nurisk/app/Http/Requests/Relawan/RejectRelawanRequest.php)
  - [AssignRelawanRequest.php](file:///home/londo/nurisk/app/Http/Requests/Relawan/AssignRelawanRequest.php)
* **Controller**: Tidak ada.
* **API Controller**:
  - [RelawanPendaftaranController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Relawan/RelawanPendaftaranController.php)
  - [RelawanPenugasanController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Relawan/RelawanPenugasanController.php)
  - [RelawanProfilController.php](file:///home/londo/nurisk/app/Http/Controllers/Api/Relawan/RelawanProfilController.php)
* **Resource**:
  - [RelawanPendaftaranResource.php](file:///home/londo/nurisk/app/Http/Resources/Relawan/RelawanPendaftaranResource.php)
  - [RelawanPenugasanResource.php](file:///home/londo/nurisk/app/Http/Resources/Relawan/RelawanPenugasanResource.php)
  - [RelawanProfilResource.php](file:///home/londo/nurisk/app/Http/Resources/Relawan/RelawanProfilResource.php)
* **Web View**: Tidak ada.
* **Feature Test**:
  - `tests/Feature/Relawan/RelawanApiTest.php`
  - `tests/Feature/Relawan/RelawanPolicyTest.php`
  - `tests/Feature/Relawan/RelawanRequestTest.php`
  - `tests/Feature/Relawan/RelawanServiceTest.php`
  - `tests/Feature/Relawan/RelawanPendaftaranResourceTest.php`
  - `tests/Feature/Relawan/RelawanPenugasanResourceTest.php`
  - `tests/Feature/Relawan/RelawanProfilResourceTest.php`
* **Unit Test**:
  - `tests/Unit/RelawanModelTest.php`

---

## 2. Documentation Drift (Penyimpangan Dokumentasi)

Terdapat perbedaan mencolok antara status riil codebase dan spesifikasi dokumen di folder `/docs`:

1. **Stale Project Status**: `PROJECT_STATUS.md` mencantumkan status seluruh domain (kecuali Auth di angka 15%) sebagai **0% selesai** (Belum Dimulai). Sementara di codebase riil, domain Auth, Organisasi, Wilayah, Jabatan, Insiden, Pos Aju, Klaster, Tugas, dan Relawan telah memiliki file yang kokoh serta test coverage 100% hijau.
2. **Missing Physical Migrations**: Dokumen `DATABASE_CONVENTION.md` dan `MODULE_IMPLEMENTATION_ORDER.md` menyiratkan adanya berkas migrasi fisik untuk seluruh tabel. Namun, migrasi fisik yang benar-benar ada di `database/migrations/` hanya terbatas pada:
   - Users/Sessions/Cache/Jobs (Laravel Core)
   - Spatie Permissions
   - Master Jabatan & Pengguna Jabatan
   - Bencana Master Jenis
   - Operasi Insiden
   - Riwayat Status Insiden
   
   Semua tabel untuk domain **Auth/Profil**, **Organisasi**, **Wilayah**, **Pos Aju**, **Klaster/Tugas**, dan **Relawan** dibuat secara dinamis di dalam metode `setUp()` pada berkas pengujian menggunakan trait `CreatesOperasiSchema` dan `CreatesRelawanSchema`.
3. **Web Backlog Divergence**: Dokumen backlog (`ATOMIC_TASK_BACKLOG.md` dan `IMPLEMENTATION_BACKLOG.md`) mengharuskan pembuatan Controller Web dan Blade Views untuk Pos Aju, Relawan, dan Klaster. Pada implementasinya, modul-modul tersebut dibangun sebagai REST API murni (`app/Http/Controllers/Api/`).

---

## 3. Architecture Drift (Pergeseran Arsitektur)

Sistem NURISK saat ini terbagi menjadi 3 pola arsitektur penyajian data:

### 3.1. Bagian yang Murni Blade-First (Web Server-Rendered)
- **Panel Autentikasi**: Login, Register, Forgot Password, Reset Password, dll.
- **Master Data Jabatan**: Panel CRUD Jabatan untuk `super_admin` (`admin/jabatan`).
- **Manajemen Insiden**: Pembuatan insiden, visualisasi peta kejadian, detail informasi insiden, dan transisi status insiden.

### 3.2. Bagian yang Murni REST API-First (Flutter Ready)
- **Pos Aju**: Seluruh operasi pendirian, perpanjangan, dan penutupan.
- **Klaster & Tugas**: Manajemen alur kerja tim, koordinator, progres klaster, dan start/pause/complete tugas.
- **Relawan**: Kebutuhan relawan, registrasi mandiri, verifikasi, approval, penugasan, dan shift.

### 3.3. Bagian yang Hybrid
- **Wilayah**: Menggunakan model Eloquent untuk seeder, tetapi menyediakan API controller (`WilayahApiController`) agar bisa dikonsumsi oleh dropdown AJAX di Blade Web maupun Flutter Mobile Client.
- **Auth**: Authenticated Session dikelola via Cookie/Session web, namun otorisasi context dikonsolidasikan lewat `AuthorizationContextService` yang siap melayani REST API.
