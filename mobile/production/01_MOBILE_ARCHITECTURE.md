# NURISK MOBILE — ARCHITECTURE BLUEPRINT
## Document 01: Mobile Architecture
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Platform-Wide  
**Author**: Enterprise Mobile Solution Architect  
**Last Updated**: 2026-07-06

---

## 1. TUJUAN APLIKASI

NURISK Mobile adalah **client application** dari NURISK ERP Disaster Management System yang dioperasikan oleh NU Peduli. Aplikasi ini berfungsi sebagai antarmuka field-level bagi seluruh aktor organisasi — mulai dari relawan lapangan hingga Ketua PWNU — untuk mengakses, memantau, dan mengoperasikan seluruh fungsi governance dan operasional bencana secara mobile.

**Prinsip Utama:**
- Flutter hanya berfungsi sebagai **presentation layer** (client only)
- Semua **business logic** berada di backend Laravel
- Flutter **tidak boleh** memiliki business rule sendiri
- Semua keputusan domain (validasi, approval, escalation) diproses di server

---

## 2. TARGET PLATFORM

### 2.1 Platform Prioritas (Sprint F1 — F4)
| Platform | Min Version | Target | Justifikasi |
|----------|-------------|--------|-------------|
| **Android** | Android 8.0 (API 26) | Android 12+ | >85% pengguna NU berbasis Android |
| **iOS** | iOS 14.0 | iOS 16+ | Ketua/Koordinator tier atas menggunakan iPhone |

### 2.2 Platform Future (Post Sprint F4)
| Platform | Status | Catatan |
|----------|--------|---------|
| **Desktop (Windows)** | Planned | Command Center & Admin screen |
| **Tablet (iPad/Android)** | Planned | Dashboard & peta untuk Posko |

---

## 3. ARSITEKTUR APLIKASI

### 3.1 Pilihan Arsitektur: **Feature-First + Clean Architecture Hybrid**

**Keputusan**: Hybrid Feature-First dengan Clean Architecture per domain.

**Justifikasi**:
- NURISK memiliki domain yang sangat jelas: Auth, Governance, Operasi, Media, Logistik
- Feature-First memungkinkan tim bekerja paralel per domain
- Clean Architecture per fitur memastikan testability dan separation of concerns
- Cocok untuk ERP yang berkembang secara incremental

### 3.2 Layer Structure
```
lib/
├── core/                          # Shared infrastructure
│   ├── api/                       # HTTP client, interceptors
│   ├── auth/                      # Token storage, session manager
│   ├── error/                     # Error models, exception handlers
│   ├── network/                   # Connectivity, offline detector
│   ├── storage/                   # Local storage abstractions
│   ├── router/                    # App routing configuration
│   └── theme/                     # Design system tokens
│
├── features/
│   ├── authentication/
│   │   ├── data/
│   │   │   ├── datasources/       # Remote (API) + Local (cache)
│   │   │   ├── models/            # JSON serializable DTOs
│   │   │   └── repositories/      # Interface implementations
│   │   ├── domain/
│   │   │   ├── entities/          # Pure domain objects
│   │   │   ├── repositories/      # Abstract contracts
│   │   │   └── usecases/          # Single-responsibility actions
│   │   └── presentation/
│   │       ├── controllers/       # State management (Riverpod)
│   │       └── screens/           # UI screens only
│   │
│   ├── governance/                # Same structure
│   ├── operasi/                   # Same structure
│   ├── media/                     # Same structure
│   └── logistik/                  # Same structure
│
└── shared/                        # Cross-feature widgets & utilities
    ├── widgets/
    └── utils/
```

---

## 4. STATE MANAGEMENT

### 4.1 Pilihan: **Riverpod 2.x (Riverpod Annotations)**

**Keputusan**: Riverpod sebagai state management utama.

**Justifikasi**:
| Kriteria | Riverpod | BLoC | GetX |
|----------|----------|------|------|
| Testability | ✅ Excellent | ✅ Excellent | ⚠️ Moderate |
| Compile-time safety | ✅ Full | ⚠️ Partial | ❌ Runtime |
| Complexity | ⚠️ Moderate | High | Low |
| Enterprise readiness | ✅ | ✅ | ❌ |
| Dependency Injection | ✅ Built-in | ❌ Perlu GetIt | ❌ |
| Code generation | ✅ | ⚠️ | ❌ |

**Provider Hierarchy**:
- `AsyncNotifierProvider` — untuk async data dengan lifecycle jelas (profil, mandate)
- `NotifierProvider` — untuk state yang sinkron (form state, filter)
- `StreamProvider` — untuk real-time (notifikasi, sync status)
- `FutureProvider` — untuk data yang di-fetch sekali (master data)

---

## 5. DEPENDENCY INJECTION

Menggunakan **Riverpod sebagai DI container** native. Tidak memerlukan package tambahan (GetIt, Injectable).

```
# Provider registration flow:
httpClientProvider → apiClientProvider → repositoryProvider → usecaseProvider → controllerProvider
```

**Scoping**:
- `@riverpod` — auto-disposed (screen-level)
- `@Riverpod(keepAlive: true)` — persistent (auth session, app config)

---

## 6. ROUTING & NAVIGATION

### 6.1 Package: **go_router 14.x**

**Justifikasi**: Official recommendation dari Flutter team. Mendukung deep link, nested navigation, redirect guard, dan typed routes.

### 6.2 Navigation Structure
```
/                      → Splash Screen
/login                 → Login Screen
/mandate-picker        → Mandate Selection
/dashboard             → Main Dashboard (protected)
  /governance
    /mandates
    /delegations
    /approvals/:id
  /operasi
    /incidents/:id
    /missions/:id
  /media
  /notifications
/settings
/profile
```

### 6.3 Route Guards
- `authGuard` — redirect ke `/login` jika tidak ada valid token
- `mandateGuard` — redirect ke `/mandate-picker` jika belum pilih mandate aktif
- `permissionGuard` — redirect ke `403` screen jika tidak punya permission
- `onboardingGuard` — redirect ke onboarding jika first launch

---

## 7. DEEP LINK STRATEGY

| Deep Link | Tujuan | Kondisi |
|-----------|--------|---------|
| `nurisk://approval/{id}` | Buka detail approval | Require auth |
| `nurisk://incident/{id}` | Buka detail insiden | Require auth + mandate |
| `nurisk://notification/{id}` | Buka detail notifikasi | Require auth |
| `https://app.nurisk.id/share/{token}` | Public share (future) | No auth |

---

## 8. LOCALIZATION

- **Default Language**: Bahasa Indonesia (`id_ID`)
- **Future**: Bahasa Jawa (`jv_ID`) untuk internal NU
- Package: `flutter_localizations` + `intl`
- Format tanggal: `dd MMMM yyyy` (Indonesia)
- Format waktu: `HH:mm WIB/WITA/WIT` sesuai timezone user

---

## 9. OFFLINE STRATEGY

Lihat dokumen lengkap: `09_OFFLINE_FIRST_STRATEGY.md`

**Prinsip Utama**:
- **Read-first cache**: Data dibaca dari local cache, diperbarui dari server di background
- **Write queue**: Semua operasi tulis yang gagal masuk queue untuk retry otomatis
- **Online-only gates**: Approval, Signature, dan Authentication tidak pernah di-offline

**Local Storage Stack**:
| Layer | Package | Kegunaan |
|-------|---------|---------|
| Secure | `flutter_secure_storage` | Token, credentials |
| Structured | `drift` (SQLite) | Cache model, queue |
| Preferences | `shared_preferences` | Settings, flags |
| Files | `path_provider` | Media cache |

---

## 10. SYNC STRATEGY

Lihat dokumen lengkap: `10_SYNC_ENGINE.md`

**Sync Modes**:
1. **Bootstrap Sync** — first-time full sync saat install/login
2. **Delta Sync** — periodik setiap 15 menit (background)
3. **Push Sync** — triggered oleh notifikasi server (FCM)
4. **Manual Sync** — user-initiated pull-to-refresh

---

## 11. ERROR HANDLING

### 11.1 Error Classification
| Code | Tipe | Perilaku Flutter |
|------|------|-----------------|
| 400 | Validation Error | Tampilkan field error di form |
| 401 | Unauthorized | Trigger refresh token → login |
| 403 | Forbidden | Tampilkan Permission Denied screen |
| 404 | Not Found | Tampilkan empty state |
| 422 | Unprocessable | Tampilkan validation message |
| 429 | Rate Limited | Tampilkan "Terlalu banyak permintaan" + countdown |
| 500 | Server Error | Tampilkan error screen + retry |
| Network | No Connection | Tampilkan offline banner + cache |

### 11.2 Error Response Contract
Semua API error mengikuti format:
```json
{
  "success": false,
  "message": "Pesan error dalam Bahasa Indonesia",
  "errors": { "field": ["Pesan validasi"] }
}
```

---

## 12. CRASH REPORTING

**Primary**: **Firebase Crashlytics**
- Automatic crash reporting
- Non-fatal error reporting (handled exceptions)
- User identifier (hashed user ID, no PII)

**Secondary**: **Firebase Performance Monitoring**
- Network request tracing
- Screen render time

**Privacy**: Tidak ada PII yang dikirim ke Crashlytics. User diidentifikasi dengan hash one-way dari `id_pengguna`.

---

## 13. BUILD VARIANT

| Variant | API Base URL | Logging | Analytics | Crashlytics | Notes |
|---------|-------------|---------|-----------|-------------|-------|
| `dev` | `http://127.0.0.1:8000/api` | Verbose | Off | Off | Local development |
| `staging` | `https://staging.nurisk.id/api` | Info | Off | On | QA & UAT |
| `production` | `https://app.nurisk.id/api` | Error only | On | On | Live deployment |

**Environment File**: `.env.dev`, `.env.staging`, `.env.prod` — dibaca via `flutter_dotenv` pada build time.

---

## 14. AUTHENTICATION FLOW

```
App Launch
    ↓
Check Secure Storage (token + expiry)
    ↓
  [Token Ada]──────────────────────────────[Token Tidak Ada]
    ↓                                             ↓
Validate Token (GET /auth/me)              Login Screen
    ↓                                             ↓
  [Valid]              [Expired]            Masukkan no_hp + password
    ↓                    ↓                        ↓
Load Mandate        Refresh Token           POST /auth/login
    ↓               (device_uuid)                 ↓
  [Mandate Ada]         ↓                   Store Token (Secure)
    ↓              [Refresh OK]                   ↓
Dashboard           Load Session           Fetch /auth/me + mandate
    ↓              [Refresh Fail]                 ↓
  [Mandate > 1]       Login Screen         Mandate Picker (jika > 1)
    ↓                                            ↓
Mandate Picker                             Dashboard
```

---

## 15. PUSH NOTIFICATION STRATEGY

**Service**: Firebase Cloud Messaging (FCM)
- **Token Management**: FCM token disimpan di server saat login, dihapus saat logout
- **Topic Subscription**: User di-subscribe ke topic berdasarkan role dan node
- **Payload Types**:

| Tipe | Trigger | Aksi di Flutter |
|------|---------|-----------------|
| `approval_requested` | Ada approval baru | Buka Governance Inbox |
| `incident_new` | Insiden baru di wilayah | Buka Incident Detail |
| `mission_assigned` | Penugasan baru | Buka Mission Detail |
| `mandate_expiring` | 7 hari sebelum expire | Tampilkan reminder |
| `sync_required` | Data baru di server | Trigger background sync |

---

## 16. STORAGE STRATEGY

| Data | Storage | Enkripsi | TTL |
|------|---------|----------|-----|
| Bearer Token | `flutter_secure_storage` | AES-256 | Sampai logout |
| Device UUID | `flutter_secure_storage` | AES-256 | Permanent |
| PIN/Biometric Key | `flutter_secure_storage` | AES-256 | Permanent |
| User Profile | `drift` SQLite | No | 24 jam |
| Mandate Data | `drift` SQLite | No | 12 jam |
| Incident Cache | `drift` SQLite | No | 1 jam |
| App Settings | `shared_preferences` | No | Permanent |

---

## 17. IMAGE CACHE STRATEGY

**Package**: `cached_network_image`
- **Cache Duration**: 7 hari untuk thumbnail, 24 jam untuk full-size
- **Presigned URL Handling**: URL dari Enterprise Media Layer bersifat temporary (15 menit). Flutter harus meng-cache URL via backend proxy atau melakukan re-fetch dari endpoint `/api/media/{id}` ketika URL expired (HTTP 403)
- **Compression**: Prioritaskan thumbnail dan WebP dari server. Jangan request full-size jika hanya perlu preview.
- **Placeholder**: Gunakan skeleton shimmer selama loading
- **Error State**: Tampilkan icon placeholder, jangan crash

---

## 18. DEPENDENCY STACK UTAMA

| Package | Versi | Fungsi |
|---------|-------|--------|
| `flutter_riverpod` | ^2.5 | State management + DI |
| `riverpod_annotation` | ^2.3 | Code generation |
| `go_router` | ^14.0 | Navigation + deep link |
| `dio` | ^5.4 | HTTP client |
| `drift` | ^2.18 | SQLite ORM |
| `flutter_secure_storage` | ^9.0 | Secure token storage |
| `firebase_messaging` | ^15.0 | Push notification |
| `firebase_crashlytics` | ^4.0 | Crash reporting |
| `cached_network_image` | ^3.3 | Image caching |
| `local_auth` | ^2.2 | Biometric/PIN |
| `connectivity_plus` | ^6.0 | Network status |
| `intl` | ^0.19 | Localization |

---

*Document Status: APPROVED FOR SPRINT F1 — Subject to review at Sprint F2 kickoff*
