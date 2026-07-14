# NURISK MOBILE — AUTHENTICATION UI FLOW
## Document 04: Authentication UI Flow & State Machine
**Version**: 2.0.0 | **Status**: REVISED — PUBLIC FIRST | **Domain**: Authentication

> **⚠️ PARADIGMA BERUBAH (v2.0)**  
> Dokumen ini telah direvisi total. Alur lama (Splash→Login→Dashboard) adalah **SALAH**.  
> Paradigma baru: Splash → Public Dashboard → Login hanya jika user membutuhkan fitur Governance.  
> Referensi: `FLUTTER_APPLICATION_ARCHITECTURE.md` Article 3 & 7.

---

## 1. MASTER FLOW DIAGRAM — PUBLIC FIRST

```
╔═══════════════════════╗
║     APP LAUNCH         ║
╚═══════════════════════╝
           │
           ▼
╔═══════════════════════╗
║    SPLASH SCREEN       ║  (2-3 detik branding + silent init)
╚═══════════════════════╝
           │
           ▼
╔═══════════════════════════════════════════╗
║   SILENT INIT (Background)                ║
║  1. Init Drift SQLite (public + private)  ║
║  2. Load environment config               ║
║  3. Read token dari Secure Storage        ║
║  4. Jika ada token: silent validate       ║
║  5. Init FCM + public topic subscribe     ║
╚═══════════════════════════════════════════╝
           │
           ▼
╔══════════════════════════════╗
║   PUBLIC DASHBOARD (/p/home) ║  ← DEFAULT LANDING (semua user)
║   (muncul TANPA login)       ║
╚══════════════════════════════╝
           │
    ┌──────┴─────────────────────────────┐
    │                                    │
[User browsing publik]      [User tap fitur governance]
    │                                    │
    ▼                                    ▼
[Tetap di Public Layer]      AuthGuard mendeteksi:
                              tidak ada token
                                         │
                                         ▼
                             ╔═══════════════════════╗
                             ║   LOGIN SCREEN         ║
                             ║ (dengan return_to URL) ║
                             ╚═══════════════════════╝
                                         │
                             ┌───────────┴───────────┐
                          [Login OK]             [Login Fail]
                             │                       │
                             ▼                 Tampilkan error
                        Fetch Mandates
                             │
                ┌────────────┼────────────┐
                ▼            ▼            ▼
          [0 mandate] [1 mandate]  [>1 mandate]
                │         │              │
                ▼         ▼              ▼
           Tampilkan  Auto-select   MANDATE PICKER
           info       mandate            │
           terbatas       │         User pilih
                          │              │
                          └──────┬───────┘
                                 ▼
                     GOVERNANCE DASHBOARD
                     (sesuai mandate aktif)
                                 │
                         [return_to ada?]
                                 │
                         Redirect ke target
```

---

## 2. SPLASH SCREEN

**Durasi**: 2–3 detik maksimum (tidak boleh blocking)

**Yang Terjadi di Background (Paralel)**:
1. Firebase SDK init
2. Load environment config
3. Init Drift SQLite (public + governance)
4. Check connectivity status
5. Read token dari `flutter_secure_storage` (silent — tidak redirect ke login)
6. Jika token ada → silent validate di background
7. Subscribe FCM public topics (`public_alerts`)

**PENTING**: Splash TIDAK boleh redirect ke Login. Setelah init selesai → selalu ke `/p/home`.

**Visual**:
- Background warna brand NURISK (hijau gelap)
- Logo NURISK + Logo NU Peduli centered
- Progress indicator halus
- Versi aplikasi di bagian bawah

**Error Handling di Splash**:
- Jika SQLite gagal init → crash dengan pesan "Gagal memulai aplikasi. Silakan reinstall."
- Jika token validation timeout → lanjutkan ke `/p/home` (tidak redirect ke login)
- Jika offline → lanjutkan ke `/p/home` dengan public cache

---

## 2.1 PUBLIC DASHBOARD SCREEN (DEFAULT LANDING)

**URL Route**: `/p/home`  
**Auth Required**: ❌ Tidak perlu

**Yang Ditampilkan**:
- KPI bencana aktif (total insiden, personel, korban terdampak)
- Feed kejadian terbaru
- Peringatan cuaca ekstrem
- Mini peta insiden aktif
- Shortcut: Lapor Kejadian, Peta Lengkap, Cuaca

**State di Header (Top Right)**:
```
[Belum Login]            [Sudah Login]
      ↓                        ↓
 Tombol "Masuk"         Avatar + nama user
                         + badge mandate aktif
```

**Offline State**:
- Tampilkan data dari public cache
- Banner abu-abu ringan: "Data dari cache · {waktu}"
- Tidak menampilkan error besar (user tetap bisa browsing)

---

## 3. LOGIN SCREEN

**URL Route**: `/auth/login`  
**Auth Required**: ❌

**Kapan Muncul** (BUKAN entry point):
- User tap fitur governance dari Public Layer
- User tap tab "Akun" → "Masuk"
- Deep link ke resource governance (belum login)
- Return dari Session Expired

**Return-to Parameter**:
- URL selalu membawa `?return_to={encoded_route}`
- Setelah login sukses → navigate ke `return_to` bukan dashboard
- Jika tidak ada `return_to` → navigate ke `/g/dashboard`

**UI Elements**:
| Element | ID | Perilaku |
|---------|-----|---------|
| Field Nomor HP | `field_no_hp` | Numeric keyboard, auto format +62 |
| Field Password | `field_password` | Obscure text, toggle show/hide |
| Tombol Login | `btn_login` | Disable saat loading, loading indicator |
| Link Lupa Password | `link_forgot_password` | Navigate ke Forgot Password (Sprint F2) |
| Link Daftar Relawan | `link_register` | Kembali ke public → `/p/relawan/daftar` |
| Link Kembali | `link_back` | Kembali ke Public Layer |

**States**:
```
IDLE → LOADING → SUCCESS → Mandate check → return_to atau /g/dashboard
                         → ERROR (tampilkan error message)
```

**Biometric Quick Login** (jika sudah pernah login dan biometric aktif):
- Tampilkan tombol biometric di bawah form login
- Jika biometric sukses → skip form → load sesi

---

## 4. MANDATE PICKER SCREEN

**URL Route**: `/mandate-picker`

**Kapan Muncul**:
- Setelah login berhasil dan user memiliki > 1 mandate aktif
- Ketika user ingin berpindah mandate dari Settings

**UI Elements**:
| Element | Keterangan |
|---------|-----------|
| Header | "Pilih Posisi Anda" |
| List Card | Satu card per mandate, menampilkan: Jabatan, Node, Wilayah, Periode |
| Badge status | Aktif / Mendelegasikan / Expired |
| Tombol Pilih | Di setiap card |
| Info text | "Anda dapat mengganti posisi ini kapan saja dari Pengaturan" |

**Card Content**:
```
╔════════════════════════════════════╗
║ 🏢 Koordinator Logistik             ║
║ PCNU Sidoarjo                       ║
║ Wilayah: Kab. Sidoarjo (3515)       ║
║ 01 Jan 2026 — Tidak Terbatas        ║
║                      [Pilih]        ║
╚════════════════════════════════════╝
```

**Skenario 0 Mandate**:
- Tampilkan state kosong: "Anda belum memiliki mandat aktif."
- Tombol "Hubungi Administrator"

**Skenario Mandate Expired**:
- Tandai dengan badge "Expired" (warna merah)
- Tidak bisa dipilih

---

## 5. DASHBOARD SCREEN (POST-AUTH)

**URL Route**: `/dashboard`

**Triggered By**: Setelah mandate dipilih

**Data yang Dimuat**:
- Profil singkat user
- Info mandate aktif
- Summary data sesuai role

**Offline State**:
- Tampilkan data dari cache
- Banner kuning di atas: "Data ditampilkan dari cache. Terakhir diperbarui: {waktu}"

---

## 6. SESSION EXPIRED FLOW

**Trigger**: Timer idle (30 menit default) atau 401 dari server

**Sub-alur A: Idle Timeout**
```
Timer 30 menit idle
    │
    ▼
Pause semua background activity
    │
    ▼
╔══════════════════════════╗
║    LOCK SCREEN            ║
║  ──────────────────────  ║
║  "Sesi terkunci"          ║
║  [Gunakan Biometric]      ║
║  [Masukkan PIN]           ║
╚══════════════════════════╝
    │
    ├── [Biometric/PIN OK]
    │       │
    │       ▼
    │   Resume sesi
    │       │
    │       ▼
    │   ╔═══════════╗
    │   ║ DASHBOARD ║
    │   ╚═══════════╝
    │
    └── [Lupa PIN / Biometric fail 3x]
            │
            ▼
        Full Re-Login
```

**Sub-alur B: 401 dari Server**
```
API Response 401
    │
    ▼
Coba Device Token Refresh
    │
    ├── [Berhasil]
    │       │
    │       ▼
    │   Retry request asal
    │
    └── [Gagal]
            │
            ▼
        Tampilkan dialog:
        "Sesi Anda telah berakhir.
         Silakan login kembali."
            │
            ▼
        [OK] → Login Screen
```

---

## 7. 403 FORBIDDEN FLOW

```
API Response 403
    │
    ▼
╔══════════════════════════════╗
║   PERMISSION DENIED SCREEN   ║
║  ──────────────────────────  ║
║  Ikon 🔒                     ║
║  "Anda tidak memiliki akses  ║
║   untuk halaman ini"         ║
║                               ║
║  Mandate aktif: [Jabatan]    ║
║  Node: [Nama Node]           ║
║                               ║
║  [← Kembali]  [Ganti Posisi] ║
╚══════════════════════════════╝
```

---

## 8. REGISTRATION FLOW

**URL Route**: `/register/{jenis}`

**Jenis**:
- `relawan` — Pendaftaran relawan baru
- `anggota` — Pendaftaran anggota NU

**Alur**:
```
Pilih Jenis Pendaftaran
    │
    ▼
Form Pendaftaran
  (nama, no_hp, kata_sandi, data profil)
    │
    ▼
POST /api/auth/register/{jenis}
    │
    ├── [Langsung Aktif]
    │       │
    │       ▼
    │   Login Otomatis → Dashboard
    │
    └── [Menunggu Persetujuan]
            │
            ▼
        Screen "Pendaftaran Dikirim"
        "Akun Anda sedang diverifikasi
         oleh administrator. Anda akan
         mendapat notifikasi setelah
         disetujui."
```

---

## 9. FORGOT PASSWORD FLOW (Sprint F2)

**Status**: Placeholder — endpoint belum ada di backend

```
Login Screen → "Lupa Password"
    │
    ▼
Masukkan Nomor HP
    │
    ▼
POST /api/auth/forgot-password [BELUM ADA]
    │
    ▼
Screen "OTP Dikirim"
"Kode verifikasi telah dikirim ke
 WhatsApp/SMS {masked_phone}"
    │
    ▼
Input 6-digit OTP (dengan timer 5 menit)
    │
    ▼
POST /api/auth/verify-otp [BELUM ADA]
    │
    ├── [OTP Valid]
    │       │
    │       ▼
    │   Form Reset Password
    │   (kata sandi baru + konfirmasi)
    │       │
    │       ▼
    │   POST /api/auth/reset-password [BELUM ADA]
    │       │
    │       ▼
    │   "Password berhasil diubah"
    │       │
    │       ▼
    │   Login Screen
    │
    └── [OTP Invalid / Expired]
            │
            ▼
        Tampilkan error + opsi "Kirim ulang OTP"
```

---

## 10. BIOMETRIC ENROLLMENT FLOW

**Trigger**: User pertama kali masuk ke Settings → Keamanan → Aktifkan Biometric

```
Tap "Aktifkan Biometric"
    │
    ▼
Verifikasi identitas dulu:
Masukkan PIN atau Password
    │
    ├── [Berhasil]
    │       │
    │       ▼
    │   Tampilkan prompt biometric OS
    │       │
    │       ├── [Biometric Terdaftar]
    │       │       │
    │       │       ▼
    │       │   Simpan flag di Secure Storage
    │       │   "biometric_enabled = true"
    │       │       │
    │       │       ▼
    │       │   "Biometric berhasil diaktifkan"
    │       │
    │       └── [Biometric Tidak Tersedia]
    │               │
    │               ▼
    │           "Biometric tidak tersedia di device ini"
    │
    └── [Gagal]
            │
            ▼
        Tampilkan error, tidak aktifkan biometric
```

---

## 11. STATE MACHINE — SESSION STATES

```
                 ┌──────────────────┐
                 │   UNAUTHENTICATED │ ←─────────────────────────┐
                 └──────────────────┘                            │
                         │                                       │
                    [Login sukses]                         [Logout / 401 fail]
                         │                                       │
                         ▼                                       │
                 ┌──────────────────┐                           │
                 │  AUTHENTICATED   │                           │
                 │  (no mandate)    │                           │
                 └──────────────────┘                           │
                         │                                       │
                   [Mandate dipilih]                            │
                         │                                       │
                         ▼                                       │
                 ┌──────────────────┐                           │
                 │  ACTIVE SESSION  │──[Idle timeout]──┐        │
                 └──────────────────┘                  │        │
                         │                             ▼        │
                    [401 received]             ┌──────────────┐ │
                         │                    │    LOCKED    │ │
                         ▼                    └──────────────┘ │
                 ┌──────────────────┐                  │        │
                 │  REFRESH PENDING │        [Biometric/PIN OK] │
                 └──────────────────┘                  │        │
                    │           │                       │        │
               [Refresh OK] [Refresh fail]    [ACTIVE SESSION]  │
                    │           │                                │
                    ▼           └──────────────────────────────►┘
              [ACTIVE SESSION]
```

---

*Document Status: APPROVED — Forgot Password flow pending backend implementation*
