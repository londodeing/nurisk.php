# PARADIGM SHIFT REVIEW — PUBLIC FIRST
## Dokumen Review & Impact Analysis
**Version**: 1.0.0 | **Status**: FINAL  
**Tipe**: Executive Review + Impact Matrix + Revision Checklist

---

## EXECUTIVE REVIEW

### Perubahan Paradigma

| Aspek | v1.0 (Auth First) | v2.0 (Public First) |
|-------|------------------|---------------------|
| Entry Point | Login Screen | Public Dashboard |
| Login Trigger | App launch | User action butuh fitur privat |
| Guest User | Tidak didukung | Didukung penuh |
| Public Access | Tidak ada | Full Public Layer |
| Logout Result | Login screen | Kembali ke Public Layer |
| State setelah logout | Reset semua | Public State tetap hidup |
| Navigation | Satu navigation | Dua navigation (public/governance) |
| Bottom Nav | 5 tab governance | 5 tab publik + 5 tab governance |
| Route Guard | AuthGuard dulu | PublicGuard → AuthGuard → MandateGuard |
| Deep Link | Ke dashboard | Ke target spesifik (dengan auth jika perlu) |

---

## IMPACT MATRIX — 15 DOKUMEN

| Dok | Judul | Dampak | Status |
|-----|-------|--------|--------|
| 01 | MOBILE_ARCHITECTURE | 🔴 Major | Harus direvisi (routing, state, nav) |
| 02 | AUTHENTICATION_DOMAIN | 🟡 Moderate | Revisi login trigger, logout behaviour |
| 03 | AUTHENTICATION_API_MAPPING | 🟡 Moderate | Tambah public endpoints, pisahkan layer |
| 04 | AUTHENTICATION_UI_FLOW | 🔴 Major | Rewrite total — Public First flow |
| 05 | GOVERNANCE_DOMAIN | 🟢 Minor | Tetap valid, tambah context public layer |
| 06 | GOVERNANCE_API_MAPPING | 🟢 Minor | Tetap valid, tambah klasifikasi public/auth |
| 07 | ROLE_BASED_NAVIGATION | 🔴 Major | Rewrite — dua nav system, guest role |
| 08 | MOBILE_PERMISSION_MATRIX | 🟡 Moderate | Tambah baris GUEST + PUBLIC |
| 09 | OFFLINE_FIRST_STRATEGY | 🟡 Moderate | Pisahkan public/governance cache |
| 10 | SYNC_ENGINE | 🟡 Moderate | Pisahkan public/governance sync |
| 11 | MEDIA_STRATEGY | 🟢 Minor | Tambah public media access |
| 12 | DESIGN_SYSTEM | 🟡 Moderate | Tambah public nav design tokens |
| 13 | SCREEN_INVENTORY | 🔴 Major | Tambah 20+ public screens |
| 14 | DATA_FLOW | 🟡 Moderate | Tambah public data flow |
| 15 | EXECUTIVE_DECISION | 🟡 Moderate | Revisi blockers — tambah public layer |

---

## REVIEW PER DOKUMEN

---

### DOK-01: MOBILE_ARCHITECTURE.md

**Status**: 🔴 Masih Valid Sebagian — Perlu Revisi Major

**Bagian Obsolete**:
- Section 14: "Authentication Flow" — alur lama (splash→login→dashboard) SALAH
- Section 4: Routing section — hanya satu router, tidak ada public router
- Section 5: State management — tidak ada pemisahan public/auth state

**Bagian yang Harus Ditambah**:
- Public Router vs Governance Router
- Guest state management
- Public Bottom Navigation
- Dua HTTP client (publicApiClient, authenticatedApiClient)
- Public cache strategy

**Bagian yang Harus Dipindahkan**:
- Authentication Flow → ke `04_AUTHENTICATION_UI_FLOW.md`

**Dampak ke Dokumen Lain**: Menjadi panduan implementasi dari FLUTTER_APPLICATION_ARCHITECTURE.md

**Final Recommendation**: Revisi Section 3 (Architecture), 4 (DI), 6 (Routing), 14 (Auth Flow), 15 (Storage Strategy)

---

### DOK-02: AUTHENTICATION_DOMAIN.md

**Status**: 🟡 Valid Sebagian — Perlu Revisi Moderate

**Bagian Obsolete**:
- Section 2.21 "Guest User: NURISK tidak mendukung guest user" — SALAH. Public Layer adalah guest mode.

**Bagian yang Harus Ditambah**:
- Login trigger mechanism (return_to parameter)
- Logout behaviour baru (kembali ke public, bukan login screen)
- "Unauthenticated Public Session" sebagai state valid

**Bagian yang Tetap Valid**:
- Login credentials (no_hp + kata_sandi)
- Token management (Sanctum)
- Device binding
- Biometric/PIN
- Session timeout
- Role & mandate detection

**Final Recommendation**: Revisi Section 2.1 (Login trigger), 2.2 (Logout), 2.21 (Guest User)

---

### DOK-03: AUTHENTICATION_API_MAPPING.md

**Status**: 🟡 Valid Sebagian — Perlu Penambahan

**Bagian Obsolete**: Tidak ada yang obsolete.

**Bagian yang Harus Ditambah**:
- Section baru: "Public API Endpoints" (dashboard, cuaca, laporan, dll.)
- Klasifikasi tier: Public / Authenticated / Governance
- Gap baru: public search/filter incident endpoint

**Final Recommendation**: Tambah section Public API mapping di awal dokumen, tambah API classification table

---

### DOK-04: AUTHENTICATION_UI_FLOW.md

**Status**: 🔴 Obsolete — Perlu Rewrite Total

**Seluruh alur lama SALAH**. Dokumen ini menggambarkan "Splash → cek token → login" sebagai alur utama.

**Yang Harus Dihapus**: Semua alur yang dimulai dari token check ke login screen

**Yang Harus Ditulis Ulang**:
- Master flow: Splash → Public Dashboard (tanpa check token)
- Token check hanya terjadi di background (silent)
- Login flow hanya muncul saat user membutuhkan fitur governance
- Return-to mechanism
- Logout → kembali ke public (bukan login screen)
- Deep link dengan auth requirement

**Final Recommendation**: Rewrite total. Lihat FLUTTER_APPLICATION_ARCHITECTURE.md Article 3 & 7.

---

### DOK-05: GOVERNANCE_DOMAIN.md

**Status**: 🟢 Masih Valid — Perlu Penambahan Minor

**Bagian yang Tetap Valid**: Semua konten governance — mandate, approval, delegation, dll.

**Bagian yang Harus Ditambah**:
- Catatan eksplisit: Governance Domain hanya aktif setelah login + mandate
- Transition dari Public Layer ke Governance Layer
- Implikasi terhadap user yang logout: kembali ke public, bukan stuck di lock screen

**Final Recommendation**: Tambah intro section yang menjelaskan Governance Layer dalam konteks Public First

---

### DOK-06: GOVERNANCE_API_MAPPING.md

**Status**: 🟢 Masih Valid — Penambahan Klasifikasi

**Bagian yang Harus Ditambah**:
- Kolom "API Layer" (Public/Authenticated/Governance) di setiap endpoint table
- Catatan bahwa governance endpoints memerlukan Bearer Token + Mandate context

**Final Recommendation**: Tambah API Layer column, minor additions

---

### DOK-07: ROLE_BASED_NAVIGATION.md

**Status**: 🔴 Obsolete Sebagian — Perlu Rewrite Navigasi

**Bagian Obsolete**:
- Seluruh Bottom Navigation section — tidak mempertimbangkan GUEST/PUBLIC navigation
- Dashboard Widget section — tidak ada public dashboard

**Bagian yang Harus Ditulis Ulang**:
- Section 2: Bottom Navigation — pisahkan PUBLIC nav vs GOVERNANCE nav
- Tambah "Guest/Public User" sebagai role pertama
- Quick Actions untuk Public user

**Bagian yang Tetap Valid**:
- Permission guard per route
- Mandate-based menu augmentation
- Drawer content per role

**Final Recommendation**: Rewrite Section 2 (Bottom Navigation), tambah Public User section

---

### DOK-08: MOBILE_PERMISSION_MATRIX.md

**Status**: 🟡 Valid Sebagian — Perlu Penambahan

**Bagian yang Harus Ditambah**:
- Baris role baru: GUEST (Public User)
- Kolom permission untuk Public Layer features
- Klasifikasi fitur: Public vs Governance

**Final Recommendation**: Tambah kolom GUEST dan section Public Feature permissions

---

### DOK-09: OFFLINE_FIRST_STRATEGY.md

**Status**: 🟡 Valid Sebagian — Perlu Revisi Moderate

**Bagian Obsolete**:
- "Guest User: NURISK tidak mendukung guest user" — SALAH
- Strategy tidak memisahkan Public cache vs Governance cache

**Bagian yang Harus Ditambah**:
- Public cache domain (persisten, tidak terhapus saat logout)
- Governance cache domain (dihapus saat logout)
- Bootstrap sync hanya untuk governance (public tidak perlu bootstrap)

**Final Recommendation**: Tambah Section "Public Cache Strategy", pisahkan cache domains

---

### DOK-10: SYNC_ENGINE.md

**Status**: 🟡 Valid Sebagian — Revisi Moderate

**Bagian yang Harus Ditambah**:
- Public Sync: sync data publik (insiden, cuaca) tanpa auth
- Governance Sync: sync data operasional (hanya jika login)
- Pisahkan SyncEngine menjadi PublicSyncEngine + GovernanceSyncEngine

**Final Recommendation**: Tambah section Public Sync, pisahkan scope

---

### DOK-11: MEDIA_STRATEGY.md

**Status**: 🟢 Masih Valid — Minor Addition

**Bagian yang Harus Ditambah**:
- Media publik (foto laporan publik) tidak perlu presigned URL jika bucket public
- Media governance tetap menggunakan presigned URL

**Final Recommendation**: Tambah klasifikasi public/private media access

---

### DOK-12: DESIGN_SYSTEM.md

**Status**: 🟡 Valid Sebagian — Penambahan Moderate

**Bagian yang Harus Ditambah**:
- Public Bottom Navigation design tokens
- Guest/Unauthenticated state design
- "Login untuk akses lebih" CTA component
- Public vs Governance color context differences

**Final Recommendation**: Tambah Section "Public Layer Components"

---

### DOK-13: SCREEN_INVENTORY.md

**Status**: 🔴 Tidak Lengkap — Perlu Penambahan Major

**Bagian yang Hilang** (seluruhnya):
- Public screens (20+ screens baru)
- Splash screen paradigma baru
- Public Home, Map, Lapor, Info, Account

**Bagian yang Tetap Valid**:
- Section 2 (Dashboard — perlu rename ke Governance Dashboard)
- Section 3+ (semua screen governance)

**Final Recommendation**: Tambah Section 0 "Public Screens" sebelum Section 1

---

### DOK-14: DATA_FLOW.md

**Status**: 🟡 Valid Sebagian — Penambahan Moderate

**Bagian yang Harus Ditambah**:
- Public Data Flow (tanpa auth)
- Dua HTTP client dalam diagram
- Public cache vs Governance cache dalam diagram storage

**Final Recommendation**: Tambah Section "Public Data Flow" sebelum Section 2

---

### DOK-15: EXECUTIVE_DECISION.md

**Status**: 🟡 Valid Sebagian — Perlu Update Blockers

**Blocker yang Tetap Valid**: B-01 sampai B-06 masih valid

**Yang Harus Ditambah**:
- Blocker baru: Public Layer API completeness check
- Readiness check untuk Public Dashboard endpoint
- Paradigm shift acknowledgment

**Final Recommendation**: Tambah section "Public Layer Readiness" di blockers

---

## CHECKLIST REVISI PER DOKUMEN

### ✅ FLUTTER_APPLICATION_ARCHITECTURE.md (BARU)
- [x] Paradigma Public First terdefinisi
- [x] Dua layer aplikasi (Public + Governance)
- [x] Route hierarchy (Public Router + Governance Router)
- [x] Route Guard Chain (6 guards)
- [x] Deep Link routing
- [x] Dua Bottom Navigation
- [x] State management split
- [x] Tiga layer API
- [x] Notification architecture
- [x] Offline architecture split
- [x] Security architecture
- [x] Document hierarchy

### 🔴 01_MOBILE_ARCHITECTURE.md
- [ ] Update Section 3: Tambah Public Layer dalam architecture
- [ ] Update Section 4: Routing → dua router
- [ ] Update Section 5: State Management → pisahkan public/auth
- [ ] Update Section 6: Tambah publicApiClient
- [ ] Update Section 14: Hapus alur auth-first, referensikan constitution
- [ ] Update Section 15: Tambah public cache storage

### 🟡 02_AUTHENTICATION_DOMAIN.md
- [ ] Revisi Section 2.1: Tambah trigger context (bukan entry point)
- [ ] Revisi Section 2.2: Logout → kembali ke public
- [ ] Revisi Section 2.21: Guest user = Public Layer user (bukan tidak didukung)
- [ ] Tambah Section 2.22: Return-to mechanism

### 🟡 03_AUTHENTICATION_API_MAPPING.md
- [ ] Tambah Section A: Public API endpoints (di atas semua section lain)
- [ ] Update setiap endpoint: tambah kolom "API Layer"
- [ ] Tambah gap baru: public incident filter endpoint

### 🔴 04_AUTHENTICATION_UI_FLOW.md
- [ ] Rewrite total Master Flow Diagram (Public First)
- [ ] Rewrite Splash Screen section
- [ ] Tambah Public Dashboard sebagai default landing
- [ ] Rewrite Login screen section (bukan entry point)
- [ ] Tambah Return-to flow
- [ ] Rewrite Logout flow (→ public, bukan login)
- [ ] Rewrite Session Expired flow (→ public jika tidak ada action penting)

### 🟢 05_GOVERNANCE_DOMAIN.md
- [ ] Tambah intro: "Governance Layer diakses setelah Public First"
- [ ] Revisi Section offline behaviour: logout → public

### 🟢 06_GOVERNANCE_API_MAPPING.md
- [ ] Tambah kolom API Layer di setiap endpoint table

### 🔴 07_ROLE_BASED_NAVIGATION.md
- [ ] Tambah Role: GUEST/Public User sebagai role pertama
- [ ] Rewrite Section 2: Dua Bottom Navigation (public + governance)
- [ ] Tambah Section: Public Drawer Menu
- [ ] Update transition logic Public → Governance Nav

### 🟡 08_MOBILE_PERMISSION_MATRIX.md
- [ ] Tambah kolom GU (Guest/Public User) di semua tabel
- [ ] Tambah Section 0: Public Layer Permissions
- [ ] Update legenda: tambah simbol publik

### 🟡 09_OFFLINE_FIRST_STRATEGY.md
- [ ] Revisi Section 2.3: public data adalah yang boleh offline (bukan hanya governance)
- [ ] Tambah Section: Public Cache Domain
- [ ] Pisahkan Table menjadi Public Data vs Governance Data
- [ ] Update Bootstrap Sync: hanya untuk governance

### 🟡 10_SYNC_ENGINE.md
- [ ] Tambah Section: Public Sync Engine (tanpa auth)
- [ ] Pisahkan scope sync: public vs governance
- [ ] Update bootstrap: governance only

### 🟢 11_MEDIA_STRATEGY.md
- [ ] Tambah Section: Public Media Access (foto laporan publik)
- [ ] Klasifikasi presigned URL: hanya untuk private media

### 🟡 12_DESIGN_SYSTEM.md
- [ ] Tambah Section 20: Public Bottom Navigation tokens
- [ ] Tambah Section 21: Unauthenticated / Guest UI states
- [ ] Tambah Section 22: "Login for More" CTA component

### 🔴 13_SCREEN_INVENTORY.md
- [ ] Tambah Section 0: Public Screens (20+ screens)
- [ ] Rename Section 2+ menjadi "GOVERNANCE SCREENS"
- [ ] Update sprint F1 scope: public screens masuk F1

### 🟡 14_DATA_FLOW.md
- [ ] Tambah Section 1.5: Public Data Flow (tanpa auth)
- [ ] Update diagram arsitektur: tambah Public API Client
- [ ] Pisahkan SQLite menjadi public_db + governance_db

### 🟡 15_EXECUTIVE_DECISION.md
- [ ] Tambah blocker: Public Layer endpoints completeness
- [ ] Tambah: Paradigm shift acknowledgment
- [ ] Update Go/No-Go criteria: public screens harus siap di F1

---

## FINAL FLUTTER PRODUCTION BLUEPRINT

### Layer Architecture (Final)
```
┌─────────────────────────────────────────────┐
│  PUBLIC LAYER (No Auth)                      │
│  Home, Map, Kejadian, Cuaca, Lapor, Info     │
│  API: /api/public/*, /api/weather/*, dll     │
│  Cache: SQLite Public (persisten)            │
└─────────────────────────────────────────────┘
           ↕ (Seamless transition)
┌─────────────────────────────────────────────┐
│  AUTH ROUTER (Transisi)                      │
│  Login, Register, Forgot Password            │
│  Mandate Picker                              │
└─────────────────────────────────────────────┘
           ↕
┌─────────────────────────────────────────────┐
│  GOVERNANCE LAYER (Auth + Mandate)           │
│  Dashboard, Insiden, Approval, Command       │
│  API: /api/governance/*, /api/v1/*           │
│  Cache: SQLite Governance (clear on logout)  │
└─────────────────────────────────────────────┘
```

### Route Guard Matrix (Final)

| Guard | Trigger | Action |
|-------|---------|--------|
| SplashGuard | App launch | Init → /p/home |
| PublicGuard | /p/* routes | Allow all |
| AuthGuard | /g/* routes (no token) | /auth/login?return_to= |
| MandateGuard | /g/* (no mandate) | /g/mandate-picker |
| PermissionGuard | /g/* (no permission) | /g/403 |
| FeatureGuard | Feature flag off | /g/403 "Fitur tidak tersedia" |

### State Architecture (Final)

```
Riverpod Provider Tree:

keepAlive: TRUE (Public — persisten)
  publicDashboardProvider
  weatherProvider
  mapProvider
  publicIncidentProvider
  connectivityProvider

keepAlive: TRUE (Auth Session)
  authSessionProvider
  activeMandateProvider
  permissionProvider

keepAlive: FALSE (Scoped — auto-disposed)
  governanceDashboardProvider
  inboxProvider
  notificationProvider
  [feature-specific providers]
```

### Navigation Architecture (Final)

```
App Shell
  ├── IF user.isPublic:
  │     PublicShell (PublicBottomNav)
  │       ├── PublicHomeRoute
  │       ├── PublicMapRoute
  │       ├── PublicLaporRoute
  │       ├── PublicInfoRoute
  │       └── PublicAccountRoute
  │
  └── IF user.isAuthenticated:
        GovernanceShell (GovernanceBottomNav)
          ├── GovernanceDashboardRoute
          ├── OperasiRoute
          ├── InboxRoute
          ├── NotificationRoute
          └── ProfileRoute
```

### API Client Architecture (Final)

```
DioClient
  ├── publicClient (no interceptors except error handling)
  │     → For all /api/public/*, /api/weather/*
  │
  ├── authClient (auth interceptor + token refresh)
  │     → For /api/auth/*, /api/v1/devices/*
  │
  └── governanceClient (auth + mandate + error interceptors)
        → For /api/governance/*, /api/v1/*
```

---

*Dokumen Review ini bersifat final. Checklist per-dokumen di atas adalah work order untuk revisi Sprint F0.5.*
