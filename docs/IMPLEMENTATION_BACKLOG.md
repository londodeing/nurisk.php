# IMPLEMENTATION_BACKLOG.md — NURISK
# Backlog Implementasi Sprint — FROZEN

> **Senior Technical Project Manager & Laravel Solution Architect**
> Versi: 1.0 — Tanggal: 16 Juni 2026
> Sumber: SQL Dump v37, Dokumen Pra-Produksi Final
>
> ⚠️ **ATURAN KERAS:**
> - DILARANG mendesain ulang sistem
> - DILARANG membuat fitur baru
> - DILARANG membuat tabel baru
> - DILARANG mengubah workflow
> - DILARANG refactoring konseptual
> - Setiap modul dikerjakan **tuntas** sebelum lanjut ke modul berikutnya
> - Definition of Done WAJIB terpenuhi 100% sebelum merge ke `main`

---

## INDEKS MODUL

| Fase | # | Modul | Prioritas | Status |
|------|---|-------|-----------|--------|
| 1 | M01 | Auth & User Management | 🔴 KRITIS | ⬜ |
| 1 | M02 | Authorization Infrastructure | 🔴 KRITIS | ⬜ |
| 1 | M03 | Master Data & Seeder | 🔴 KRITIS | ⬜ |
| 2 | M04 | Insiden & Laporan Kejadian | 🔴 KRITIS | ⬜ |
| 2 | M05 | Assessment | 🔴 KRITIS | ⬜ |
| 2 | M06 | Sitrep | 🔴 KRITIS | ⬜ |
| 3 | M07 | Pleno & Eskalasi | 🟠 TINGGI | ⬜ |
| 3 | M08 | Surat Menyurat | 🟠 TINGGI | ⬜ |
| 4 | M09 | Assignment & Otoritas Kontekstual | 🟠 TINGGI | ⬜ |
| 4 | M10 | Mobilisasi Personel & Klaster | 🟡 SEDANG | ⬜ |
| 4 | M11 | Shift / Periode Operasional | 🟡 SEDANG | ⬜ |
| 5 | M12 | Logistik | 🔴 KRITIS | ⬜ |
| 5 | M13 | Relawan | 🟠 TINGGI | ⬜ |
| 5 | M14 | Aset | 🟡 SEDANG | ⬜ |
| 6 | M15 | Pos Aju | 🟠 TINGGI | ⬜ |
| 6 | M16 | Pengungsian & Penerima Manfaat | 🟡 SEDANG | ⬜ |
| 6 | M17 | Jurnal Operasi & Audit Trail | 🟡 SEDANG | ⬜ |
| 7 | M18 | Command Center & Dashboard | 🟠 TINGGI | ⬜ |
| 7 | M19 | Public UI & Laporan Publik | 🟡 SEDANG | ⬜ |

> **M17 (Feedback Klaster) dan M18 (Gap Kebutuhan)** dari MODULE_IMPLEMENTATION_ORDER.md digabung ke M17 (Jurnal/Audit) karena tabel SQL belum terkonfirmasi. Jika tabel dikonfirmasi, buat backlog tambahan.

---

---

# FASE 1 — FONDASI SISTEM

---

## M01 — Auth & User Management

### 1. Nama Modul
`Auth & User Management`

### 2. Tujuan Modul
Membangun sistem autentikasi berbasis `no_hp` + `kata_sandi`, manajemen akun pengguna, serta sinkronisasi ke Spatie Permission melalui trigger database yang sudah ada. Modul ini adalah fondasi seluruh sistem — tidak ada modul lain yang dapat berjalan sebelum M01 selesai.

### 3. Dependensi Modul
- Tidak ada dependensi modul sebelumnya
- Paket: `laravel/sanctum` (atau session-based auth)
- Paket: `spatie/laravel-permission` (sudah ada di composer berdasarkan tabel `model_has_roles`)

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `auth_users` | Akun utama, login via `no_hp` + `kata_sandi` |
| `auth_roles` | 5 role PRD (super_admin, pwnu, pcnu, relawan, publik) |
| `auth_pengguna_profil` | Data profil: `nik`, `nama_lengkap`, `email` |
| `auth_keahlian_master` | Daftar keahlian relawan |
| `auth_pengguna_keahlian` | Pivot pengguna ↔ keahlian |
| `model_has_roles` | Spatie sync (diisi trigger `tr_sync_user_role_insert`) |
| `model_has_permissions` | Spatie permission (tabel infrastruktur) |

**Kolom kritis `auth_users`:**
- PK: `id_pengguna` (bukan `id`)
- Login: `no_hp`, `kata_sandi`
- Status: `status_akun` ENUM(`menunggu`,`aktif`,`nonaktif`,`suspend`)
- Timestamps: `dibuat_pada`, `diperbarui_pada` (bukan `created_at`/`updated_at`)
- Scope: `default_scope_type` ENUM(`pwnu`,`pcnu`,`mwc`,`ranting`,`lembaga`,`banom`), `default_scope_id`

### 5. Model Laravel yang Diperlukan
```
app/Models/AuthUser.php
app/Models/AuthRole.php
app/Models/AuthPenggunaProfil.php
app/Models/AuthKeahlianMaster.php
app/Models/AuthPenggunaKeahlian.php
```

**Wajib di `AuthUser`:**
```php
protected $table = 'auth_users';
protected $primaryKey = 'id_pengguna';
const CREATED_AT = 'dibuat_pada';
const UPDATED_AT = 'diperbarui_pada';
protected $hidden = ['kata_sandi'];

// Override untuk Laravel Auth
public function getAuthPassword(): string { return $this->kata_sandi; }

// Relasi
public function peran(): BelongsTo    // → auth_roles via id_peran
public function profil(): HasOne      // → auth_pengguna_profil via id_pengguna
public function keahlian(): BelongsToMany  // → auth_keahlian_master via auth_pengguna_keahlian
```

**Wajib di `config/auth.php`:**
```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Models\AuthUser::class,
    ],
],
```

### 6. Policy yang Diperlukan
```
app/Policies/AuthUserPolicy.php
```
- `view`: hanya super_admin atau pengguna itu sendiri
- `update`: super_admin atau pengguna sendiri (kecuali `id_peran` dan `status_akun`)
- `updateRole`: hanya super_admin
- `activate`: super_admin atau pwnu (ubah `status_akun` menjadi `aktif`)

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Auth/LoginController.php
app/Http/Controllers/Auth/LogoutController.php
app/Http/Controllers/Auth/RegisterPublikController.php
app/Http/Controllers/Admin/UserManagementController.php
```

**`LoginController`:** `showLoginForm()`, `login()` — gunakan `no_hp` sebagai username field
**`RegisterPublikController`:** `showForm()`, `store()` — `status_akun = 'menunggu'` by default
**`UserManagementController`:** `index()`, `show()`, `edit()`, `update()`, `aktivasi()`, `suspend()`

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Auth/LoginRequest.php
app/Http/Requests/Auth/RegisterPublikRequest.php
app/Http/Requests/Admin/UpdateUserRequest.php
app/Http/Requests/Admin/AktivasiUserRequest.php
```

**`LoginRequest` rules:**
```php
'no_hp'      => 'required|string|max:20',
'kata_sandi' => 'required|string|min:8',
```

**`RegisterPublikRequest` rules:**
```php
'no_hp'         => 'required|unique:auth_users,no_hp|max:20',
'kata_sandi'    => 'required|confirmed|min:8',
'nama_lengkap'  => 'required|string|max:150',
'nik'           => 'nullable|digits:16|unique:auth_pengguna_profil,nik',
'email'         => 'nullable|email|max:150',
```

### 9. Blade yang Diperlukan
```
resources/views/auth/login.blade.php
resources/views/auth/register.blade.php
resources/views/admin/users/index.blade.php
resources/views/admin/users/show.blade.php
resources/views/admin/users/edit.blade.php
resources/views/layouts/app.blade.php        ← layout master internal
resources/views/layouts/public.blade.php     ← layout publik
resources/views/layouts/partials/sidebar.blade.php
resources/views/layouts/partials/navbar.blade.php
```

### 10. Route yang Diperlukan
```php
// routes/web.php

// Auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/daftar', [RegisterPublikController::class, 'showForm'])->name('register');
    Route::post('/daftar', [RegisterPublikController::class, 'store']);
});

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout')->middleware('auth');

// User Management (super_admin only)
Route::middleware(['auth', 'role:super_admin'])->prefix('admin/users')->name('admin.users.')->group(function () {
    Route::get('/', [UserManagementController::class, 'index'])->name('index');
    Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
    Route::patch('/{user}/aktivasi', [UserManagementController::class, 'aktivasi'])->name('aktivasi');
    Route::patch('/{user}/suspend', [UserManagementController::class, 'suspend'])->name('suspend');
});
```

### 11. API yang Diperlukan
```
GET  /api/auth/me          → data pengguna yang sedang login (untuk AJAX navbar)
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Auth/LoginTest.php
  ✓ user_dapat_login_dengan_no_hp_valid
  ✓ login_gagal_dengan_kata_sandi_salah
  ✓ akun_menunggu_tidak_dapat_login
  ✓ akun_suspend_tidak_dapat_login
  ✓ akun_nonaktif_tidak_dapat_login
  ✓ login_berhasil_redirect_ke_dashboard

tests/Feature/Auth/RegisterTest.php
  ✓ publik_dapat_mendaftar_akun_baru
  ✓ no_hp_duplikat_ditolak
  ✓ nik_duplikat_ditolak
  ✓ status_akun_default_menunggu_setelah_register

tests/Feature/Admin/UserManagementTest.php
  ✓ super_admin_dapat_aktivasi_akun_menunggu
  ✓ super_admin_dapat_suspend_akun_aktif
  ✓ pcnu_tidak_dapat_akses_user_management
```

### 13. Definition of Done
- [ ] Migrasi `auth_users`, `auth_pengguna_profil`, `auth_keahlian_master`, `auth_pengguna_keahlian` berjalan tanpa error
- [ ] Login via `no_hp` + `kata_sandi` berhasil
- [ ] Redirect setelah login sesuai role (`id_peran`)
- [ ] Akun `menunggu`/`suspend`/`nonaktif` ditolak login dengan pesan error yang jelas
- [ ] Register publik menghasilkan akun dengan `status_akun = 'menunggu'`
- [ ] Trigger `tr_sync_user_role_insert` memastikan `model_has_roles` tersync otomatis
- [ ] Layout `app.blade.php` dan `public.blade.php` berfungsi
- [ ] Sidebar menampilkan menu sesuai `id_peran` pengguna
- [ ] Semua feature test passing dengan MySQL

---

## M02 — Authorization Infrastructure

### 1. Nama Modul
`Authorization Infrastructure`

### 2. Tujuan Modul
Membangun infrastruktur otorisasi 4 lapis: role global, jabatan struktural, scope wilayah, dan assignment operasional. Modul ini menyediakan fondasi Policy, Gate, dan Middleware yang akan digunakan oleh semua modul selanjutnya.

### 3. Dependensi Modul
- **M01** (Auth) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `master_jabatan` | Daftar jabatan struktural organisasi |
| `pengguna_jabatan` | Mapping pengguna → jabatan aktif |
| `auth_users` | `default_scope_type`, `default_scope_id`, `id_peran` |
| `auth_roles` | 5 role PRD |

### 5. Model Laravel yang Diperlukan
```
app/Models/JabatanPosisi.php
app/Models/PenggunaJabatan.php
```

**`JabatanPosisi`:**
```php
protected $table = 'master_jabatan';
protected $primaryKey = 'id_jabatan';
public $timestamps = false;
```

**`PenggunaJabatan`:**
```php
protected $table = 'pengguna_jabatan';
const CREATED_AT = 'dibuat_pada';
const UPDATED_AT = 'diperbarui_pada';
```

**Tambahkan di `AuthUser`:**
```php
public function jabatan(): HasMany  // → pengguna_jabatan
public function jabatanAktif(): HasOne  // → jabatan aktif saat ini
```

### 6. Policy yang Diperlukan
```
app/Policies/BaseNuriskPolicy.php   ← abstract base dengan helper scope
app/Policies/JabatanPolicy.php
```

**`BaseNuriskPolicy` wajib menyediakan:**
```php
protected function isSuperAdmin(AuthUser $user): bool
protected function isPwnu(AuthUser $user): bool
protected function isPcnu(AuthUser $user): bool
protected function isRelawan(AuthUser $user): bool
protected function isPublik(AuthUser $user): bool
protected function cekScopeWilayah(AuthUser $user, int $idPcnu): bool
    // → true jika id_peran ∈ [1,2] ATAU (id_peran=3 AND default_scope_id == $idPcnu)
protected function hasAssignment(AuthUser $user, int $idInsiden, string $peran = null): bool
    // → cek operasi_penugasan aktif untuk user & insiden
```

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Admin/JabatanController.php
```
CRUD master_jabatan — hanya `super_admin`.

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Admin/StoreJabatanRequest.php
app/Http/Requests/Admin/UpdateJabatanRequest.php
```

### 9. Blade yang Diperlukan
```
resources/views/admin/jabatan/index.blade.php
resources/views/admin/jabatan/create.blade.php
resources/views/admin/jabatan/edit.blade.php
```

### 10. Route yang Diperlukan
```php
// Gate definitions — di AuthServiceProvider::boot()
Gate::define('finalize-sitrep',   fn($u) => in_array($u->id_peran, [1,2,3]));
Gate::define('escalate-insiden',  fn($u) => in_array($u->id_peran, [1,2]));
Gate::define('finalize-pleno',    fn($u) => in_array($u->id_peran, [1,2]));
Gate::define('approve-logistik',  fn($u) => in_array($u->id_peran, [1,2,3]));
Gate::define('sign-surat',        fn($u, $surat) => /* cek jabatan */);

// Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('admin/jabatan')->name('admin.jabatan.')->group(function () {
    Route::resource('/', JabatanController::class);
});
```

### 11. API yang Diperlukan
Tidak ada API khusus untuk modul ini.

### 12. Feature Test yang Diperlukan
```
tests/Feature/Auth/AuthorizationTest.php
  ✓ super_admin_dapat_akses_semua_route
  ✓ pwnu_tidak_dapat_akses_route_super_admin
  ✓ pcnu_dibatasi_scope_wilayah_sendiri
  ✓ relawan_tidak_dapat_akses_route_internal_tanpa_assignment
  ✓ publik_hanya_akses_route_publik
  ✓ gate_escalate_insiden_hanya_untuk_pwnu
  ✓ gate_finalize_pleno_hanya_untuk_pwnu
  ✓ helper_cek_scope_wilayah_benar
```

### 13. Definition of Done
- [ ] Migration `master_jabatan`, `pengguna_jabatan` berhasil
- [ ] `AuthServiceProvider` mendaftarkan semua Policy
- [ ] Semua 5 Gate terdefinisi
- [ ] `BaseNuriskPolicy` dengan 6 helper method tersedia dan diuji
- [ ] Middleware `role:*` dari Spatie berfungsi
- [ ] Helper `cekScopeWilayah()` mengembalikan hasil benar untuk semua kombinasi role/scope
- [ ] Semua feature test passing

---

## M03 — Master Data & Seeder

### 1. Nama Modul
`Master Data & Seeder`

### 2. Tujuan Modul
Mengisi seluruh data master yang sudah ada di SQL dump ke dalam database melalui Seeder Laravel. Juga menyediakan CRUD panel admin untuk master data yang bisa berubah.

### 3. Dependensi Modul
- **M01**, **M02** — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Data | Jumlah |
|---|---|---|
| `auth_roles` | 5 role PRD | 5 baris |
| `bencana_master_jenis` | 13 jenis bencana | 13 baris |
| `master_satuan` | 29 satuan | 29 baris |
| `logistik_kategori` | 7 kategori logistik | 7 baris |
| `auth_keahlian_master` | 7 keahlian | 7 baris |
| `aset_master_kategori` | 5 kategori aset | 5 baris |
| `aset_master_jenis` | 6 jenis aset | 6 baris |
| `aset_master_status` | 5 status aset | 5 baris |
| `operasi_master_klaster` | 6 klaster | 6 baris |
| `master_jabatan` | 15 jabatan default | 15 baris |
| `master_surat_jenis` | Input manual | — |
| `master_jabatan_penandatangan` | Input manual | — |

### 5. Model Laravel yang Diperlukan
```
app/Models/BencanaMasterJenis.php
app/Models/MasterSatuan.php
app/Models/LogistikKategori.php
app/Models/AsetMasterKategori.php
app/Models/AsetMasterJenis.php
app/Models/AsetMasterStatus.php
app/Models/OperasiMasterKlaster.php
app/Models/OperasiMasterIndikator.php
app/Models/MasterSuratJenis.php
app/Models/MasterSuratTemplate.php
app/Models/MasterJabatanPenandatangan.php
```

Semua model master data: `public $timestamps = false;` kecuali yang memiliki timestamp di SQL.

### 6. Policy yang Diperlukan
Tidak ada Policy khusus — CRUD master data hanya untuk `super_admin`, cukup middleware `role:super_admin`.

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Admin/MasterDataController.php
app/Http/Controllers/Admin/MasterSuratJenisController.php
app/Http/Controllers/Admin/MasterJabatanPenandatanganController.php
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Admin/StoreMasterSuratJenisRequest.php
app/Http/Requests/Admin/StoreMasterJabatanRequest.php
```

### 9. Blade yang Diperlukan
```
resources/views/admin/master/index.blade.php          ← dashboard master data
resources/views/admin/master/surat-jenis/index.blade.php
resources/views/admin/master/surat-jenis/create.blade.php
resources/views/admin/master/jabatan-penandatangan/index.blade.php
resources/views/admin/master/jabatan-penandatangan/create.blade.php
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'role:super_admin'])->prefix('admin/master')->name('admin.master.')->group(function () {
    Route::get('/', [MasterDataController::class, 'index'])->name('index');
    Route::resource('surat-jenis', MasterSuratJenisController::class);
    Route::resource('jabatan-penandatangan', MasterJabatanPenandatanganController::class);
});
```

### 11. API yang Diperlukan
```
GET /api/master/bencana-jenis        → untuk dropdown di form insiden
GET /api/master/satuan               → untuk dropdown di form logistik
GET /api/master/klaster              → untuk dropdown di form klaster
GET /api/master/keahlian             → untuk dropdown di form relawan
```
Semua endpoint ini di-cache 1 jam.

### 12. Feature Test yang Diperlukan
```
tests/Feature/Seeder/SeederTest.php
  ✓ semua_tabel_master_terisi_setelah_seeder
  ✓ auth_roles_memiliki_5_role_PRD
  ✓ bencana_master_jenis_memiliki_13_jenis
  ✓ master_satuan_memiliki_29_satuan
  ✓ operasi_master_klaster_memiliki_6_klaster
  ✓ api_master_bencana_jenis_dapat_diakses
```

### 13. Definition of Done
- [ ] `DatabaseSeeder` memanggil semua seeder berurutan tanpa error
- [ ] Semua data master dari SQL dump berhasil di-seed
- [ ] Panel CRUD master data dapat diakses oleh `super_admin`
- [ ] API endpoint master data mengembalikan data yang benar dan ter-cache
- [ ] Semua feature test passing

---

---

# FASE 2 — OPERASIONAL INTI

---

## M04 — Insiden & Laporan Kejadian

### 1. Nama Modul
`Insiden & Laporan Kejadian`

### 2. Tujuan Modul
Membangun domain inti manajemen insiden bencana: pencatatan laporan dari publik, validasi laporan, pembentukan insiden resmi, transisi status insiden, dan histori perubahan status. Ini adalah domain pusat yang hampir semua domain lain bergantung padanya.

### 3. Dependensi Modul
- **M01**, **M02**, **M03** — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `laporan_kejadian` | Laporan awal dari publik atau petugas |
| `operasi_insiden` | Entitas insiden resmi |
| `riwayat_status_insiden` | Histori setiap transisi status |
| `bencana_master_jenis` | FK master jenis bencana |
| `operasi_jurnal` | Setiap transisi status dicatat ke sini |

**Kolom kritis `operasi_insiden`:**
- `kode_kejadian` VARCHAR(25) UNIQUE — generate format: `INS-YYYYMM-{seq}`
- `status_insiden` ENUM(`draft`,`terverifikasi`,`respon`,`pemulihan`,`selesai`,`dibatalkan`)
- `status_operasi` ENUM(`monitoring`,`siaga`,`tanggap_darurat`,`pemulihan`,`selesai`)
- `is_locked` TINYINT(1) — 1 jika selesai, diproteksi trigger `tr_lock_incident_data`
- `id_pcnu` INT — scope wilayah, wajib divalidasi di Policy
- Timestamps waktu: `waktu_verifikasi`, `waktu_respon_dimulai`, `waktu_pemulihan_dimulai`, `waktu_ditutup`
- Soft delete: `dihapus_pada`

**Trigger yang harus diketahui (JANGAN diubah):**
- `tr_validate_temporal_incident`: `waktu_selesai` < `waktu_mulai` → SIGNAL error
- `tr_lock_incident_data`: `is_locked = 1` → semua UPDATE ditolak DB

### 5. Model Laravel yang Diperlukan
```
app/Models/LaporanKejadian.php
app/Models/OperasiInsiden.php
app/Models/RiwayatStatusInsiden.php
```

**`OperasiInsiden` wajib:**
```php
protected $table = 'operasi_insiden';
protected $primaryKey = 'id_insiden';
const CREATED_AT = 'dibuat_pada';
const UPDATED_AT = 'diperbarui_pada';
const DELETED_AT = 'dihapus_pada';
use SoftDeletes;

protected $casts = [
    'is_locked'    => 'boolean',
    'waktu_mulai'  => 'datetime',
    'waktu_selesai' => 'datetime',
];

// Relasi
public function jenisBencana(): BelongsTo   // → bencana_master_jenis via id_jenis_bencana
public function laporanAsal(): BelongsTo    // → laporan_kejadian via id_laporan_asal
public function sitreps(): HasMany          // → operasi_sitrep via id_insiden
public function assessments(): HasMany      // → assessment_utama via id_insiden
public function penugasan(): HasMany        // → operasi_penugasan via id_insiden
public function riwayatStatus(): HasMany    // → riwayat_status_insiden via id_insiden
public function jurnal(): HasMany           // → operasi_jurnal via id_insiden

// Scope
public function scopeByWilayah($query, AuthUser $user)
public function scopeAktif($query)  // status NOT IN (selesai, dibatalkan)
```

**`LaporanKejadian`:**
```php
protected $table = 'laporan_kejadian';
protected $primaryKey = 'id_laporan_kejadian';
const CREATED_AT = 'dibuat_pada';
const UPDATED_AT = 'diperbarui_pada';
```

### 6. Policy yang Diperlukan
```
app/Policies/LaporanKejadianPolicy.php
app/Policies/InsidenPolicy.php
```

**`InsidenPolicy`:**
- `viewAny`: semua role login (data difilter scope di Controller)
- `view`: role ≥ relawan jika dalam scope atau ditugaskan
- `create`: `id_peran` ∈ [1,2,3]
- `update`: [1,2] atau [3] jika `cekScopeWilayah()`
- `delete`: [1,2] — soft delete
- `transisiStatus`: tergantung status saat ini dan role
- `validasiLaporan`: [1,2,3] dalam scope

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Publik/LaporanKejadianController.php
app/Http/Controllers/Operasi/InsidenController.php
app/Http/Controllers/Operasi/InsidenStatusController.php
```

**`InsidenController`:** `index`, `show`, `create`, `store`, `edit`, `update`, `destroy`
**`InsidenStatusController`:** `update` — handle transisi status + catat riwayat + catat jurnal dalam `DB::transaction()`

**Service wajib:**
```
app/Services/InsidenService.php
  → transisiStatus(OperasiInsiden $insiden, string $statusBaru, AuthUser $aktor, ?string $alasan): void
     1. Validasi transisi valid
     2. Update status_insiden
     3. Update kolom waktu terkait (waktu_verifikasi, dll)
     4. Catat riwayat_status_insiden
     5. Catat operasi_jurnal
     6. Set is_locked = 1 jika status = 'selesai'
     Semua dalam DB::transaction()
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Publik/StoreLaporanKejadianRequest.php
app/Http/Requests/Operasi/StoreInsidenRequest.php
app/Http/Requests/Operasi/UpdateInsidenRequest.php
app/Http/Requests/Operasi/TransisiStatusInsidenRequest.php
app/Http/Requests/Operasi/ValidasiLaporanRequest.php
```

**`StoreLaporanKejadianRequest` (publik, bisa tanpa auth):**
```php
'nama_pelapor'        => 'required|string|max:150',
'hp_pelapor'          => 'required|string|max:20',
'id_jenis_bencana'    => 'required|exists:bencana_master_jenis,id_jenis',
'keterangan_situasi'  => 'required|string',
'waktu_kejadian'      => 'required|date',
'latitude'            => 'required|numeric|between:-15,10',
'longitude'           => 'required|numeric|between:90,150',
'photo_path'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
```

**`TransisiStatusInsidenRequest`:**
```php
'status_terbaru'      => 'required|in:terverifikasi,respon,pemulihan,selesai,dibatalkan',
'alasan'              => 'required_if:status_terbaru,dibatalkan|nullable|string',
```

### 9. Blade yang Diperlukan
```
resources/views/publik/laporan/create.blade.php       ← form laporan publik (tanpa auth)
resources/views/operasi/insiden/index.blade.php
resources/views/operasi/insiden/show.blade.php        ← tab-based: Info|Assessment|Sitrep|Klaster|Logistik|Personel|Jurnal
resources/views/operasi/insiden/create.blade.php
resources/views/operasi/insiden/edit.blade.php
resources/views/operasi/laporan/index.blade.php       ← daftar laporan menunggu validasi
resources/views/operasi/laporan/show.blade.php
```

**Catatan UI:**
- Badge status: `draft`=abu-abu, `terverifikasi`=biru, `respon`=oranye, `pemulihan`=kuning, `selesai`=hijau, `dibatalkan`=merah
- Jika `is_locked = 1`: tampilkan banner `⚠️ INSIDEN TERKUNCI — Semua data bersifat read-only`
- Tombol transisi status tampil kondisional berdasarkan `$user->can('transisiStatus', $insiden)`

### 10. Route yang Diperlukan
```php
// Publik (tanpa auth)
Route::get('/laporan/buat', [LaporanKejadianController::class, 'create'])->name('publik.laporan.create');
Route::post('/laporan', [LaporanKejadianController::class, 'store'])->name('publik.laporan.store');

// Internal (auth required)
Route::middleware(['auth', 'check.akun.aktif'])->group(function () {

    Route::prefix('insiden')->name('insiden.')->group(function () {
        Route::get('/', [InsidenController::class, 'index'])->name('index');
        Route::get('/buat', [InsidenController::class, 'create'])->name('create');
        Route::post('/', [InsidenController::class, 'store'])->name('store');
        Route::get('/{insiden}', [InsidenController::class, 'show'])->name('show');
        Route::get('/{insiden}/edit', [InsidenController::class, 'edit'])->name('edit');
        Route::put('/{insiden}', [InsidenController::class, 'update'])->name('update');
        Route::delete('/{insiden}', [InsidenController::class, 'destroy'])->name('destroy');
        Route::patch('/{insiden}/status', [InsidenStatusController::class, 'update'])->name('status.update');
    });

    Route::prefix('laporan-kejadian')->name('laporan.')->group(function () {
        Route::get('/', [LaporanKejadianController::class, 'indexInternal'])->name('index');
        Route::get('/{laporan}', [LaporanKejadianController::class, 'show'])->name('show');
        Route::patch('/{laporan}/validasi', [LaporanKejadianController::class, 'validasi'])->name('validasi');
    });
});
```

### 11. API yang Diperlukan
```
GET  /api/insiden                    → list insiden aktif (untuk command center)
GET  /api/insiden/{id}               → detail insiden (JSON)
GET  /api/insiden/{id}/riwayat-status → histori transisi status
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Insiden/InsidenCrudTest.php
  ✓ pcnu_dapat_membuat_insiden_di_scope_sendiri
  ✓ pcnu_tidak_dapat_membuat_insiden_di_scope_lain
  ✓ kode_kejadian_unik_tidak_duplikat
  ✓ waktu_selesai_sebelum_waktu_mulai_ditolak

tests/Feature/Insiden/InsidenScopeTest.php
  ✓ pcnu_hanya_melihat_insiden_scope_sendiri
  ✓ pwnu_melihat_semua_insiden_lintas_pcnu
  ✓ relawan_hanya_melihat_insiden_yang_ditugaskan

tests/Feature/Insiden/InsidenStatusTest.php
  ✓ transisi_draft_ke_terverifikasi_berhasil
  ✓ transisi_terverifikasi_ke_respon_berhasil
  ✓ transisi_selesai_ke_apapun_ditolak
  ✓ transisi_dicatat_ke_riwayat_status_insiden
  ✓ transisi_dicatat_ke_operasi_jurnal
  ✓ insiden_terkunci_setelah_status_selesai
  ✓ is_locked_mencegah_update_insiden

tests/Feature/Laporan/LaporanKejadianTest.php
  ✓ publik_dapat_mengirim_laporan_tanpa_login
  ✓ koordinat_di_luar_indonesia_ditolak
  ✓ laporan_default_status_menunggu
  ✓ pcnu_dapat_validasi_laporan
```

### 13. Definition of Done
- [ ] Migration `laporan_kejadian`, `operasi_insiden`, `riwayat_status_insiden` berhasil
- [ ] `kode_kejadian` di-generate otomatis format `INS-YYYYMM-{seq}` saat create
- [ ] `InsidenService::transisiStatus()` berjalan dalam `DB::transaction()`
- [ ] Setiap transisi status tercatat di `riwayat_status_insiden` dan `operasi_jurnal`
- [ ] Scope wilayah PCNU berfungsi di semua list query
- [ ] `is_locked = 1` setelah status `selesai`, banner terkunci tampil di UI
- [ ] Form laporan publik dapat diakses tanpa login
- [ ] Semua feature test passing

---

## M05 — Assessment

### 1. Nama Modul
`Assessment`

### 2. Tujuan Modul
Membangun modul kajian lapangan (assessment) yang terhubung ke insiden. Mencakup kaji cepat awal dan pendataan lanjutan, dengan manajemen `is_latest` otomatis melalui trigger database.

### 3. Dependensi Modul
- **M04** (Insiden) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `assessment_utama` | Data kajian utama per insiden |
| `assessment_dampak_manusia` | Korban: meninggal, hilang, menderita_mengungsi |
| `assessment_kebutuhan_mendesak` | Kebutuhan: pangan, medis |

**Kolom kritis:**
- `waktu_assesment` — **perhatikan typo ini dari SQL, IKUTI SQL, jangan koreksi**
- `is_latest` TINYINT(1) — dikelola trigger `tr_single_latest_assessment`
- `jenis_laporan` ENUM(`kaji_cepat`, `pendataan_lanjutan`)
- `dihapus_pada` — soft delete

**Trigger yang harus diketahui:**
- `tr_single_latest_assessment`: setiap INSERT dengan `is_latest = 1` otomatis set `is_latest = 0` untuk semua assessment lain dalam insiden yang sama

### 5. Model Laravel yang Diperlukan
```
app/Models/AssessmentUtama.php
app/Models/AssessmentDampakManusia.php
app/Models/AssessmentKebutuhanMendesak.php
```

**`AssessmentUtama`:**
```php
protected $table = 'assessment_utama';
protected $primaryKey = 'id_assessment_utama';
const CREATED_AT = 'dibuat_pada';
const UPDATED_AT = null;
const DELETED_AT = 'dihapus_pada';
use SoftDeletes;

// PERHATIAN: nama kolom waktu_assesment (typo dari SQL, IKUTI SQL)
protected $fillable = ['id_insiden', 'id_laporan_valid', 'id_petugas_assessment',
    'jenis_laporan', 'nomor_sitrep', 'waktu_assesment', 'label_perkembangan',
    'is_latest', 'latitude_titik_kaji', 'longitude_titik_kaji',
    'kondisi_umum', 'upaya_penanganan', 'kendala_lapangan'];

public function insiden(): BelongsTo        // → operasi_insiden
public function dampakManusia(): HasOne     // → assessment_dampak_manusia
public function kebutuhanMendesak(): HasOne // → assessment_kebutuhan_mendesak
```

### 6. Policy yang Diperlukan
```
app/Policies/AssessmentPolicy.php
```
- `create`: `id_peran` ∈ [1,2,3] atau relawan yang ditugaskan sebagai TRC pada insiden tersebut
- `view`: semua role internal yang memiliki akses ke insiden terkait
- `delete`: [1,2] atau [3] dalam scope (soft delete)
- Catatan: assessment yang menjadi basis sitrep (`id_assessment_basis` di `operasi_sitrep`) tidak dapat dihapus

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Operasi/AssessmentController.php
```
Routes nested di bawah insiden: `/insiden/{insiden}/assessment`
Methods: `index`, `create`, `store`, `show`, `destroy`

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Operasi/StoreAssessmentRequest.php
```
```php
'id_petugas_assessment'  => 'required|exists:auth_users,id_pengguna',
'jenis_laporan'          => 'required|in:kaji_cepat,pendataan_lanjutan',
'waktu_assesment'        => 'required|date',  // perhatikan typo, ikuti SQL
'label_perkembangan'     => 'nullable|string|max:100',
'latitude_titik_kaji'    => 'nullable|numeric|between:-15,10',
'longitude_titik_kaji'   => 'nullable|numeric|between:90,150',
'kondisi_umum'           => 'nullable|string',
'upaya_penanganan'       => 'nullable|string',
'kendala_lapangan'       => 'nullable|string',
// Nested: dampak manusia
'meninggal'              => 'nullable|integer|min:0',
'hilang'                 => 'nullable|integer|min:0',
'menderita_mengungsi'    => 'nullable|integer|min:0',
// Nested: kebutuhan mendesak
'kebutuhan_pangan'       => 'nullable|string',
'kebutuhan_medis'        => 'nullable|string',
```

### 9. Blade yang Diperlukan
```
resources/views/operasi/assessment/index.blade.php    ← list assessment per insiden
resources/views/operasi/assessment/create.blade.php   ← form dengan section dampak & kebutuhan
resources/views/operasi/assessment/show.blade.php     ← detail assessment dengan badge is_latest
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('insiden/{insiden}')->name('insiden.')->group(function () {
    Route::resource('assessment', AssessmentController::class)
         ->only(['index', 'create', 'store', 'show', 'destroy']);
});
```

### 11. API yang Diperlukan
```
GET /api/insiden/{id}/assessment/latest   → assessment terbaru (is_latest=1) untuk sitrep
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Assessment/AssessmentTest.php
  ✓ assessment_baru_set_is_latest_dan_reset_sebelumnya
  ✓ assessment_tersimpan_beserta_dampak_dan_kebutuhan
  ✓ assessment_tidak_dapat_dibuat_untuk_insiden_draft
  ✓ assessment_tidak_dapat_dibuat_untuk_insiden_selesai
  ✓ koordinat_di_luar_indonesia_ditolak
  ✓ soft_delete_assessment_berhasil
  ✓ assessment_dengan_sitrep_tidak_dapat_dihapus
  ✓ relawan_trc_dapat_membuat_assessment
  ✓ relawan_tidak_ditugaskan_tidak_dapat_membuat_assessment
```

### 13. Definition of Done
- [ ] Migration ketiga tabel assessment berhasil
- [ ] Trigger `tr_single_latest_assessment` berfungsi (diverifikasi lewat DB test)
- [ ] Store assessment menyimpan `assessment_utama` + `assessment_dampak_manusia` + `assessment_kebutuhan_mendesak` dalam `DB::transaction()`
- [ ] Validasi koordinat berfungsi di FormRequest level (backup dari trigger DB)
- [ ] Badge `is_latest` tampil benar di UI
- [ ] Semua feature test passing

---

## M06 — Sitrep

### 1. Nama Modul
`Sitrep (Laporan Situasi)`

### 2. Tujuan Modul
Membangun modul laporan situasi resmi yang terhubung ke insiden dan assessment. Mengelola siklus hidup sitrep dari draft hingga final dengan snapshot otomatis dan hash integrity.

### 3. Dependensi Modul
- **M04** (Insiden), **M05** (Assessment) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `operasi_sitrep` | Laporan situasi per insiden |

**Kolom kritis:**
- `nomor_sitrep` INT — UNIQUE per `id_insiden`, generate otomatis (MAX+1)
- `status_sitrep` ENUM(`draft`,`ditinjau`,`final`)
- `snapshot_dampak` JSON — auto-populate trigger `tr_auto_snapshot_sitrep`
- `hash_snapshot` VARCHAR(64) — SHA-256 dari JSON snapshot
- `id_penfinalisasi` — wajib diisi saat finalisasi
- `waktu_difinalisasi` — wajib diisi saat finalisasi
- `file_pdf_path` — path PDF yang di-generate saat finalisasi
- `dihapus_pada` — soft delete

**Trigger yang harus diketahui:**
- `tr_auto_snapshot_sitrep`: saat INSERT, otomatis populate `snapshot_dampak` dari `assessment_dampak_manusia`
- `tr_auto_snapshot_sitrep_update`: saat UPDATE (kecuali status sudah `final`), update snapshot
- Setelah `final`: snapshot IMMUTABLE, trigger tidak jalan

### 5. Model Laravel yang Diperlukan
```
app/Models/OperasiSitrep.php
```
```php
protected $table = 'operasi_sitrep';
protected $primaryKey = 'id_sitrep';
const CREATED_AT = 'dibuat_pada';
const UPDATED_AT = null;
const DELETED_AT = 'dihapus_pada';
use SoftDeletes;

protected $casts = [
    'snapshot_dampak'    => 'array',
    'snapshot_logistik'  => 'array',
    'snapshot_operasi'   => 'array',
    'snapshot_aktivitas' => 'array',
    'waktu_difinalisasi' => 'datetime',
    'waktu_pelaporan'    => 'datetime',
];

public function insiden(): BelongsTo     // → operasi_insiden
public function petugas(): BelongsTo     // → auth_users via id_petugas
public function penfinalisasi(): BelongsTo  // → auth_users via id_penfinalisasi
public function assessmentBasis(): BelongsTo // → assessment_utama via id_assessment_basis
```

### 6. Policy yang Diperlukan
```
app/Policies/SitrepPolicy.php
```
- `create`: `id_peran` ∈ [1,2,3] atau relawan dengan assignment aktif di insiden tersebut
- `finalisasi`: Gate `finalize-sitrep` ([1,2,3]) dan `cekScopeWilayah()`
- `view`: semua role internal yang punya akses insiden
- `delete`: [1,2] saja, hanya sitrep `draft`

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Operasi/SitrepController.php
app/Http/Controllers/Operasi/SitrepFinalisasiController.php
```

**Service wajib:**
```
app/Services/SitrepService.php
  → finalisasi(OperasiSitrep $sitrep, AuthUser $aktor): void
     1. Validasi status 'ditinjau' (tidak boleh dari 'draft')
     2. Update: status_sitrep='final', waktu_difinalisasi=now(), id_penfinalisasi
     3. Generate PDF via SitrepPdfService
     4. Update file_pdf_path
     5. Catat operasi_jurnal
     Semua dalam DB::transaction()

app/Services/SitrepPdfService.php
  → generate(OperasiSitrep $sitrep): string  ← return path file
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Operasi/StoreSitrepRequest.php
app/Http/Requests/Operasi/UpdateSitrepRequest.php
app/Http/Requests/Operasi/FinalisasiSitrepRequest.php
```

```php
// StoreSitrepRequest
'id_petugas'          => 'required|exists:auth_users,id_pengguna',
'id_assessment_basis' => 'nullable|exists:assessment_utama,id_assessment_utama',
'waktu_pelaporan'     => 'required|date',
'kondisi_umum'        => 'nullable|string',
'upaya_penanganan'    => 'nullable|string',
'kendala_lapangan'    => 'nullable|string',
'kebutuhan_mendesak'  => 'nullable|string',
// nomor_sitrep di-generate otomatis di Controller/Service
```

### 9. Blade yang Diperlukan
```
resources/views/operasi/sitrep/index.blade.php    ← list, urut nomor_sitrep DESC
resources/views/operasi/sitrep/create.blade.php
resources/views/operasi/sitrep/show.blade.php     ← readonly jika final, tampilkan hash
resources/views/operasi/sitrep/edit.blade.php     ← hanya untuk status draft/ditinjau
resources/views/pdf/sitrep.blade.php              ← template PDF (khusus dompdf)
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('insiden/{insiden}')->name('insiden.')->group(function () {
    Route::resource('sitrep', SitrepController::class)
         ->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    Route::patch('/sitrep/{sitrep}/tinjau', [SitrepController::class, 'tinjau'])->name('sitrep.tinjau');
    Route::patch('/sitrep/{sitrep}/finalisasi', [SitrepFinalisasiController::class, 'store'])->name('sitrep.finalisasi');
    Route::get('/sitrep/{sitrep}/pdf', [SitrepController::class, 'downloadPdf'])->name('sitrep.pdf');
});
```

### 11. API yang Diperlukan
```
GET /api/insiden/{id}/sitrep/latest   → sitrep final terbaru untuk display publik
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Sitrep/SitrepTest.php
  ✓ nomor_sitrep_generate_otomatis_berurutan
  ✓ nomor_sitrep_unik_per_insiden
  ✓ sitrep_draft_dapat_diedit
  ✓ sitrep_final_tidak_dapat_diedit
  ✓ sitrep_draft_tidak_dapat_langsung_final
  ✓ finalisasi_mengisi_waktu_difinalisasi_dan_id_penfinalisasi
  ✓ hash_snapshot_tidak_null_setelah_finalisasi
  ✓ pdf_terbuat_setelah_finalisasi
  ✓ relawan_tidak_ditugaskan_tidak_dapat_buat_sitrep
```

### 13. Definition of Done
- [ ] Migration `operasi_sitrep` berhasil
- [ ] `nomor_sitrep` di-generate otomatis (SELECT MAX + 1 per insiden)
- [ ] Trigger snapshot berfungsi (verifikasi: setelah INSERT, `snapshot_dampak` tidak null)
- [ ] `SitrepService::finalisasi()` berjalan dalam `DB::transaction()`
- [ ] PDF di-generate dan path tersimpan di `file_pdf_path`
- [ ] Sitrep FINAL: semua form field disabled di UI, banner "FINAL" tampil
- [ ] Tombol Download PDF tampil hanya jika `status_sitrep = 'final'`
- [ ] Semua feature test passing

---

---

# FASE 3 — GOVERNANCE

---

## M07 — Pleno & Eskalasi

### 1. Nama Modul
`Pleno & Eskalasi`

### 2. Tujuan Modul
Membangun modul rapat pengambilan keputusan (pleno) sebagai entitas governance utama. Pleno menghasilkan keputusan yang menjadi dasar eskalasi, penunjukan komandan, pembukaan pos aju, dan periode operasional.

### 3. Dependensi Modul
- **M04** (Insiden) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `operasi_pleno` | Rapat pleno utama |
| `operasi_pleno_keputusan` | Keputusan yang dihasilkan pleno |
| `operasi_pleno_peserta` | Peserta, hak suara, status persetujuan |
| `operasi_eskalasi` | Eskalasi level insiden via pleno |
| `operasi_aktivasi` | Aktivasi operasi tanggap darurat |

**Enum penting:**
- `operasi_pleno_peserta.status_kehadiran`: `hadir`, `izin`, `tanpa_keterangan`
- `operasi_pleno_peserta.status_persetujuan`: `setuju`, `tolak`, `abstain`
- `operasi_eskalasi.level_sebelumnya/level_baru`: `lokal`, `pcnu`, `pwnu`, `nasional`
- `operasi_aktivasi.status_darurat`: `siaga`, `tanggap_darurat`, `pemulihan`, `selesai`

### 5. Model Laravel yang Diperlukan
```
app/Models/OperasiPleno.php
app/Models/OperasiPlanoKeputusan.php
app/Models/OperasiPlenoPeserta.php
app/Models/OperasiEskalasi.php
app/Models/OperasiAktivasi.php
```

**`OperasiPleno` relasi:**
```php
public function insiden(): BelongsTo
public function keputusan(): HasMany   // → operasi_pleno_keputusan
public function peserta(): HasMany     // → operasi_pleno_peserta
public function eskalasi(): HasOne     // → operasi_eskalasi via id_pleno
```

### 6. Policy yang Diperlukan
```
app/Policies/PlanoPolicy.php
app/Policies/EskalasiPolicy.php
```
- `PlanoPolicy::create`: [1,2,3] dalam scope insiden
- `PlanoPolicy::finalisasi`: Gate `finalize-pleno` ([1,2] saja)
- `PlanoPolicy::tambahPeserta`: [1,2] atau [3] dalam scope
- `EskalasiPolicy::create`: Gate `escalate-insiden` ([1,2] saja)

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Governance/PlanoController.php
app/Http/Controllers/Governance/PlanoKeputusanController.php
app/Http/Controllers/Governance/PlanoPesertaController.php
app/Http/Controllers/Governance/EskalasiController.php
app/Http/Controllers/Governance/AktivasiController.php
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Governance/StorePlanoRequest.php
app/Http/Requests/Governance/StoreKeputusanRequest.php
app/Http/Requests/Governance/UpdatePesertaRequest.php
app/Http/Requests/Governance/StoreEskalasiRequest.php
```

```php
// StoreEskalasiRequest
'id_pleno'           => 'required|exists:operasi_pleno,id_pleno',
'level_sebelumnya'   => 'required|in:lokal,pcnu,pwnu,nasional',
'level_baru'         => 'required|in:lokal,pcnu,pwnu,nasional',
'alasan_eskalasi'    => 'required|string|min:10',
// Validasi level_baru > level_sebelumnya dilakukan di Service
```

### 9. Blade yang Diperlukan
```
resources/views/governance/pleno/index.blade.php
resources/views/governance/pleno/show.blade.php     ← tab: Info|Keputusan|Peserta
resources/views/governance/pleno/create.blade.php
resources/views/governance/pleno/peserta.blade.php
resources/views/governance/eskalasi/create.blade.php
resources/views/governance/aktivasi/create.blade.php
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('insiden/{insiden}')->name('insiden.')->group(function () {
    Route::resource('pleno', PlanoController::class)->only(['index','create','store','show']);
    Route::patch('/pleno/{pleno}/finalisasi', [PlanoController::class, 'finalisasi'])->name('pleno.finalisasi');
    Route::post('/pleno/{pleno}/keputusan', [PlanoKeputusanController::class, 'store'])->name('pleno.keputusan.store');
    Route::post('/pleno/{pleno}/peserta', [PlanoPesertaController::class, 'store'])->name('pleno.peserta.store');
    Route::patch('/pleno/{pleno}/peserta/{peserta}/vote', [PlanoPesertaController::class, 'vote'])->name('pleno.peserta.vote');
    Route::post('/eskalasi', [EskalasiController::class, 'store'])->name('eskalasi.store');
    Route::post('/aktivasi', [AktivasiController::class, 'store'])->name('aktivasi.store');
});
```

### 11. API yang Diperlukan
```
GET /api/insiden/{id}/pleno          → list pleno per insiden
GET /api/insiden/{id}/pleno/{pleno}  → detail pleno + keputusan
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Governance/PlanoTest.php
  ✓ pcnu_dapat_membuat_pleno_dalam_scope
  ✓ pcnu_tidak_dapat_membuat_pleno_luar_scope
  ✓ pleno_final_tidak_dapat_diubah
  ✓ pleno_final_tidak_dapat_tambah_keputusan
  ✓ peserta_setuju_dan_tolak_tercatat
  ✓ peserta_tolak_wajib_isi_catatan
  ✓ finalisasi_pleno_hanya_pwnu

tests/Feature/Governance/EskalasiTest.php
  ✓ pcnu_tidak_dapat_eskalasi_mandiri
  ✓ pwnu_dapat_eskalasi_insiden_pcnu
  ✓ eskalasi_tanpa_pleno_ditolak
  ✓ eskalasi_level_turun_ditolak
  ✓ eskalasi_tercatat_di_operasi_jurnal
```

### 13. Definition of Done
- [ ] Migration semua tabel pleno, eskalasi, aktivasi berhasil
- [ ] Pleno FINAL: tidak dapat tambah keputusan, tidak dapat ubah peserta
- [ ] Voting peserta tersimpan dengan benar
- [ ] Eskalasi: level naik, wajib `id_pleno`, hanya pwnu
- [ ] Eskalasi dicatat ke `operasi_jurnal`
- [ ] Semua feature test passing

---

## M08 — Surat Menyurat

### 1. Nama Modul
`Surat Menyurat`

### 2. Tujuan Modul
Membangun sistem surat resmi sebagai legal governance entity. Surat memiliki alur paraf berurutan, nomor otomatis, generate PDF, dan bersifat immutable setelah finalisasi. Surat bukan sekadar upload file.

### 3. Dependensi Modul
- **M07** (Pleno) — wajib selesai
- **M03** (Master Data: `master_surat_jenis`, `master_jabatan_penandatangan`) — wajib ada data

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `operasi_surat_keluar` | Surat resmi utama |
| `dokumen_surat_paraf` | Daftar paraf berurutan |
| `dokumen_surat_tembusan` | Daftar penerima tembusan |
| `master_surat_jenis` | Jenis surat + format nomor |
| `master_surat_template` | Template isi surat |
| `master_jabatan_penandatangan` | Jabatan yang berwenang tanda tangan |

**Alur paraf:**
- `dokumen_surat_paraf.urutan` INT — paraf diproses dari urutan 1, 2, 3, ...
- `status_paraf` ENUM(`menunggu`, `disetujui`, `ditolak`)
- Jika satu paraf `ditolak` → surat kembali ke status DRAFT, semua paraf setelahnya di-reset ke `menunggu`

### 5. Model Laravel yang Diperlukan
```
app/Models/DokumenSuratUtama.php
app/Models/DokumenSuratParaf.php
app/Models/DokumenSuratTembusan.php
app/Models/MasterSuratJenis.php
app/Models/MasterSuratTemplate.php
app/Models/MasterJabatanPenandatangan.php
```

**`DokumenSuratUtama` relasi:**
```php
public function jenisurat(): BelongsTo   // → master_surat_jenis
public function paraf(): HasMany         // → dokumen_surat_paraf (ordered by urutan ASC)
public function tembusan(): HasMany      // → dokumen_surat_tembusan
public function parafAktif(): HasOne     // → paraf dengan urutan terendah yang masih menunggu
```

### 6. Policy yang Diperlukan
```
app/Policies/SuratPolicy.php
```
- `create`: [1,2,3] yang memiliki jabatan sesuai `master_jabatan_penandatangan`
- `paraf`: pengguna yang `id_pengguna` sesuai dengan paraf urutan aktif
- `finalisasi`: [1,2] saja, setelah semua paraf `disetujui`
- `view`: [1,2,3] dalam scope
- `update`: [1,2,3] hanya jika status `draft`

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Governance/SuratController.php
app/Http/Controllers/Governance/SuratParafController.php
```

**Service wajib:**
```
app/Services/SuratService.php
  → prosesParaf(DokumenSuratParaf $paraf, string $status, ?string $catatan, AuthUser $aktor): void
     Jika disetujui + ada paraf berikutnya → aktifkan paraf berikutnya
     Jika disetujui + tidak ada paraf lagi → ubah status surat ke 'approved'
     Jika ditolak → ubah status surat ke 'draft', reset semua paraf setelahnya ke 'menunggu'
     Semua dalam DB::transaction()

app/Services/NomorSuratService.php
  → generate(MasterSuratJenis $jenis, int $tahun): string
     Parsing format_nomor dari master_surat_jenis, isi sequence, tahun

app/Services/SuratPdfService.php
  → generate(DokumenSuratUtama $surat): string  ← return path file
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Governance/StoreSuratRequest.php
app/Http/Requests/Governance/UpdateSuratRequest.php
app/Http/Requests/Governance/ParafSuratRequest.php
```

```php
// ParafSuratRequest
'status_paraf'  => 'required|in:disetujui,ditolak',
'catatan'       => 'required_if:status_paraf,ditolak|nullable|string',
```

### 9. Blade yang Diperlukan
```
resources/views/governance/surat/index.blade.php
resources/views/governance/surat/create.blade.php
resources/views/governance/surat/show.blade.php     ← preview mirip surat resmi, readonly jika final
resources/views/governance/surat/edit.blade.php     ← hanya jika status draft
resources/views/governance/surat/paraf.blade.php    ← form paraf untuk pengguna yang berwenang
resources/views/pdf/surat.blade.php                 ← template PDF dompdf
```

**UI Rules:**
- Surat FINALIZED: tampilkan watermark "FINAL", semua form disabled
- Tombol Generate PDF hanya tampil jika `status = 'finalized'`
- Timeline paraf: tampilkan siapa paraf, kapan, status (disetujui/ditolak/menunggu)

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('surat')->name('surat.')->group(function () {
    Route::get('/', [SuratController::class, 'index'])->name('index');
    Route::get('/buat', [SuratController::class, 'create'])->name('create');
    Route::post('/', [SuratController::class, 'store'])->name('store');
    Route::get('/{surat}', [SuratController::class, 'show'])->name('show');
    Route::get('/{surat}/edit', [SuratController::class, 'edit'])->name('edit');
    Route::put('/{surat}', [SuratController::class, 'update'])->name('update');
    Route::get('/{surat}/pdf', [SuratController::class, 'downloadPdf'])->name('pdf');
    Route::patch('/{surat}/finalisasi', [SuratController::class, 'finalisasi'])->name('finalisasi');
    Route::patch('/{surat}/paraf/{paraf}', [SuratParafController::class, 'update'])->name('paraf.update');
});
```

### 11. API yang Diperlukan
```
GET /api/surat/{id}/status-paraf   → status paraf saat ini (untuk AJAX polling)
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Governance/SuratTest.php
  ✓ nomor_surat_tergenerate_otomatis_unik
  ✓ paraf_berurutan_tidak_bisa_lompat
  ✓ paraf_disetujui_aktifkan_paraf_berikutnya
  ✓ semua_paraf_disetujui_surat_jadi_approved
  ✓ paraf_ditolak_surat_kembali_draft
  ✓ paraf_ditolak_tanpa_catatan_ditolak_validasi
  ✓ surat_finalized_tidak_dapat_diedit
  ✓ pdf_terbuat_setelah_finalisasi
  ✓ hanya_pcnu_atau_pwnu_yang_berwenang_paraf
```

### 13. Definition of Done
- [ ] Migration semua tabel surat berhasil
- [ ] `NomorSuratService` generate nomor unik berdasarkan `format_nomor` jenis surat
- [ ] Alur paraf berurutan berjalan benar dengan reset saat ditolak
- [ ] `SuratService::prosesParaf()` berjalan dalam `DB::transaction()`
- [ ] PDF di-generate oleh dompdf dan path tersimpan
- [ ] Surat FINALIZED: UI readonly, watermark tampil
- [ ] Semua feature test passing

---

---

# FASE 4 — ASSIGNMENT DAN PERSONEL

---

## M09 — Assignment & Otoritas Kontekstual

### 1. Nama Modul
`Assignment & Otoritas Kontekstual`

### 2. Tujuan Modul
Membangun sistem penugasan personel ke insiden. Assignment memberikan otoritas sementara (incident-based) kepada pengguna selama operasi berlangsung. Ini adalah dasar untuk mobilisasi, sitrep relawan, dan akses operasional.

### 3. Dependensi Modul
- **M04** (Insiden), **M07** (Pleno) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `operasi_penugasan` | Penugasan personel + peran otoritas |
| `operasi_otoritas_kontekstual` | Otoritas role operasional per insiden |

**Enum penting:**
- `peran_otoritas`: `komandan_insiden`, `trc`, `relawan`, `medis`, `logistik`, `operator`
- `status_penugasan`: `aktif` (default)
- `dihapus_pada` — soft delete

### 5. Model Laravel yang Diperlukan
```
app/Models/OperasiPenugasan.php
app/Models/OperasiOtoritasKontekstual.php
```

**`OperasiPenugasan`:**
```php
protected $table = 'operasi_penugasan';
protected $primaryKey = 'id_incident_assignment';  // kolom PK dari SQL
const CREATED_AT = 'dibuat_pada';
const UPDATED_AT = 'diperbarui_pada';
const DELETED_AT = 'dihapus_pada';
use SoftDeletes;

// Scope: penugasan aktif (waktu_selesai IS NULL)
public function scopeAktif($query): Builder
public function insiden(): BelongsTo
public function pengguna(): BelongsTo
public function penugasOleh(): BelongsTo  // → auth_users via ditugaskan_oleh
```

### 6. Policy yang Diperlukan
```
app/Policies/PenugasanPolicy.php
```
- `create`: [1,2,3] dalam scope insiden
- `update`: [1,2] atau komandan_insiden yang sedang aktif
- `delete`: [1,2] atau [3] dalam scope (soft delete)

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Operasi/PenugasanController.php
```
Methods: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `akhiri`

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Operasi/StorePenugasanRequest.php
app/Http/Requests/Operasi/AkhiriPenugasanRequest.php
```

```php
// StorePenugasanRequest
'id_pengguna'      => 'required|exists:auth_users,id_pengguna',
'peran_otoritas'   => 'required|in:komandan_insiden,trc,relawan,medis,logistik,operator',
'waktu_mulai'      => 'required|date',
'waktu_selesai'    => 'nullable|date|after:waktu_mulai',
'catatan'          => 'nullable|string',
'asal_lingkup'     => 'nullable|string|max:100',
'tujuan_lingkup'   => 'nullable|string|max:100',
```

### 9. Blade yang Diperlukan
```
resources/views/operasi/penugasan/index.blade.php    ← list penugasan aktif per insiden
resources/views/operasi/penugasan/create.blade.php
resources/views/operasi/penugasan/show.blade.php
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('insiden/{insiden}/penugasan')->name('insiden.penugasan.')->group(function () {
    Route::get('/', [PenugasanController::class, 'index'])->name('index');
    Route::get('/buat', [PenugasanController::class, 'create'])->name('create');
    Route::post('/', [PenugasanController::class, 'store'])->name('store');
    Route::get('/{penugasan}', [PenugasanController::class, 'show'])->name('show');
    Route::patch('/{penugasan}/akhiri', [PenugasanController::class, 'akhiri'])->name('akhiri');
    Route::delete('/{penugasan}', [PenugasanController::class, 'destroy'])->name('destroy');
});
```

### 11. API yang Diperlukan
```
GET /api/insiden/{id}/penugasan/aktif   → daftar personel aktif di insiden (untuk command center)
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Operasi/PenugasanTest.php
  ✓ pcnu_dapat_menugaskan_relawan_dalam_scope
  ✓ cross_region_assignment_tercatat_asal_tujuan_lingkup
  ✓ organisasi_asal_relawan_tidak_berubah_setelah_assignment
  ✓ penugasan_berakhir_saat_waktu_selesai_diisi
  ✓ relawan_yang_ditugaskan_dapat_buat_sitrep
  ✓ relawan_yang_tidak_ditugaskan_tidak_dapat_buat_sitrep
  ✓ pcnu_tidak_dapat_tugaskan_relawan_di_insiden_lain
```

### 13. Definition of Done
- [ ] Migration `operasi_penugasan`, `operasi_otoritas_kontekstual` berhasil
- [ ] Cross-region: `asal_lingkup` dan `tujuan_lingkup` tersimpan, `auth_users.id_unit` tidak berubah
- [ ] `hasAssignment()` di `BaseNuriskPolicy` menggunakan tabel ini dengan benar
- [ ] Penugasan aktif tampil di dashboard insiden
- [ ] Semua feature test passing

---

## M10 — Mobilisasi Personel & Klaster

### 1. Nama Modul
`Mobilisasi Personel & Klaster`

### 2. Tujuan Modul
Membangun manajemen perpindahan fisik personel ke lapangan dan pengelolaan klaster operasi (Komando, Logistik, SAR, Kesehatan, Dapur Umum, Psikososial).

### 3. Dependensi Modul
- **M09** (Assignment) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `operasi_mobilisasi_personil` | Status fisik personel di lapangan |
| `operasi_klaster` | Klaster operasi per insiden |
| `operasi_klaster_koordinator` | Koordinator per klaster (via pleno) |
| `operasi_master_klaster` | 6 klaster master (sudah di-seed) |
| `operasi_master_indikator` | Indikator keberhasilan per klaster |

**Enum:**
- `operasi_mobilisasi_personil.status_kehadiran`: `menuju_lokasi`, `di_lokasi`, `kembali`, `izin`
- `operasi_klaster.status_klaster`: `nonaktif`, `aktif`, `selesai`
- `operasi_klaster.prioritas`: `rendah`, `sedang`, `tinggi`, `kritis`

### 5. Model Laravel yang Diperlukan
```
app/Models/OperasiMobilisasiPersonil.php
app/Models/OperasiKlaster.php
app/Models/OperasiKlasterKoordinator.php
```

### 6. Policy yang Diperlukan
```
app/Policies/MobilisasiPolicy.php
app/Policies/KlasterPolicy.php
```

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Operasi/MobilisasiController.php
app/Http/Controllers/Operasi/KlasterController.php
app/Http/Controllers/Operasi/KlasterKoordinatorController.php
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Operasi/StoreMobilisasiRequest.php
app/Http/Requests/Operasi/UpdateStatusKehadiranRequest.php
app/Http/Requests/Operasi/StoreKlasterRequest.php
```

### 9. Blade yang Diperlukan
```
resources/views/operasi/klaster/index.blade.php      ← 6 klaster, progress per klaster
resources/views/operasi/klaster/show.blade.php
resources/views/operasi/mobilisasi/index.blade.php   ← daftar personel + status kehadiran
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('insiden/{insiden}')->name('insiden.')->group(function () {
    Route::resource('klaster', KlasterController::class)->only(['index','show','store','update']);
    Route::post('/klaster/{klaster}/koordinator', [KlasterKoordinatorController::class, 'store'])->name('klaster.koordinator.store');
    Route::resource('mobilisasi', MobilisasiController::class)->only(['index','store']);
    Route::patch('/mobilisasi/{mobilisasi}/status', [MobilisasiController::class, 'updateStatus'])->name('mobilisasi.status');
});
```

### 11. API yang Diperlukan
```
GET /api/insiden/{id}/personel-aktif   → personel dengan status_kehadiran=di_lokasi
GET /api/insiden/{id}/klaster          → klaster aktif + progres
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Operasi/KlasterTest.php
  ✓ 6_klaster_default_tersedia_di_insiden_aktif
  ✓ koordinator_klaster_wajib_via_pleno
  ✓ klaster_selesai_tidak_dapat_aktif_kembali

tests/Feature/Operasi/MobilisasiTest.php
  ✓ status_kehadiran_dapat_diupdate
  ✓ personel_aktif_tampil_di_api
```

### 13. Definition of Done
- [ ] Migration semua tabel klaster dan mobilisasi berhasil
- [ ] 6 klaster master tersedia (dari seeder M03)
- [ ] Status kehadiran dapat diupdate oleh personel sendiri atau komandan
- [ ] `progres_persen` dapat diupdate oleh koordinator klaster
- [ ] Semua feature test passing

---

## M11 — Shift / Periode Operasional

### 1. Nama Modul
`Shift / Periode Operasional`

### 2. Tujuan Modul
Membangun manajemen periode operasional yang dibuat berdasarkan keputusan pleno. Periode adalah pembagian waktu operasi (misal: Tanggap Darurat Tahap I, II, dst).

### 3. Dependensi Modul
- **M07** (Pleno), **M09** (Assignment) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `operasi_periode` | Periode/shift operasi per insiden |

**Aturan:**
- `id_pleno_keputusan` wajib — periode wajib berdasarkan keputusan pleno
- `status_periode`: `berjalan`, `selesai`, `diperpanjang`
- `tanggal_selesai` ≥ `tanggal_mulai` — validasi di FormRequest

### 5. Model Laravel yang Diperlukan
```
app/Models/OperasiPeriode.php
```
```php
protected $table = 'operasi_periode';
protected $primaryKey = 'id_periode_operasi';
public $timestamps = false;

public function insiden(): BelongsTo
public function planoKeputusan(): BelongsTo  // → operasi_pleno_keputusan via id_pleno_keputusan
```

### 6. Policy yang Diperlukan
```
app/Policies/PeriodePolicy.php
```
- `create`: [1,2,3] dalam scope, wajib ada `id_pleno_keputusan` yang valid
- `update`: [1,2] atau [3] dalam scope, hanya jika `status_periode != 'selesai'`

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Operasi/PeriodeController.php
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Operasi/StorePeriodeRequest.php
```
```php
'id_pleno_keputusan'  => 'required|exists:operasi_pleno_keputusan,id_keputusan',
'label_periode'       => 'required|string|max:100',
'tanggal_mulai'       => 'required|date',
'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
'keterangan_tujuan'   => 'nullable|string',
'status_periode'      => 'required|in:berjalan,selesai,diperpanjang',
```

### 9. Blade yang Diperlukan
```
resources/views/operasi/periode/index.blade.php
resources/views/operasi/periode/create.blade.php
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('insiden/{insiden}/periode')->name('insiden.periode.')->group(function () {
    Route::get('/', [PeriodeController::class, 'index'])->name('index');
    Route::get('/buat', [PeriodeController::class, 'create'])->name('create');
    Route::post('/', [PeriodeController::class, 'store'])->name('store');
    Route::patch('/{periode}/status', [PeriodeController::class, 'updateStatus'])->name('status');
});
```

### 11. API yang Diperlukan
Tidak ada API khusus.

### 12. Feature Test yang Diperlukan
```
tests/Feature/Operasi/PeriodeTest.php
  ✓ periode_wajib_ada_pleno_keputusan
  ✓ tanggal_selesai_tidak_boleh_sebelum_tanggal_mulai
  ✓ perpanjangan_periode_via_pleno_baru
  ✓ periode_selesai_tidak_dapat_diubah
```

### 13. Definition of Done
- [ ] Migration `operasi_periode` berhasil
- [ ] Validasi `id_pleno_keputusan` wajib ada di FormRequest
- [ ] Validasi tanggal berfungsi
- [ ] Semua feature test passing

---

---

# FASE 5 — LOGISTIK DAN ASET

---

## M12 — Logistik

### 1. Nama Modul
`Logistik`

### 2. Tujuan Modul
Membangun sistem manajemen logistik operasi: stok barang, mutasi (masuk/keluar/penyesuaian), gudang, permintaan logistik, dan perencanaan. Seluruh perubahan stok WAJIB melalui tabel `logistik_mutasi`.

### 3. Dependensi Modul
- **M04** (Insiden), **M09** (Assignment), **M03** (Master Satuan) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `logistik_gudang` | Gudang penyimpanan (scope PCNU/PWNU) |
| `logistik_barang_katalog` | Katalog standar barang |
| `logistik_kategori` | Kategori barang (sudah di-seed) |
| `master_satuan` | Satuan barang (sudah di-seed) |
| `logistik_stok` | Stok aktual per pos aju/gudang |
| `logistik_mutasi` | Log setiap perubahan stok — SATU-SATUNYA cara ubah stok |
| `logistik_perencanaan` | Perencanaan kebutuhan per insiden |
| `logistik_permintaan` | Permintaan barang dari pos aju ke gudang |

**⚠️ ATURAN KRITIS — WAJIB DIBACA:**
- **DILARANG** `LogistikStok::update(['jumlah_tersedia' => X])` secara langsung
- **WAJIB** INSERT ke `logistik_mutasi` → trigger `tr_execute_logistik_stok_update` yang update stok
- Trigger `tr_logistik_mutasi_integrity_guard`: `keluar > jumlah_tersedia` → SIGNAL error dari DB
- Trigger `tr_validate_stock_ownership`: gudang PCNU-A tidak bisa suplai insiden PCNU-B
- Trigger `tr_validate_logistik_request_scope`: posaju tujuan harus dalam insiden yang sama
- `uuid_mutasi` CHAR(36) wajib unik per transaksi

**Enum:**
- `tipe_mutasi`: `masuk`, `keluar`, `penyesuaian`
- `status_permintaan`: `draft`, `diajukan`, `disetujui`, `ditolak`, `dikirim`, `selesai`
- `prioritas` logistik_permintaan: `biasa`, `mendesak`, `darurat`
- `prioritas` logistik_perencanaan: `darurat`, `mendesak`, `normal`

### 5. Model Laravel yang Diperlukan
```
app/Models/LogistikGudang.php
app/Models/LogistikBarangKatalog.php
app/Models/LogistikStok.php
app/Models/LogistikMutasi.php
app/Models/LogistikPerencanaan.php
app/Models/LogistikPermintaan.php
```

**`LogistikMutasi`:**
```php
protected $table = 'logistik_mutasi';
protected $primaryKey = 'id_mutasi';
const CREATED_AT = 'waktu_mutasi';
const UPDATED_AT = null;

// Boot: auto-generate uuid_mutasi
protected static function boot() {
    parent::boot();
    static::creating(fn($m) => $m->uuid_mutasi = (string) Str::uuid());
}

public function stok(): BelongsTo     // → logistik_stok via id_stok
public function penginput(): BelongsTo  // → auth_users via id_penginput
```

**`LogistikStok`:**
```php
// JANGAN tambahkan mutator untuk jumlah_tersedia
// Nilai ini HANYA boleh berubah via trigger database setelah INSERT ke logistik_mutasi
```

### 6. Policy yang Diperlukan
```
app/Policies/LogistikGudangPolicy.php
app/Policies/LogistikMutasiPolicy.php
app/Policies/LogistikPermintaanPolicy.php
```
- `LogistikGudangPolicy::view`: hanya akses gudang sesuai scope (`id_pcnu`)
- `LogistikMutasiPolicy::create`: [1,2,3] dalam scope atau relawan dengan assignment `logistik`
- `LogistikPermintaanPolicy::approve`: [1,2,3] — koordinator logistik atau pwnu/pcnu

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Logistik/GudangController.php
app/Http/Controllers/Logistik/StokController.php
app/Http/Controllers/Logistik/MutasiController.php
app/Http/Controllers/Logistik/PermintaanController.php
app/Http/Controllers/Logistik/PerencanaanController.php
app/Http/Controllers/Logistik/BarangKatalogController.php
```

**Service wajib:**
```
app/Services/LogistikMutasiService.php
  → catat(int $idStok, string $tipe, float $jumlah, string $asalTujuan, ?string $keterangan, AuthUser $aktor): LogistikMutasi
     1. Validasi jumlah > 0
     2. Jika tipe='keluar': cek jumlah_tersedia cukup (backup sebelum trigger)
     3. INSERT ke logistik_mutasi (trigger DB akan update stok)
     Semua dalam DB::transaction()
     Catat operasi_jurnal setelah mutasi
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Logistik/StoreMutasiRequest.php
app/Http/Requests/Logistik/StorePermintaanRequest.php
app/Http/Requests/Logistik/ApprovePermintaanRequest.php
app/Http/Requests/Logistik/StorePerencanaanRequest.php
app/Http/Requests/Logistik/StoreGudangRequest.php
```

```php
// StoreMutasiRequest
'id_stok'       => 'required|exists:logistik_stok,id_stok',
'tipe_mutasi'   => 'required|in:masuk,keluar,penyesuaian',
'jumlah'        => 'required|numeric|min:0.01',
'asal_tujuan'   => 'required|string|max:255',
'keterangan'    => 'nullable|string',
```

### 9. Blade yang Diperlukan
```
resources/views/logistik/gudang/index.blade.php
resources/views/logistik/gudang/show.blade.php     ← stok per gudang, highlight stok kritis
resources/views/logistik/stok/index.blade.php
resources/views/logistik/mutasi/index.blade.php    ← log mutasi chronologis
resources/views/logistik/mutasi/create.blade.php   ← form: pilih tipe, jumlah, asal_tujuan
resources/views/logistik/permintaan/index.blade.php ← kanban board status permintaan
resources/views/logistik/permintaan/create.blade.php
resources/views/logistik/perencanaan/index.blade.php
```

**UI Rules:**
- Stok kritis: highlight merah jika `jumlah_tersedia < threshold` (threshold dikonfigurasi)
- Form mutasi: tampilkan jumlah stok saat ini sebagai referensi
- Kanban permintaan: kolom `draft | diajukan | disetujui | dikirim | selesai`

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('logistik')->name('logistik.')->group(function () {
    Route::resource('gudang', GudangController::class);
    Route::resource('gudang.stok', StokController::class)->only(['index']);
    Route::resource('gudang.stok.mutasi', MutasiController::class)->only(['index','create','store']);
    Route::resource('permintaan', PermintaanController::class);
    Route::patch('/permintaan/{permintaan}/approve', [PermintaanController::class, 'approve'])->name('permintaan.approve');
    Route::patch('/permintaan/{permintaan}/tolak', [PermintaanController::class, 'tolak'])->name('permintaan.tolak');
    Route::prefix('insiden/{insiden}')->name('insiden.')->group(function () {
        Route::resource('perencanaan', PerencanaanController::class)->only(['index','create','store']);
    });
});
```

### 11. API yang Diperlukan
```
GET /api/logistik/stok-kritis              → stok dengan jumlah_tersedia rendah (untuk command center)
GET /api/insiden/{id}/logistik/summary     → ringkasan logistik per insiden
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Logistik/MutasiTest.php
  ✓ mutasi_masuk_menambah_jumlah_tersedia
  ✓ mutasi_keluar_mengurangi_jumlah_tersedia
  ✓ mutasi_keluar_melebihi_stok_ditolak
  ✓ mutasi_penyesuaian_set_nilai_absolut
  ✓ uuid_mutasi_unik_setiap_transaksi
  ✓ update_langsung_stok_tidak_tersedia_via_route
  ✓ mutasi_tercatat_di_operasi_jurnal

tests/Feature/Logistik/ScopeTest.php
  ✓ pcnu_tidak_dapat_akses_gudang_pcnu_lain
  ✓ gudang_pcnu_a_tidak_dapat_suplai_insiden_pcnu_b
  ✓ pwnu_dapat_akses_semua_gudang

tests/Feature/Logistik/PermintaanTest.php
  ✓ alur_permintaan_draft_diajukan_disetujui_dikirim_selesai
  ✓ permintaan_lintas_insiden_ditolak
```

### 13. Definition of Done
- [ ] Migration semua tabel logistik berhasil
- [ ] `LogistikMutasiService::catat()` berjalan dalam `DB::transaction()`
- [ ] Trigger `tr_execute_logistik_stok_update` berfungsi (stok berubah setelah mutasi)
- [ ] Trigger `tr_logistik_mutasi_integrity_guard` berfungsi (keluar > stok → error)
- [ ] `uuid_mutasi` di-generate otomatis via Model boot
- [ ] Tidak ada route yang memungkinkan update langsung `jumlah_tersedia`
- [ ] UI stok kritis dengan highlight merah berfungsi
- [ ] Semua feature test passing

---

## M13 — Relawan

### 1. Nama Modul
`Relawan`

### 2. Tujuan Modul
Membangun manajemen relawan: pendaftaran untuk kebutuhan spesifik, verifikasi oleh PCNU, dan penugasan ke operasi. Relawan dapat beroperasi lintas PCNU tanpa mengubah organisasi asal.

### 3. Dependensi Modul
- **M01** (Auth), **M04** (Insiden), **M09** (Assignment) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `relawan_pendaftaran` | Pendaftaran relawan untuk kebutuhan spesifik |
| `relawan_penugasan` | Penugasan relawan aktif ke operasi |
| `auth_pengguna_keahlian` | Keahlian relawan |

**Aturan kritis:**
- UNIQUE constraint: `(id_pengguna, id_relawan_kebutuhan)` di `relawan_pendaftaran`
- Relawan belum terverifikasi TIDAK dapat ditugaskan ke operasi

### 5. Model Laravel yang Diperlukan
```
app/Models/RelawanPendaftaran.php
app/Models/RelawanPenugasan.php
```

### 6. Policy yang Diperlukan
```
app/Policies/RelawanPendaftaranPolicy.php
app/Policies/RelawanPenugasanPolicy.php
```
- `RelawanPendaftaranPolicy::create`: semua role `aktif`
- `RelawanPendaftaranPolicy::approve`: [1,2,3] dalam scope
- `RelawanPenugasanPolicy::create`: [1,2,3] — hanya pengguna terverifikasi

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Relawan/PendaftaranController.php
app/Http/Controllers/Relawan/PenugasanRelawanController.php
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Relawan/StorePendaftaranRequest.php
app/Http/Requests/Relawan/ApprovalPendaftaranRequest.php
```

### 9. Blade yang Diperlukan
```
resources/views/relawan/pendaftaran/index.blade.php
resources/views/relawan/pendaftaran/create.blade.php
resources/views/relawan/pendaftaran/show.blade.php     ← detail + approval form
resources/views/relawan/penugasan/index.blade.php
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('relawan')->name('relawan.')->group(function () {
    Route::resource('pendaftaran', PendaftaranController::class)->only(['index','create','store','show']);
    Route::patch('/pendaftaran/{pendaftaran}/approve', [PendaftaranController::class, 'approve'])->name('pendaftaran.approve');
    Route::patch('/pendaftaran/{pendaftaran}/tolak', [PendaftaranController::class, 'tolak'])->name('pendaftaran.tolak');
    Route::resource('penugasan', PenugasanRelawanController::class)->only(['index','show']);
});
```

### 11. API yang Diperlukan
```
GET /api/relawan/tersedia   → relawan aktif + tersedia (is_tersedia=1) untuk assign
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Relawan/RelawanTest.php
  ✓ relawan_dapat_mendaftar
  ✓ daftar_ganda_kebutuhan_sama_ditolak
  ✓ relawan_belum_terverifikasi_tidak_dapat_ditugaskan
  ✓ pcnu_dapat_approve_pendaftaran_dalam_scope
  ✓ cross_region_assignment_valid_tanpa_ubah_organisasi_asal
  ✓ keahlian_relawan_tampil_di_profil
```

### 13. Definition of Done
- [ ] Migration `relawan_pendaftaran`, `relawan_penugasan` berhasil
- [ ] UNIQUE constraint `(id_pengguna, id_relawan_kebutuhan)` berfungsi
- [ ] Approval flow berjalan: menunggu → aktif / ditolak
- [ ] Relawan belum terverifikasi ditolak saat ditugaskan
- [ ] Semua feature test passing

---

## M14 — Aset

### 1. Nama Modul
`Aset`

### 2. Tujuan Modul
Membangun manajemen aset operasional: unit aset, peminjaman ke insiden, dan pengembalian. Dilindungi oleh trigger database yang mencegah double-booking.

### 3. Dependensi Modul
- **M04** (Insiden) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `aset_unit` | Unit aset operasional |
| `aset_penggunaan` | Peminjaman aset per insiden |
| `aset_master_jenis` | Jenis aset (sudah di-seed) |
| `aset_master_kategori` | Kategori aset (sudah di-seed) |
| `aset_master_status` | Status aset: 1=Tersedia, 2=Dalam Tugas, 3=Perbaikan, 4=Rusak, 5=Hilang |

**Trigger yang harus diketahui:**
- `tr_prevent_double_booking_aset`: INSERT ke `aset_penggunaan` → cek `id_status = 1`, jika tidak → SIGNAL error; jika iya → set `id_status = 2`
- `tr_aset_return_to_available`: UPDATE `aset_penggunaan.waktu_kembali` dari NULL → set `id_status = 1`

**Enum `aset_unit.kondisi_fisik`:** `baik`, `rusak_ringan`, `rusak_berat`

### 5. Model Laravel yang Diperlukan
```
app/Models/AsetUnit.php
app/Models/AsetPenggunaan.php
app/Models/AsetMasterJenis.php
app/Models/AsetMasterKategori.php
app/Models/AsetMasterStatus.php
```

### 6. Policy yang Diperlukan
```
app/Policies/AsetPolicy.php
```
- `view`: [1,2,3] dalam scope `id_pemilik_unit`
- `pinjam`: [1,2,3] dalam scope, aset harus berstatus Tersedia
- `kembalikan`: [1,2,3] atau pengguna yang meminjam

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Aset/AsetUnitController.php
app/Http/Controllers/Aset/AsetPenggunaanController.php
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Aset/StoreAsetUnitRequest.php
app/Http/Requests/Aset/PinjamAsetRequest.php
app/Http/Requests/Aset/KembalikanAsetRequest.php
```

```php
// PinjamAsetRequest
'id_insiden'            => 'required|exists:operasi_insiden,id_insiden',
'id_pengguna_peminjam'  => 'required|exists:auth_users,id_pengguna',
'waktu_pinjam'          => 'required|date',
'tujuan_penggunaan'     => 'required|string',
```

### 9. Blade yang Diperlukan
```
resources/views/aset/index.blade.php        ← list aset + status badge + filter
resources/views/aset/show.blade.php         ← detail + histori peminjaman
resources/views/aset/pinjam.blade.php       ← form peminjaman
resources/views/aset/create.blade.php       ← daftarkan aset baru
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('aset')->name('aset.')->group(function () {
    Route::resource('/', AsetUnitController::class)->parameter('', 'aset');
    Route::post('/{aset}/pinjam', [AsetPenggunaanController::class, 'store'])->name('pinjam');
    Route::patch('/penggunaan/{penggunaan}/kembalikan', [AsetPenggunaanController::class, 'kembalikan'])->name('kembalikan');
});
```

### 11. API yang Diperlukan
```
GET /api/aset/tersedia   → aset dengan id_status=1 (untuk dropdown form pinjam)
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Aset/AsetTest.php
  ✓ aset_tersedia_dapat_dipinjam
  ✓ aset_tidak_tersedia_tidak_dapat_dipinjam
  ✓ pinjam_otomatis_set_status_dalam_tugas
  ✓ kembalikan_otomatis_set_status_tersedia
  ✓ double_booking_dicegah_trigger_db
  ✓ kondisi_akhir_dicatat_saat_kembali
```

### 13. Definition of Done
- [ ] Migration `aset_unit`, `aset_penggunaan` berhasil
- [ ] Trigger `tr_prevent_double_booking_aset` berfungsi
- [ ] Trigger `tr_aset_return_to_available` berfungsi
- [ ] Badge status aset tampil dengan warna yang benar
- [ ] Semua feature test passing

---

---

# FASE 6 — LAPANGAN DAN PASCA RESPON

---

## M15 — Pos Aju

### 1. Nama Modul
`Pos Aju`

### 2. Tujuan Modul
Membangun manajemen pos komando lapangan (pos aju) yang dibuka berdasarkan keputusan pleno. Pos aju memiliki komandan yang ditunjuk via pleno dan stok logistik tersendiri.

### 3. Dependensi Modul
- **M07** (Pleno), **M12** (Logistik) — wajib selesai

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `operasi_posaju` | Pos aju lapangan |
| `operasi_posaju_komandan` | Histori komandan pos aju |
| `logistik_stok` | Stok yang terkait ke `id_posaju` |

**Aturan:**
- Pos aju dibuka berdasarkan keputusan pleno (`id_pleno_keputusan` wajib)
- Komandan ditunjuk via pleno (`id_pleno_penunjukan` wajib di `operasi_posaju_komandan`)
- Status DITUTUP: tidak dapat diaktifkan kembali

### 5. Model Laravel yang Diperlukan
```
app/Models/OperasiPosaju.php
app/Models/OperasiPosajuKomandan.php
```

### 6. Policy yang Diperlukan
```
app/Policies/PosajuPolicy.php
```
- `create`: [1,2,3] dalam scope, wajib ada keputusan pleno
- `tutup`: [1,2] atau komandan insiden yang sedang aktif
- Pos aju DITUTUP tidak dapat diubah

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Operasi/PosajuController.php
app/Http/Controllers/Operasi/PosajuKomandanController.php
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Operasi/StorePosajuRequest.php
app/Http/Requests/Operasi/TutupPosajuRequest.php
```

### 9. Blade yang Diperlukan
```
resources/views/operasi/posaju/index.blade.php    ← list pos aju per insiden + peta mini Leaflet
resources/views/operasi/posaju/show.blade.php     ← detail + stok + komandan + personel
resources/views/operasi/posaju/create.blade.php
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('insiden/{insiden}/posaju')->name('insiden.posaju.')->group(function () {
    Route::get('/', [PosajuController::class, 'index'])->name('index');
    Route::get('/buat', [PosajuController::class, 'create'])->name('create');
    Route::post('/', [PosajuController::class, 'store'])->name('store');
    Route::get('/{posaju}', [PosajuController::class, 'show'])->name('show');
    Route::patch('/{posaju}/tutup', [PosajuController::class, 'tutup'])->name('tutup');
    Route::post('/{posaju}/komandan', [PosajuKomandanController::class, 'store'])->name('komandan.store');
});
```

### 11. API yang Diperlukan
```
GET /api/insiden/{id}/posaju/aktif   → posaju aktif + koordinat (untuk peta command center)
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Operasi/PosajuTest.php
  ✓ posaju_wajib_ada_keputusan_pleno
  ✓ komandan_posaju_wajib_ada_pleno_penunjukan
  ✓ posaju_ditutup_tidak_dapat_aktif_kembali
  ✓ stok_posaju_terhubung_ke_id_posaju
  ✓ koordinat_posaju_tampil_di_peta
```

### 13. Definition of Done
- [ ] Migration `operasi_posaju`, `operasi_posaju_komandan` berhasil
- [ ] Validasi `id_pleno_keputusan` wajib berfungsi
- [ ] Pos aju DITUTUP: semua form disabled, tidak ada route PATCH kecuali `tutup`
- [ ] Marker Leaflet.js untuk pos aju berfungsi di halaman show
- [ ] Semua feature test passing

---

## M16 — Pengungsian & Penerima Manfaat

### 1. Nama Modul
`Pengungsian & Penerima Manfaat`

### 2. Tujuan Modul
Membangun manajemen data penerima manfaat (pengungsi, penerima bantuan) yang terkait dengan insiden. Statistik pengungsi ditampilkan di command center.

### 3. Dependensi Modul
- **M04** (Insiden), **M05** (Assessment) — wajib selesai

> ⚠️ **CATATAN:** Hanya `master_penerima_manfaat` yang terkonfirmasi di SQL dump. Tabel operasional pengungsian (seperti `pengungsian_utama`) belum ada. Implementasikan modul ini berdasarkan tabel yang terkonfirmasi saja.

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `master_penerima_manfaat` | Data penerima bantuan / pengungsi |

**Enum:**
- `tipe_penerima`: `individu`, `kk`, `kelompok`, `posko`, `desa`, `lembaga`

### 5. Model Laravel yang Diperlukan
```
app/Models/MasterPenerimaBanfaat.php
```
> **Catatan:** Nama model mengikuti nama tabel `master_penerima_manfaat` — perhatikan bahwa kolom `id_penerima` adalah PK.

### 6. Policy yang Diperlukan
```
app/Policies/PenerimaBanfaatPolicy.php
```

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Pengungsian/PenerimaBanfaatController.php
```

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Pengungsian/StorePenerimaBanfaatRequest.php
```
```php
'nama_penerima'   => 'required|string|max:150',
'nik_penerima'    => 'nullable|digits:16|required_if:tipe_penerima,individu',
'tipe_penerima'   => 'required|in:individu,kk,kelompok,posko,desa,lembaga',
'id_desa'         => 'nullable|string|max:10',
'alamat_detail'   => 'nullable|string',
'kontak'          => 'nullable|string|max:20',
```

### 9. Blade yang Diperlukan
```
resources/views/pengungsian/penerima/index.blade.php
resources/views/pengungsian/penerima/create.blade.php
resources/views/pengungsian/penerima/show.blade.php
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('pengungsian')->name('pengungsian.')->group(function () {
    Route::resource('penerima', PenerimaBanfaatController::class);
});
```

### 11. API yang Diperlukan
```
GET /api/insiden/{id}/pengungsi/statistik   → aggregasi dari assessment_dampak_manusia
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Pengungsian/PenerimaBanfaatTest.php
  ✓ individu_wajib_nik
  ✓ tipe_penerima_valid
  ✓ data_penerima_dapat_disimpan
```

### 13. Definition of Done
- [ ] Migration `master_penerima_manfaat` berhasil
- [ ] Validasi `nik` wajib untuk tipe `individu` berfungsi
- [ ] Statistik pengungsi dari `assessment_dampak_manusia` tampil di UI
- [ ] Semua feature test passing

---

## M17 — Jurnal Operasi & Audit Trail

### 1. Nama Modul
`Jurnal Operasi & Audit Trail`

### 2. Tujuan Modul
Membangun tampilan dan manajemen jurnal operasi yang mencatat semua event penting. Jurnal ditulis secara otomatis oleh service layer, modul ini menyediakan tampilan dan API untuk membacanya.

### 3. Dependensi Modul
- **M04** (Insiden) — minimum. Jurnal sudah ditulis oleh service lain sejak M04.

### 4. Tabel yang Digunakan
| Tabel | Keterangan |
|---|---|
| `operasi_jurnal` | Catatan naratif event operasional |
| `riwayat_status_insiden` | Histori transisi status (sudah diimplementasi di M04) |

**`operasi_jurnal` — kolom kritis:**
- `kategori_event` ENUM: `sistem`, `laporan`, `aktivasi`, `respon`, `penugasan`, `logistik`, `aset`, `personil`, `posko`, `selesai`
- `id_referensi` + `tabel_referensi`: polymorphic reference (TANPA FK fisik)
- Tidak dapat dihapus (tidak ada soft delete, tidak ada route DELETE)

### 5. Model Laravel yang Diperlukan
```
app/Models/OperasiJurnal.php
```
```php
protected $table = 'operasi_jurnal';
protected $primaryKey = 'id_jurnal';
const CREATED_AT = 'waktu_event';
const UPDATED_AT = null;
public $timestamps = false;

// Polymorphic: entitas_referensi() bukan relasi Eloquent standar
// Gunakan map manual berdasarkan tabel_referensi
public function entitasReferensi(): ?Model
{
    $map = [
        'operasi_insiden'     => OperasiInsiden::class,
        'operasi_sitrep'      => OperasiSitrep::class,
        'logistik_mutasi'     => LogistikMutasi::class,
        'operasi_penugasan'   => OperasiPenugasan::class,
        'aset_penggunaan'     => AsetPenggunaan::class,
        'operasi_posaju'      => OperasiPosaju::class,
    ];
    if (!isset($map[$this->tabel_referensi])) return null;
    return $map[$this->tabel_referensi]::find($this->id_referensi);
}
```

**Service wajib (digunakan oleh semua service lain):**
```
app/Services/JurnalService.php
  → catat(int $idInsiden, string $kategori, string $judul, AuthUser $aktor,
          ?string $deskripsi = null, ?int $idReferensi = null, ?string $tabelReferensi = null): void
     OperasiJurnal::create([...])
     TIDAK dalam transaction — jurnal adalah best-effort, tidak boleh rollback operasi utama
```

### 6. Policy yang Diperlukan
```
app/Policies/JurnalPolicy.php
```
- `view`: semua role internal yang punya akses insiden
- `create`: semua role internal (manual jurnal oleh operator)
- TIDAK ADA `delete` — jurnal tidak boleh dihapus

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Operasi/JurnalController.php
```
Methods: `index` (list per insiden), `store` (manual jurnal oleh operator)

### 8. Request Validation yang Diperlukan
```
app/Http/Requests/Operasi/StoreJurnalRequest.php
```
```php
'kategori_event'  => 'required|in:sistem,laporan,aktivasi,respon,penugasan,logistik,aset,personil,posko,selesai',
'judul_event'     => 'required|string|max:255',
'deskripsi_event' => 'nullable|string',
```

### 9. Blade yang Diperlukan
```
resources/views/operasi/jurnal/index.blade.php   ← timeline jurnal per insiden, filter per kategori
resources/views/operasi/jurnal/create.blade.php  ← form manual jurnal (operator)
```

### 10. Route yang Diperlukan
```php
Route::middleware(['auth', 'check.akun.aktif'])->prefix('insiden/{insiden}/jurnal')->name('insiden.jurnal.')->group(function () {
    Route::get('/', [JurnalController::class, 'index'])->name('index');
    Route::get('/buat', [JurnalController::class, 'create'])->name('create');
    Route::post('/', [JurnalController::class, 'store'])->name('store');
    // TIDAK ADA route DELETE
});
```

### 11. API yang Diperlukan
```
GET /api/insiden/{id}/jurnal             → timeline jurnal terbaru (untuk command center)
GET /api/insiden/{id}/jurnal?kategori=X  → filter per kategori
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Operasi/JurnalTest.php
  ✓ transisi_status_insiden_otomatis_catat_jurnal
  ✓ mutasi_logistik_otomatis_catat_jurnal
  ✓ jurnal_tidak_dapat_dihapus
  ✓ filter_jurnal_per_kategori_berfungsi
  ✓ manual_jurnal_dapat_dibuat_oleh_operator
```

### 13. Definition of Done
- [ ] `JurnalService::catat()` tersedia dan digunakan oleh semua service domain
- [ ] Tidak ada route DELETE untuk jurnal
- [ ] Timeline jurnal tampil chronologis dengan filter kategori
- [ ] Jurnal otomatis terisi saat transisi status (dari InsidenService, SitrepService, dll)
- [ ] Semua feature test passing

---

---

# FASE 7 — DASHBOARD DAN PUBLIK

---

## M18 — Command Center & Dashboard

### 1. Nama Modul
`Command Center & Dashboard`

### 2. Tujuan Modul
Membangun tampilan operasional real-time untuk pengguna internal: peta insiden aktif (Leaflet.js), statistik personel, stok kritis, dan aktivitas terbaru. Seluruh data bersifat **read-only** — tidak ada write operation dari command center.

### 3. Dependensi Modul
- Semua modul Fase 1–6 minimum M04, M06, M09, M12, M15 — wajib selesai

### 4. Tabel yang Digunakan (READ ONLY)
`operasi_insiden`, `operasi_sitrep`, `operasi_mobilisasi_personil`, `logistik_stok`, `operasi_posaju`, `operasi_jurnal`, `assessment_dampak_manusia`, `laporan_kejadian`

### 5. Model Laravel yang Diperlukan
Tidak ada model baru. Menggunakan model yang sudah ada.

### 6. Policy yang Diperlukan
```
app/Policies/DashboardPolicy.php
```
- `viewCommandCenter`: [1,2,3] atau relawan dengan assignment aktif
- `viewPublik`: semua (tanpa auth)

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Dashboard/DashboardController.php
app/Http/Controllers/Dashboard/CommandCenterController.php
app/Http/Controllers/Api/CommandCenterApiController.php
```

**`DashboardController`:** aggregasi data per role (pcnu: filter scope, pwnu: semua)
**`CommandCenterController`:** render halaman utama command center
**`CommandCenterApiController`:** endpoint AJAX untuk polling

### 8. Request Validation yang Diperlukan
Tidak ada (read-only).

### 9. Blade yang Diperlukan
```
resources/views/dashboard/index.blade.php          ← dashboard ringkasan per role
resources/views/dashboard/command-center.blade.php ← peta + statistik (polling AJAX 30 detik)
resources/views/dashboard/partials/stats-insiden.blade.php
resources/views/dashboard/partials/stats-logistik.blade.php
resources/views/dashboard/partials/peta-insiden.blade.php   ← Leaflet.js
```

**Map Rules:**
- Base layer: OpenStreetMap (tile gratis)
- Marker clustering: `Leaflet.markercluster`
- Custom icon per `bencana_master_jenis.ikon_map`
- Popup: `kode_kejadian`, `status_insiden`, `jenis_bencana`, `waktu_mulai`
- Layer control: filter per status insiden
- Hanya marker insiden status `respon` dan `pemulihan` yang tampil

**Polling:**
```javascript
// Polling setiap 30 detik — BUKAN WebSocket
setInterval(function() {
    fetch('/api/command-center/insiden-aktif')
        .then(r => r.json())
        .then(data => updateMap(data));
}, 30000);
```

### 10. Route yang Diperlukan
```php
// Internal dashboard
Route::middleware(['auth', 'check.akun.aktif'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/command-center', [CommandCenterController::class, 'index'])->name('command-center');
});

// API untuk polling (auth required)
Route::middleware('auth:sanctum')->prefix('api/command-center')->name('api.command-center.')->group(function () {
    Route::get('/insiden-aktif', [CommandCenterApiController::class, 'insidenAktif'])->name('insiden-aktif');
    Route::get('/statistik', [CommandCenterApiController::class, 'statistik'])->name('statistik');
    Route::get('/stok-kritis', [CommandCenterApiController::class, 'stokKritis'])->name('stok-kritis');
    Route::get('/jurnal-terbaru', [CommandCenterApiController::class, 'jurnalTerbaru'])->name('jurnal-terbaru');
});
```

### 11. API yang Diperlukan
```
GET /api/command-center/insiden-aktif    → [{id, kode, lat, lng, status, jenis, prioritas}]
GET /api/command-center/statistik        → {total_insiden, total_personel, stok_kritis_count}
GET /api/command-center/stok-kritis      → daftar barang dengan jumlah_tersedia < threshold
GET /api/command-center/jurnal-terbaru   → 10 jurnal terbaru lintas insiden aktif
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Dashboard/CommandCenterTest.php
  ✓ pwnu_dapat_akses_command_center
  ✓ pcnu_melihat_data_scope_sendiri_saja
  ✓ publik_tidak_dapat_akses_command_center
  ✓ api_insiden_aktif_hanya_return_respon_dan_pemulihan
  ✓ api_stok_kritis_return_data_sesuai_scope
  ✓ relawan_yang_tidak_ditugaskan_tidak_dapat_akses
```

### 13. Definition of Done
- [ ] Dashboard tampil berbeda berdasarkan role (pcnu vs pwnu)
- [ ] Peta Leaflet.js dengan marker clustering berfungsi
- [ ] Custom icon per jenis bencana berfungsi
- [ ] AJAX polling 30 detik berfungsi tanpa error console
- [ ] Data di command center difilter scope dengan benar
- [ ] Tidak ada operasi write dari command center
- [ ] Semua feature test passing

---

## M19 — Public UI & Laporan Publik

### 1. Nama Modul
`Public UI & Laporan Publik`

### 2. Tujuan Modul
Membangun antarmuka publik: peta insiden aktif yang dapat diakses tanpa login, form laporan kejadian dari masyarakat, dan informasi umum pos aju aktif.

### 3. Dependensi Modul
- **M04** (Insiden & Laporan) — wajib selesai
- **M18** (Command Center) — direkomendasikan selesai untuk berbagi komponen peta

### 4. Tabel yang Digunakan (READ ONLY + INSERT)
| Tabel | Akses |
|---|---|
| `operasi_insiden` | READ — hanya status aktif, tanpa data sensitif |
| `operasi_sitrep` | READ — hanya sitrep final, aggregate dampak |
| `operasi_posaju` | READ — nama pos aju saja (tanpa koordinat detail) |
| `laporan_kejadian` | INSERT — form laporan dari masyarakat |

### 5. Model Laravel yang Diperlukan
Menggunakan model yang sudah ada dari M04.

### 6. Policy yang Diperlukan
Tidak ada Policy baru — route publik tidak memerlukan auth.

### 7. Controller yang Diperlukan
```
app/Http/Controllers/Publik/PublikPetaController.php
app/Http/Controllers/Publik/PublikInfoController.php
```

### 8. Request Validation yang Diperlukan
`StoreLaporanKejadianRequest` — sudah ada dari M04.

### 9. Blade yang Diperlukan
```
resources/views/publik/peta.blade.php         ← peta publik, tanpa data sensitif
resources/views/publik/info.blade.php         ← informasi insiden aktif (deskripsi umum saja)
resources/views/publik/laporan/create.blade.php  ← sudah ada dari M04
resources/views/publik/laporan/sukses.blade.php  ← konfirmasi setelah submit laporan
```

**Data yang BOLEH ditampilkan ke publik:**
- Jenis bencana dan lokasi umum (kecamatan/kota, bukan koordinat presisi)
- Status insiden (respon/pemulihan saja)
- Statistik korban aggregate dari sitrep final terbaru
- Nama pos aju aktif (tanpa koordinat detail)
- Form laporan kejadian

**Data yang DILARANG ditampilkan ke publik:**
- Data logistik
- Data personel (nama relawan)
- Koordinat presisi pos aju
- Detail internal operasi

### 10. Route yang Diperlukan
```php
// Routes publik (tanpa auth)
Route::prefix('publik')->name('publik.')->group(function () {
    Route::get('/', [PublikPetaController::class, 'index'])->name('peta');
    Route::get('/info', [PublikInfoController::class, 'index'])->name('info');
    Route::get('/info/{insiden}', [PublikInfoController::class, 'show'])->name('info.show');
    Route::get('/laporan', [LaporanKejadianController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [LaporanKejadianController::class, 'store'])->name('laporan.store');
});

// API publik (tanpa auth)
Route::prefix('api/publik')->name('api.publik.')->group(function () {
    Route::get('/insiden-aktif', [PublikPetaController::class, 'insidenAktif'])->name('insiden-aktif');
});
```

### 11. API yang Diperlukan
```
GET /api/publik/insiden-aktif   → [{jenis, lokasi_umum, status, statistik_korban_aggregate}]
// TIDAK menyertakan: koordinat presisi, data logistik, data personel
```

### 12. Feature Test yang Diperlukan
```
tests/Feature/Publik/PublikTest.php
  ✓ publik_dapat_akses_peta_tanpa_login
  ✓ api_publik_tidak_mengekspos_data_sensitif
  ✓ form_laporan_dapat_disubmit_tanpa_login
  ✓ laporan_tersimpan_dengan_status_menunggu
  ✓ koordinat_invalid_ditolak_di_form_publik
```

### 13. Definition of Done
- [ ] Halaman peta publik dapat diakses tanpa login
- [ ] Data API publik tidak mengekspos data logistik, personel, atau koordinat presisi
- [ ] Form laporan kejadian berfungsi dengan validasi koordinat
- [ ] Konfirmasi setelah submit laporan ditampilkan
- [ ] Semua feature test passing

---

---

# LAMPIRAN

## Ringkasan Definition of Done Global

Setiap modul dinyatakan SELESAI jika memenuhi semua kriteria berikut:

| Kriteria | Keterangan |
|---|---|
| Migration berhasil | `php artisan migrate` tanpa error di MySQL |
| Seeder berhasil | `php artisan db:seed` mengisi data yang benar |
| Model | PK, timestamp, dan relasi didefinisikan benar |
| Policy | Semua aksi memiliki Policy, tidak ada `return true` kosong |
| FormRequest | `authorize()` diisi logika nyata, rules komprehensif |
| Controller | Semua action menggunakan `$this->authorize()` |
| Blade | Bootstrap 5.3, responsive, tidak ada query di view |
| Route | Named routes, middleware correct, tidak ada route berlebihan |
| Feature Test | Semua test passing di MySQL (bukan SQLite) |
| DB Transaction | Semua operasi multi-tabel dalam `DB::transaction()` |
| Jurnal | Event penting tercatat ke `operasi_jurnal` |
| Scope wilayah | PCNU hanya akses data scope sendiri |

---

## Checklist Sprint-Ready

Sebelum memulai sprint, verifikasi:

- [ ] Database MySQL `nurisk` sudah dibuat dan SQL dump di-import
- [ ] Database MySQL `nurisk_testing` sudah dibuat untuk testing
- [ ] `.env` dan `.env.testing` sudah dikonfigurasi
- [ ] `composer install` berhasil
- [ ] `php artisan key:generate` sudah dijalankan
- [ ] Package `spatie/laravel-permission` dan `barryvdh/laravel-dompdf` sudah diinstal
- [ ] Storage link sudah dibuat: `php artisan storage:link`

---

## Tabel Dependensi Modul (Dependency Graph)

```
M01 (Auth)
 └── M02 (Authorization Infra)
      └── M03 (Master Data)
           └── M04 (Insiden)
                ├── M05 (Assessment)
                │    └── M06 (Sitrep)
                ├── M07 (Pleno)
                │    └── M08 (Surat)
                ├── M09 (Assignment)
                │    ├── M10 (Mobilisasi & Klaster)
                │    └── M11 (Periode Operasional)
                ├── M12 (Logistik)
                │    └── M15 (Pos Aju) ← juga butuh M07
                ├── M13 (Relawan)
                ├── M14 (Aset)
                └── M16 (Pengungsian)

M17 (Jurnal/Audit) ← berjalan paralel dengan semua modul dari M04+
M18 (Command Center) ← setelah M04, M06, M09, M12, M15
M19 (Public UI) ← setelah M04, M18
```

---

## Catatan Sinkronisasi SQL dan PRD

| Item | Status | Tindakan |
|---|---|---|
| Tabel `feedback_klaster` | ⚠️ Belum ada di SQL | TUNGGU konfirmasi — jangan implementasi |
| Tabel `gap_kebutuhan` | ⚠️ Belum ada di SQL | TUNGGU konfirmasi — jangan implementasi |
| Tabel pengungsian operasional | ⚠️ Hanya `master_penerima_manfaat` | Implementasi M16 hanya pakai tabel yang ada |
| `waktu_assesment` (typo) | ✅ Dikonfirmasi dari SQL | IKUTI typo SQL — jangan koreksi di kode |
| Tabel hierarki organisasi NU | ⚠️ Tidak ada di SQL | Gunakan enum `default_scope_type` di `auth_users` |
| Dual role mechanism | ⚠️ `auth_roles` + Spatie `model_has_roles` | Sync via trigger `tr_sync_user_role_insert` |
| `master_penerima_manfaat` | ✅ Ada di SQL | Nama kolom: `id_penerima`, `nama_penerima` |
