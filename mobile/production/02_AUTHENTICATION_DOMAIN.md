# NURISK MOBILE — AUTHENTICATION DOMAIN
## Document 02: Authentication Domain Specification
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Authentication  
**Author**: Enterprise Mobile Solution Architect  
**Backend Reference**: `POST /api/auth/login`, `GET /api/auth/me`, `POST /api/auth/logout`

---

## 1. OVERVIEW

Domain Authentication mengatur seluruh siklus identitas pengguna di NURISK Mobile — dari pertama kali membuka aplikasi, memilih mandate aktif, menjaga sesi aktif, hingga logout. Semua logika otentikasi **dieksekusi di server Laravel**. Flutter hanya menyimpan token dan menampilkan UI.

**Mekanisme Autentikasi**: Laravel Sanctum (Bearer Token)
**Identifier Login**: `no_hp` (Nomor Handphone) — bukan email
**Token Type**: Opaque Bearer Token (bukan JWT)

---

## 2. FITUR-FITUR DOMAIN

### 2.1 LOGIN

**Definisi**: User memasukkan `no_hp` dan `kata_sandi` untuk mendapatkan Bearer Token.

**Input Fields**:
- `no_hp` (required) — Nomor handphone terdaftar
- `kata_sandi` (required) — Password akun
- `device_name` (optional) — Identifier device untuk Sanctum (auto-generated: `{platform}_{model}`)

**Output**:
- Bearer Token (disimpan di `flutter_secure_storage`)
- Profil user (`user`, `profil`, `peran`)
- Redirect ke Mandate Picker atau Dashboard

**Status Akun yang Diblokir Login**:
| Status | Pesan ke User |
|--------|--------------|
| `menunggu` | "Akun Anda sedang menunggu persetujuan admin." |
| `nonaktif` | "Akun Anda telah dinonaktifkan. Hubungi administrator." |
| `suspend` | "Akun Anda sementara ditangguhkan." |

**Aturan**:
- Tidak boleh ada auto-login bypass
- Token wajib disimpan di Secure Storage, tidak di SharedPreferences biasa
- Setelah 5x salah input, UI menampilkan CAPTCHA atau cooldown (implementasi backend)

---

### 2.2 LOGOUT

**Definisi**: Mencabut Bearer Token aktif dari server dan membersihkan state lokal.

**Proses**:
1. Kirim `POST /api/auth/logout` dengan Bearer Token aktif
2. Hapus token dari Secure Storage
3. Hapus seluruh cache mandate, profil, dan session
4. Reset Riverpod state (invalidate semua provider)
5. Redirect ke Login Screen

**Logout Triggers**:
- User menekan tombol Logout secara manual
- Server mengembalikan 401 dan refresh token gagal
- Session timeout terdeteksi (idle)
- Admin merevoke akun dari backend

**Catatan**: Jika `POST /api/auth/logout` gagal (network error), tetap lakukan local logout. Anggap token sudah invalid.

---

### 2.3 TOKEN MANAGEMENT

**Token Type**: Sanctum Opaque Token (tidak memiliki expiry bawaan di payload)

**Token Lifecycle**:
- Token **tidak expire** secara otomatis di sisi Laravel (konfigurasi default Sanctum)
- Token dianggap expired jika server mengembalikan **HTTP 401**
- Device Token (dari `POST /v1/device/refresh-token`) memiliki TTL 30 hari

**Strategi Refresh**:
- Tidak ada refresh token endpoint standar OAuth2 di NURISK
- Jika `401` diterima:
  1. Flutter mencoba `POST /v1/device/refresh-token` dengan `device_uuid`
  2. Jika berhasil → simpan `device_token` baru → retry request asal
  3. Jika gagal → logout → redirect ke Login

**Penyimpanan**:
| Data | Storage |
|------|---------|
| `sanctum_token` | `flutter_secure_storage` |
| `device_uuid` | `flutter_secure_storage` (generate UUID v4 saat pertama install) |
| `device_token` | `flutter_secure_storage` |
| `token_expires_at` | `flutter_secure_storage` |

---

### 2.4 DEVICE BINDING

**Definisi**: Setiap instalasi Flutter dikaitkan dengan `device_uuid` yang unik dan permanen.

**UUID Generation**: 
- UUID v4 di-generate satu kali saat pertama install
- Disimpan di `flutter_secure_storage` (tidak terhapus saat logout)
- Hanya terhapus jika aplikasi di-uninstall/reinstall

**Device Registration**:
- `device_uuid` dikirim sebagai `device_name` saat login
- Server menyimpan di tabel `mobile_devices` via `MobileDevice` model

**Penonaktifan Device**:
- Admin dapat menonaktifkan device dari backend
- Jika `POST /v1/device/refresh-token` mengembalikan `status: "inactive"` → logout paksa + pesan "Device Anda telah dinonaktifkan oleh administrator."

---

### 2.5 MULTIPLE DEVICE

**Kebijakan**: NURISK mengizinkan multi-device per user (tidak ada restriction jumlah device).

**Implikasi Flutter**:
- Token per device bersifat independen
- Logout di satu device tidak mempengaruhi device lain
- Admin dapat melihat semua device aktif via `GET /v1/devices`
- User dapat logout semua device via `POST /v1/devices/logout-all`

---

### 2.6 REMEMBER LOGIN

**Implementasi**: 
- Secara default, Bearer Token bersifat persistent (tidak ada expiry)
- "Remember Login" = tidak menghapus token dari storage saat menutup app
- Jika user menonaktifkan "Remember Login" di Settings → token dihapus saat app di-background selama >24 jam

**Biometric & PIN** (lihat 2.7 dan 2.8) berfungsi sebagai mekanisme re-auth cepat tanpa harus memasukkan password ulang.

---

### 2.7 BIOMETRIC AUTHENTICATION

**Definisi**: Verifikasi identitas menggunakan sidik jari atau Face ID sebagai pengganti memasukkan PIN/password saat membuka app.

**Prinsip Penting**:
- Biometric **tidak** menggantikan login ke server
- Biometric hanya digunakan untuk **unlock sesi lokal** yang sudah ter-authenticate
- Token tetap disimpan di Secure Storage dan divalidasi ke server

**Alur**:
1. Token valid ada di Secure Storage
2. App di-reopen setelah background
3. Tampilkan biometric prompt
4. Jika biometric sukses → load sesi → Dashboard
5. Jika biometric gagal 3x → minta PIN
6. Jika PIN gagal → logout paksa

**Syarat**: User harus mengaktifkan biometric secara eksplisit di Settings pertama kali.

---

### 2.8 PIN

**Definisi**: 6-digit PIN sebagai alternatif biometric atau sebagai fallback.

**PIN Storage**: PIN hash disimpan di `flutter_secure_storage` menggunakan `SHA-256(pin + device_uuid)` sebagai salt.

**Aturan PIN**:
- Minimal 6 digit, numerik
- Tidak boleh berurutan (123456) atau berulang (111111)
- Dapat diubah di Settings setelah verifikasi PIN lama
- Reset PIN hanya bisa via full login (no_hp + password)
- Maksimal 5 percobaan gagal → logout paksa

---

### 2.9 OTP (Future Sprint)

**Status**: Direncanakan di Sprint F3. Belum diimplementasikan di backend.

**Rencana**:
- OTP dikirim via WhatsApp/SMS ke `no_hp` terdaftar
- Digunakan untuk: Forgot Password, Change Email, device baru
- TTL OTP: 5 menit, 3x percobaan

---

### 2.10 FORGOT PASSWORD

**Status**: Backend belum memiliki endpoint. Didokumentasikan sebagai kebutuhan yang perlu ditambahkan.

**Alur yang Direncanakan**:
1. User tap "Lupa Password" di Login Screen
2. Masukkan `no_hp`
3. OTP dikirim ke WhatsApp/SMS
4. Verifikasi OTP
5. Set password baru (minimal 8 karakter)
6. Redirect ke Login

**Endpoint yang Diperlukan (belum ada di backend)**:
- `POST /api/auth/forgot-password` 
- `POST /api/auth/verify-otp`
- `POST /api/auth/reset-password`

---

### 2.11 CHANGE PASSWORD

**Status**: Backend belum memiliki endpoint eksplisit. Perlu ditambahkan.

**Alur**:
1. User masuk ke Settings → Keamanan → Ubah Password
2. Masukkan password lama → validasi ke server
3. Masukkan password baru (2x konfirmasi)
4. Submit → jika berhasil → logout semua device → login ulang

---

### 2.12 CHANGE EMAIL (FUTURE)

**Catatan**: NURISK menggunakan `no_hp` sebagai identifier, bukan email. Jika email perlu ditambahkan, ini adalah fitur Future Sprint yang memerlukan desain backend terlebih dahulu.

---

### 2.13 PROFILE SESSION

**Data yang Disimpan di Local Cache** (drift SQLite):

```
UserSession {
  id_pengguna     : int
  no_hp           : string
  nama_lengkap    : string
  foto_profil     : string?
  id_peran        : int
  nama_peran      : string
  status_akun     : enum (menunggu/aktif/nonaktif/suspend)
  terakhir_masuk  : DateTime
  active_mandate  : MandateSnapshot?
  cached_at       : DateTime
}
```

**MandateSnapshot** (disimpan bersama session):
```
MandateSnapshot {
  id              : int
  sk_id           : int
  node_id         : int
  node_name       : string
  position_id     : int
  position_name   : string
  tanggal_mulai   : Date
  tanggal_berakhir: Date?
  status          : enum
}
```

---

### 2.14 SESSION TIMEOUT

**Session Timeout**: Token Sanctum tidak expire di server secara default. Flutter yang mengelola timeout.

**App Timeout (Foreground Idle)**:
- Default: 30 menit tidak ada interaksi
- Configurable di Settings: 15, 30, 60 menit, atau Never
- Timeout → Biometric/PIN unlock prompt

**Background Timeout**:
- Jika app berada di background > 4 jam → require Biometric/PIN saat dibuka
- Jika app berada di background > 24 jam tanpa "Remember Login" → require full re-login

---

### 2.15 UNAUTHORIZED FLOW

#### 401 — Unauthorized (Token Invalid/Expired)
```
Request → 401 Response
    ↓
Coba Refresh via device_uuid (POST /v1/device/refresh-token)
    ↓
[Refresh OK]          [Refresh Fail]
    ↓                      ↓
Retry request          Hapus semua token
    ↓                      ↓
Continue               Tampilkan dialog: "Sesi Anda telah berakhir"
                           ↓
                       Redirect ke Login
```

#### 403 — Forbidden (Tidak Ada Permission)
```
Request → 403 Response
    ↓
Tampilkan Permission Denied Screen
    ↓
Tampilkan role aktif + mandate aktif user
    ↓
Tombol: "Kembali" atau "Hubungi Administrator"
```

#### 401 Expired Session (Idle Timeout)
```
Idle Timer Trigger
    ↓
Pause semua background sync
    ↓
Tampilkan Lock Screen
    ↓
[Biometric/PIN]        [Forgot PIN]
    ↓                      ↓
Resume sesi            Full Re-Login
```

---

### 2.16 ROLE DETECTION

**Sumber**: Field `id_peran` dan relasi `peran` dari `GET /api/auth/me`

**Role yang Dikenali Flutter**:
| Role Key | Label UI |
|----------|---------|
| `super_admin` | Super Administrator |
| `pwnu` | Admin PWNU |
| `pcnu` | Admin PCNU |
| `relawan` | Relawan |
| `operator` | Operator |

**Implikasi di Flutter**:
- Role menentukan menu yang muncul di Bottom Navigation dan Drawer
- Role menentukan Quick Action yang tersedia
- Lihat `07_ROLE_BASED_NAVIGATION.md` untuk detail per-role

---

### 2.17 PERMISSION DETECTION

**Sumber**: Endpoint `GET /api/auth/me` → field `jabatanPosisi.jabatan` (relasi ke permissions)

**Penyimpanan**: Permission di-cache di SQLite lokal setelah login, di-refresh setiap 12 jam atau saat mandate berubah.

**Permission Format** (kontrak yang perlu distandarisasi dengan backend):
```json
{
  "permissions": [
    "governance.mandate.view",
    "governance.approval.create",
    "operasi.incident.view",
    "media.upload"
  ]
}
```

**Catatan**: Saat ini endpoint `/api/auth/me` belum mengembalikan permission secara terstruktur. Ini adalah **gap yang harus diselesaikan di backend sebelum Sprint F1**.

---

### 2.18 CURRENT MANDATE

**Definisi**: Mandate yang sedang aktif dipilih oleh user untuk sesi ini.

**Sumber Data**: `GET /api/governance/mandates?user_id={id}`
**Filter**: `status = aktif` dan `tanggal_mulai <= today <= tanggal_berakhir` (atau `tanggal_berakhir is null`)

**Storage**: Disimpan di Secure Storage sebagai `active_mandate_id` (int)

**Saat Mandate Berubah**:
1. Clear semua cache operasional
2. Re-fetch permission sesuai mandate baru
3. Re-build navigation menu
4. Tampilkan konfirmasi: "Anda sekarang beroperasi sebagai [Nama Jabatan] di [Nama Node]"

---

### 2.19 CURRENT NODE & AUTHORITY

**Definisi**: 
- **Current Node** = Unit organisasi (OrgNode) dari mandate aktif
- **Current Authority** = Kewenangan (OrgAuthority) yang dimiliki di node tersebut

**Sumber Data**:
- `GET /api/governance/nodes/{node_id}` — untuk info node
- `GET /api/governance/authorities?node_id={id}&user_id={id}` — untuk authority

**Cached di**: SQLite lokal, TTL 12 jam

---

### 2.20 CURRENT TERRITORY

**Definisi**: Wilayah geografis yang menjadi tanggung jawab mandate aktif.

**Sumber Data**: Field `territory_code` dari `OrgNode` terkait mandate.

**Implikasi**:
- Filter data di peta berdasarkan `territory_code`
- Filter laporan masuk berdasarkan wilayah
- Tampilkan info wilayah di header Dashboard

---

### 2.21 GUEST USER

**Status**: NURISK tidak mendukung guest user. Semua fitur memerlukan autentikasi.

**Pengecualian**: Halaman publik (laporan bencana publik via `GET /api/public/dashboard`) dapat diakses tanpa token, namun tidak melalui Flutter app — hanya via browser/web.

---

*Document Status: DRAFT — Menunggu konfirmasi backend untuk endpoint Forgot Password dan Permission structure*
