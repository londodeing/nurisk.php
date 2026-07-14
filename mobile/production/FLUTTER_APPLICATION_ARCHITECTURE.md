# FLUTTER APPLICATION ARCHITECTURE — CONSTITUTION
## Dokumen Induk (Constitution Document)
**Version**: 2.0.0 | **Status**: PRODUCTION BLUEPRINT  
**Paradigma**: PUBLIC FIRST  
**Last Updated**: 2026-07-06

> **PERAN DOKUMEN INI**  
> Ini adalah satu-satunya sumber kebenaran arsitektur Flutter NURISK.  
> Seluruh 15 dokumen teknis mengacu pada konstitusi ini.  
> Jika paradigma berubah di masa depan, ubah dokumen ini — dokumen lain menyesuaikan.

---

## ARTICLE 1 — PARADIGMA FUNDAMENTAL

### 1.1 Public First, Not Auth First

NURISK Mobile adalah **aplikasi publik**. Siapapun dapat menginstall dan menggunakan fitur publik tanpa login. Login adalah konsekuensi dari keinginan menggunakan fitur operasional — **bukan entry point**.

```
PARADIGMA LAMA (SALAH):
  Splash → Login → Dashboard

PARADIGMA BARU (BENAR):
  Splash → Dashboard Publik → [Jika butuh fitur privat] → Login → Mandate → Governance
```

### 1.2 Dua Dunia Aplikasi

Aplikasi terbagi menjadi dua layer yang berdiri sendiri dan dapat berjalan independen:

| Layer | Akses | Kebutuhan | Deskripsi |
|-------|-------|-----------|-----------|
| **PUBLIC LAYER** | Semua orang | Tidak perlu login | Informasi bencana, cuaca, peta, laporan |
| **GOVERNANCE LAYER** | Login + Mandate | Token + Mandate | Operasional, approval, manajemen, command |

### 1.3 Prinsip yang Tidak Boleh Dilanggar

1. **App tidak boleh memaksa login sebelum menampilkan informasi publik**
2. **Public state harus tetap hidup meskipun user logout**
3. **Login hanya dipicu oleh user action yang memerlukan akses privat**
4. **Setelah login, redirect kembali ke halaman yang memicu login (return_to)**
5. **Satu akun dapat memiliki banyak mandate — mandate menentukan segalanya**

---

## ARTICLE 2 — LAPISAN APLIKASI

### 2.1 PUBLIC LAYER

Dapat diakses tanpa autentikasi apapun.

**Fitur Publik**:
| Fitur | API Endpoint | Deskripsi |
|-------|-------------|-----------|
| Dashboard Publik | `GET /api/public/dashboard` | KPI bencana aktif, insiden, personel |
| Daftar Kejadian | `GET /api/views/alert-insiden-baru` | Feed insiden baru |
| Detail Kejadian | `GET /api/public/incident/{id}/detail` | Detail insiden spesifik |
| Peta Kejadian | `GET /api/laporan/peta` | Koordinat laporan/insiden |
| Prakiraan Cuaca | `GET /api/weather/forecast` | Cuaca wilayah |
| Peringatan Dini (BMKG) | `GET /api/external/bmkg/gempa` | Gempa bumi terbaru |
| Cuaca Internal | `GET /api/internal/weather/*` | Current, hourly, daily, risk |
| Submit Laporan Kejadian | `POST /api/laporan` | Masyarakat lapor bencana |
| Registrasi Relawan | `POST /api/relawan/daftar` | Daftar menjadi relawan |
| Master Bencana | `GET /api/master/jenis-bencana` | Referensi jenis bencana |
| Statistik Publik | `GET /api/views/command-center-summary` | Statistik umum |

### 2.2 GOVERNANCE LAYER

Memerlukan autentikasi valid + mandate aktif.

**Fitur Governance**:
- Incident Management (create, update, escalate)
- Mission & Penugasan Management
- Assessment & Sitrep
- Posko Management
- Mobilisasi & Resource
- Aset Management
- Logistik
- Governance (Approval, Delegation, Surat)
- Command Center (data sensitif)
- Media Internal
- Notifikasi Internal
- Profil & Pengaturan

---

## ARTICLE 3 — ARSITEKTUR ROUTING

### 3.1 Router Hierarchy

```
AppRouter (Root)
    │
    ├── SplashRoute (/)
    │
    ├── PUBLIC ROUTER (/p/*)       ← Tidak perlu auth
    │   ├── /home                  ← Dashboard publik
    │   ├── /map                   ← Peta bencana
    │   ├── /incidents             ← Feed kejadian
    │   ├── /incidents/:id         ← Detail kejadian
    │   ├── /weather               ← Cuaca & peringatan
    │   ├── /lapor                 ← Submit laporan
    │   ├── /relawan/daftar        ← Registrasi relawan
    │   ├── /emergency-contacts    ← Nomor darurat
    │   ├── /donate                ← Donasi Lazisnu
    │   ├── /about                 ← Tentang NU Peduli
    │   └── /faq                   ← FAQ
    │
    ├── AUTH ROUTER (/auth/*)      ← Tidak perlu auth, hanya untuk auth flow
    │   ├── /login                 ← Login screen
    │   ├── /register              ← Pilih jenis
    │   ├── /register/:jenis       ← Form daftar
    │   ├── /register/pending      ← Menunggu approval
    │   ├── /forgot-password       ← Reset password (Sprint F2)
    │   └── /otp                   ← OTP verification
    │
    └── GOVERNANCE ROUTER (/g/*)  ← Perlu auth + mandate
        ├── /mandate-picker        ← Pilih mandate aktif
        ├── /dashboard             ← Governance dashboard
        ├── /governance/*          ← Semua fitur governance
        ├── /operasi/*             ← Semua fitur operasional
        ├── /laporan/*             ← Manajemen laporan (admin)
        ├── /aset/*                ← Manajemen aset
        ├── /logistik/*            ← Logistik
        ├── /surat/*               ← Dokumen & paraf
        ├── /media/*               ← Media internal
        ├── /notifications         ← Notifikasi internal
        ├── /profile               ← Profil user
        ├── /settings              ← Pengaturan
        └── /403                   ← Permission denied
```

### 3.2 Route Guard Chain

```
Request Route
    │
    ▼
[1] SplashGuard
    Cek: App sudah init?
    Action: Redirect ke /p/home setelah init
    │
    ▼
[2] PublicGuard
    Cek: Route di bawah /p/*?
    Action: Langsung izinkan, tidak perlu auth
    │
    ▼
[3] AuthGuard
    Cek: Token valid di Secure Storage?
    Jika: Route di bawah /g/* dan tidak ada token
    Action: Redirect ke /auth/login?return_to={route}
    │
    ▼
[4] MandateGuard
    Cek: Ada active_mandate_id?
    Jika: Route di bawah /g/* kecuali /mandate-picker
    Jika tidak ada mandate → /g/mandate-picker
    Jika 1 mandate → auto-select → lanjut
    Jika > 1 mandate → /g/mandate-picker
    │
    ▼
[5] PermissionGuard
    Cek: User punya permission untuk route ini?
    Action: Redirect ke /g/403 jika tidak
    │
    ▼
[6] FeatureGuard
    Cek: Fitur aktif di config? (Feature flag)
    Action: Redirect ke /g/403 dengan pesan "Fitur tidak tersedia"
    │
    ▼
Render Route
```

### 3.3 Deep Link Routing

```
nurisk://incident/123
    │
    ▼
DeepLinkHandler
    │
    ├── [Sudah Login + Mandate]
    │       ▼
    │   /g/operasi/insiden/123
    │
    ├── [Sudah Login, Belum Mandate]
    │       ▼
    │   /g/mandate-picker
    │   [setelah pilih mandate] → /g/operasi/insiden/123
    │
    └── [Belum Login]
            ▼
        /auth/login?return_to=/g/operasi/insiden/123
        [setelah login + mandate] → /g/operasi/insiden/123
```

**Deep Link yang Didukung**:
| Deep Link | Destinasi | Require Auth |
|-----------|-----------|-------------|
| `nurisk://incident/{id}` | Detail insiden | Governance |
| `nurisk://approval/{id}` | Detail approval | Governance |
| `nurisk://notification/{id}` | Notifikasi | Governance |
| `nurisk://map` | Peta publik | Public |
| `nurisk://report` | Lapor kejadian | Public |

---

## ARTICLE 4 — NAVIGASI

### 4.1 Public Bottom Navigation

```
Bottom Nav Publik (5 tab):
  [🏠 Beranda]  [🗺️ Peta]  [📸 Lapor]  [ℹ️ Info]  [👤 Akun]
```

| Tab | Route | Keterangan |
|-----|-------|-----------|
| Beranda | `/p/home` | Dashboard publik, feed bencana, cuaca |
| Peta | `/p/map` | Peta interaktif insiden & posko |
| Lapor | `/p/lapor` | Submit laporan kejadian (publik) |
| Info | `/p/about` | Nomor darurat, FAQ, tentang NU Peduli |
| Akun | `/p/account` | Login / profil jika sudah login |

**Catatan Tab "Akun"**:
- Jika belum login: tampilkan halaman "Login untuk akses fitur lengkap"
- Jika sudah login: tampilkan ringkasan profil + shortcut ke Governance

### 4.2 Governance Bottom Navigation

```
Bottom Nav Governance (5–6 tab, tergantung role):
  [🏠 Dashboard]  [🚨 Insiden]  [✅ Inbox]  [🔔 Notif]  [👤 Profil]
```

| Tab | Route | Role |
|-----|-------|------|
| Dashboard | `/g/dashboard` | Semua |
| Insiden | `/g/operasi/insiden` | Semua kecuali basic relawan |
| Inbox | `/g/governance/inbox` | Yang punya mandate governance |
| Notif | `/g/notifications` | Semua |
| Profil | `/g/profile` | Semua |

### 4.3 Transisi Public ↔ Governance

```
Public Nav → Governance Nav:
  User tap "Login" di tab Akun
      ↓
  Login flow
      ↓
  Mandate Picker (jika perlu)
      ↓
  Ganti Bottom Navigation ke Governance Nav
  (Public Nav disembunyikan, Governance Nav tampil)

Governance Nav → Public Nav:
  User tap "Logout" di Profil
      ↓
  Konfirmasi logout
      ↓
  Clear auth state
      ↓
  Ganti kembali ke Public Nav
  (Public state tetap hidup — data peta, feed tetap ada)
```

---

## ARTICLE 5 — STATE MANAGEMENT

### 5.1 Pemisahan State

```
AppState (Root)
    │
    ├── PublicState (PERSISTENT — tidak hilang saat logout)
    │   ├── PublicDashboardState     (KPI, feed insiden)
    │   ├── WeatherState             (cuaca, peringatan)
    │   ├── MapState                 (data peta, layer)
    │   ├── PublicIncidentState      (daftar insiden publik)
    │   └── ConnectivityState        (status jaringan)
    │
    └── AuthenticatedState (SCOPED — reset saat logout)
        ├── AuthSessionState         (token, user, expiry)
        ├── ActiveMandateState       (mandate aktif)
        ├── PermissionState          (daftar permission)
        ├── GovernanceDashboardState (dashboard governance)
        ├── InboxState               (approval inbox)
        ├── NotificationState        (notifikasi internal)
        └── [Per-feature states...]
```

### 5.2 State Lifecycle

| State | Inisialisasi | Persisten | Reset Saat |
|-------|-------------|-----------|-----------|
| PublicDashboardState | App launch | ✅ Cache | Manual refresh |
| WeatherState | App launch | ✅ Cache 30 menit | — |
| MapState | Saat buka peta | ✅ Cache | — |
| AuthSessionState | Setelah login | ✅ Secure Storage | Logout |
| ActiveMandateState | Setelah mandate dipilih | ✅ Secure Storage | Logout / Ganti Mandate |
| PermissionState | Setelah mandate | ❌ | Logout / Ganti Mandate |
| GovernanceDashboardState | Setelah mandate | ❌ | Logout |
| InboxState | Setelah mandate | ❌ | Logout |

---

## ARTICLE 6 — API ARCHITECTURE

### 6.1 Tiga Layer API

```
┌─────────────────────────────────────────────────────┐
│ PUBLIC API LAYER                                     │
│ - Tidak perlu token                                  │
│ - No Authorization header                            │
│ - Rate limited by IP                                 │
│ Contoh: /api/public/*, /api/weather/*, /api/laporan  │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ AUTHENTICATED API LAYER                              │
│ - Wajib Bearer Token                                 │
│ - Header: Authorization: Bearer {token}              │
│ Contoh: /api/auth/me, /api/v1/devices/*              │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ GOVERNANCE API LAYER                                 │
│ - Wajib Bearer Token + Mandate Context               │
│ - Header: Authorization: Bearer {token}              │
│ - Header: X-Mandate-Id: {mandate_id}   (opsional)   │
│ - Server memvalidasi scope berdasarkan mandate       │
│ Contoh: /api/governance/*, /api/v1/insiden/*         │
└─────────────────────────────────────────────────────┘
```

### 6.2 HTTP Client Configuration

| Client | Interceptors | Base URL |
|--------|-------------|---------|
| `publicApiClient` | Rate limit, error handler | `{base_url}/api` |
| `authenticatedApiClient` | Auth interceptor, token refresh | `{base_url}/api` |
| `governanceApiClient` | Auth + mandate header | `{base_url}/api` |

---

## ARTICLE 7 — AUTHENTICATION PHILOSOPHY

### 7.1 Login Bukan Entry Point

Login hanya dipicu oleh satu kondisi: **user ingin menggunakan fitur Governance**.

**Trigger Login**:
- User tap fitur yang memerlukan auth di Public Layer
- User tap tab "Akun" dan memilih "Login"
- Deep link ke resource Governance
- Session expired saat sedang di Governance Layer

**Return-to Mechanism**:
```
Setiap redirect ke Login HARUS membawa parameter:
  /auth/login?return_to={encoded_route}

Setelah login berhasil:
  if (return_to != null) → navigate ke return_to
  else → navigate ke /g/dashboard
```

### 7.2 Token Lifecycle

```
Login → Token disimpan → Validasi periodik → [Expired] → Refresh → [Gagal] → Logout
                                                                    ↓
                                                              Kembali ke Public Layer
                                                              (bukan paksa login)
```

**Logout Behaviour**:
- Hapus token dari Secure Storage
- Reset AuthenticatedState
- **JANGAN** reset PublicState
- Navigate ke Public Layer (/p/home)
- **JANGAN** paksa ke Login screen

---

## ARTICLE 8 — MANDATE PHILOSOPHY

### 8.1 Mandate Adalah Konteks Operasional

Mandate bukan bagian dari Login. Mandate adalah pemilihan **konteks kerja** setelah login.

```
Login berhasil
    ↓
Fetch mandate aktif user
    ↓
[0 mandate]         [1 mandate]       [>1 mandate]
    ↓                   ↓                 ↓
Tampilkan info      Auto-select       Mandate Picker
"Belum ada          mandate aktif     (user memilih)
 posisi aktif"           ↓
    ↓              Load permission
Akses terbatas     Load dashboard
(hanya profil,     sesuai mandate
 settings)
```

### 8.2 Mandate Determines Everything

| Yang Ditentukan | Oleh |
|----------------|------|
| Menu yang muncul | Mandate aktif |
| Dashboard widget | Mandate aktif |
| Permission | Mandate + authority |
| API scope | Mandate (territory_code) |
| Workflow yang tersedia | Mandate + function |
| Notifikasi yang diterima | Mandate (FCM topic) |

### 8.3 Mandate Switching

- Dapat dilakukan kapan saja dari Settings
- Tidak perlu re-login
- Clear semua Governance state saat mandate berganti
- Public state tidak terpengaruh

---

## ARTICLE 9 — NOTIFICATION ARCHITECTURE

### 9.1 Dua Kanal Notifikasi

| Kanal | Pengirim | Penerima | Contoh |
|-------|---------|---------|--------|
| **Public Notification** | Backend (broadcast) | Semua user + guest | Peringatan bencana, cuaca ekstrem |
| **Internal Notification** | Backend (targeted) | User login tertentu | Approval, assignment, delegation |

### 9.2 FCM Topic Strategy

| Topic | Siapa yang Subscribe | Konten |
|-------|---------------------|--------|
| `public_alerts` | Semua device | Bencana baru, peringatan BMKG |
| `weather_{territory}` | User di wilayah | Cuaca ekstrem setempat |
| `mandate_{mandate_id}` | User dengan mandate | Approval, tugas |
| `user_{user_id}` | User spesifik | Delegasi, pengumuman personal |

---

## ARTICLE 10 — OFFLINE ARCHITECTURE

### 10.1 Dua Cache Domain

| Cache Domain | Siapa | TTL | Storage |
|-------------|-------|-----|---------|
| **Public Cache** | Semua user | Lihat tabel | SQLite publik |
| **Governance Cache** | User login | Lihat tabel | SQLite private |
| **Map Cache** | Semua user | 1 jam tiles | File system |
| **Media Cache** | Sesuai context | 7 hari thumb | File system |

### 10.2 Public Cache (Persisten, tidak terhapus saat logout)

| Data | TTL |
|------|-----|
| Dashboard publik (KPI) | 15 menit |
| Daftar insiden publik | 15 menit |
| Data cuaca | 30 menit |
| Master data bencana | 7 hari |
| Data peta tiles | 1 jam |
| Peringatan BMKG | 10 menit |

### 10.3 Governance Cache (Dihapus saat logout)

| Data | TTL |
|------|-----|
| Profil user | 24 jam |
| Mandate & permission | 12 jam |
| Governance inbox | 10 menit |
| Penugasan aktif | 15 menit |
| Insiden (governance view) | 30 menit |

---

## ARTICLE 11 — SECURITY ARCHITECTURE

### 11.1 Endpoint Classification

| Endpoint Pattern | Klasifikasi | Auth Required |
|-----------------|-------------|--------------|
| `/api/public/*` | Public | ❌ |
| `/api/external/*` | Public | ❌ |
| `/api/weather/*` | Public | ❌ |
| `/api/laporan` (POST) | Public | ❌ |
| `/api/laporan/peta` | Public | ❌ |
| `/api/master/*` | Public | ❌ |
| `/api/relawan/daftar` | Public | ❌ |
| `/api/auth/login` | Public | ❌ |
| `/api/auth/register` | Public | ❌ |
| `/api/auth/me` | Authenticated | ✅ |
| `/api/auth/logout` | Authenticated | ✅ |
| `/api/v1/devices/*` | Authenticated | ✅ |
| `/api/governance/*` | Governance | ✅ + Mandate |
| `/api/v1/insiden/*` | Governance | ✅ + Mandate |
| `/api/admin/*` | Super Admin | ✅ + Role |

### 11.2 Secure Storage Map

| Data | Storage | Enkripsi |
|------|---------|----------|
| Bearer Token | `flutter_secure_storage` | AES-256 |
| Device UUID | `flutter_secure_storage` | AES-256 |
| Device Token | `flutter_secure_storage` | AES-256 |
| Active Mandate ID | `flutter_secure_storage` | AES-256 |
| PIN Hash | `flutter_secure_storage` | SHA-256 + salt |
| Biometric Key | `flutter_secure_storage` | Platform keystore |
| Public Cache | SQLite (unencrypted) | ❌ (data publik) |
| Governance Cache | SQLite (encrypted) | ✅ SQLCipher |

---

## ARTICLE 12 — BUILD ENVIRONMENT

| Variant | Public API | Auth | Behaviour |
|---------|-----------|------|-----------|
| `dev` | `http://127.0.0.1:8000/api` | Local | Full logging |
| `staging` | `https://staging.nurisk.id/api` | Staging | Error logging |
| `production` | `https://app.nurisk.id/api` | Production | Crash only |

---

## ARTICLE 13 — SPRINT SCOPE DEFINITION

| Sprint | Domain | Deliverable |
|--------|--------|------------|
| **F0** | Documentation | 15 dokumen + constitution ini |
| **F1** | Public Layer + Auth | Splash, Public Dashboard, Login, Mandate, Governance MVP |
| **F2** | Operasional | Insiden, Laporan, Posko, Penugasan |
| **F3** | Logistik + Media | Aset, Logistik, Media full |
| **F4** | Advanced | Pleno, Sitrep advanced, Desktop |

---

## ARTICLE 14 — DOCUMENT HIERARCHY

Semua dokumen teknis mengacu pada constitution ini. Urutan prioritas:

```
FLUTTER_APPLICATION_ARCHITECTURE.md (Constitution — Dokumen ini)
    │
    ├── 01_MOBILE_ARCHITECTURE.md         (Implementasi teknis)
    ├── 04_AUTHENTICATION_UI_FLOW.md      (UI state machine)
    ├── 07_ROLE_BASED_NAVIGATION.md       (Navigation per role)
    ├── 09_OFFLINE_FIRST_STRATEGY.md      (Cache strategy)
    ├── 14_DATA_FLOW.md                   (Data layer)
    │
    ├── 02_AUTHENTICATION_DOMAIN.md       (Domain detail)
    ├── 05_GOVERNANCE_DOMAIN.md           (Domain detail)
    │
    ├── 03_AUTHENTICATION_API_MAPPING.md  (API contract)
    ├── 06_GOVERNANCE_API_MAPPING.md      (API contract)
    │
    ├── 08_MOBILE_PERMISSION_MATRIX.md    (Permission matrix)
    ├── 10_SYNC_ENGINE.md                 (Sync detail)
    ├── 11_MEDIA_STRATEGY.md              (Media detail)
    ├── 12_DESIGN_SYSTEM.md               (Design tokens)
    ├── 13_SCREEN_INVENTORY.md            (Screen list)
    └── 15_EXECUTIVE_DECISION.md          (Go/No-Go)
```

---

## CHANGELOG

| Versi | Tanggal | Perubahan |
|-------|---------|-----------|
| 1.0.0 | 2026-07-06 | Initial — Auth First paradigm (DEPRECATED) |
| 2.0.0 | 2026-07-06 | **PUBLIC FIRST paradigm** — Major rewrite |

---

*Dokumen ini adalah law of the land untuk Flutter NURISK.*  
*Tidak ada keputusan arsitektur yang dapat mengesampingkan artikel-artikel di sini tanpa revisi constitution terlebih dahulu.*
