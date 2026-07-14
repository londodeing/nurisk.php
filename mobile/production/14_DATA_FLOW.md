# NURISK MOBILE — DATA FLOW
## Document 14: Data Flow Architecture
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Platform-Wide

---

## 1. ARSITEKTUR DATA FLOW KESELURUHAN

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         FLUTTER CLIENT                                   │
│                                                                          │
│  ┌─────────────┐    ┌──────────────┐    ┌───────────────────────────┐   │
│  │  UI Layer   │    │   Domain     │    │    Data Layer             │   │
│  │             │    │              │    │                            │   │
│  │  Screen     │◄──►│  UseCase     │◄──►│  Repository (interface)   │   │
│  │  Controller │    │  Entity      │    │       ▲         ▲          │   │
│  │  (Riverpod) │    │              │    │       │         │          │   │
│  └─────────────┘    └──────────────┘    │  RemoteDS  LocalDS        │   │
│                                         │  (Dio/API) (Drift/SQL)    │   │
│                                         └───────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────┘
         │ (HTTP/REST)                          │ (Read/Write)
         ▼                                      ▼
┌────────────────────────┐          ┌──────────────────────────┐
│   LARAVEL BACKEND       │          │   SQLITE (Drift)          │
│                         │          │                           │
│  ┌─────────────────┐   │          │  cache_user_profile       │
│  │  API Controller │   │          │  cache_mandates           │
│  │  Middleware      │   │          │  cache_incidents          │
│  │  Service Layer   │   │          │  cache_notifications      │
│  │  RBAC            │   │          │  offline_queue            │
│  └─────────────────┘   │          │  media_upload_queue       │
│           │             │          │  master_data_*            │
│           ▼             │          └──────────────────────────┘
│  ┌─────────────────┐   │
│  │  MySQL Database  │   │
│  │  (auth_users,    │   │          ┌──────────────────────────┐
│  │   org_mandates,  │   │          │  SECURE STORAGE           │
│  │   org_nodes,     │   │          │                           │
│  │   incidents,     │   │          │  sanctum_token            │
│  │   media, ...)    │   │          │  device_uuid              │
│  └─────────────────┘   │          │  device_token             │
│           │             │          │  active_mandate_id        │
│           ▼             │          │  biometric_enabled        │
│  ┌─────────────────┐   │          │  session_timeout_pref     │
│  │  MinIO Storage   │   │          └──────────────────────────┘
│  │  (Media Files)   │   │
│  │  Queue Workers   │   │
│  │  (WebP/Thumb)    │   │
│  └─────────────────┘   │
└────────────────────────┘
```

---

## 2. READ DATA FLOW (GET)

```
User Action (tap, scroll, buka halaman)
    │
    ▼
Riverpod Provider / Controller
    │
    ▼
UseCase.execute()
    │
    ▼
Repository.find() / Repository.getList()
    │
    ├─────────────────────────────────────────┐
    ▼                                         ▼
LocalDataSource.read()               RemoteDataSource.fetch()
(SQLite / Drift)                     (Dio HTTP Client)
    │                                         │
    ▼                                         ▼
[Cache Hit + Not Stale]              [HTTP GET → Laravel API]
    │                                         │
    │                                         ▼
    │                               [Response JSON → Model]
    │                                         │
    │                                         ▼
    │                               LocalDataSource.upsert()
    │                               (update SQLite cache)
    │                                         │
    └────────────┬────────────────────────────┘
                 ▼
         Repository mengembalikan
         Entity ke UseCase
                 │
                 ▼
         Controller update state
                 │
                 ▼
         Riverpod rebuild UI
                 │
                 ▼
         User melihat data terbaru
```

---

## 3. WRITE DATA FLOW (POST/PUT)

### 3.1 Flow — Online

```
User Action (submit form, tap approve)
    │
    ▼
Controller.submit()
    │
    ▼
UseCase.execute(command)
    │
    ▼
Check connectivity (ConnectivityService)
    │
[Online]
    │
    ▼
RemoteDataSource.post() / .put()
(Dio HTTP Client)
    │
    ▼
Request dikirim ke Laravel API
    │
    ├── [Success 200/201]
    │       │
    │       ▼
    │   Model di-parse dari response
    │       │
    │       ▼
    │   LocalDataSource.upsert() (update cache)
    │       │
    │       ▼
    │   Return entity baru ke Controller
    │       │
    │       ▼
    │   Controller update state → UI refresh
    │
    └── [Error]
            │
            ▼
        Error di-parse (HTTP status + message)
            │
            ▼
        Controller emit error state
            │
            ▼
        UI tampilkan error message
```

### 3.2 Flow — Offline (Queue-able operations)

```
User Action (submit form)
    │
    ▼
Controller.submit()
    │
    ▼
Check connectivity → [OFFLINE]
    │
    ▼
OfflineQueueService.enqueue(operation)
(simpan ke SQLite offline_queue)
    │
    ▼
Controller update state (optimistic update)
    │
    ▼
UI tampilkan "Tersimpan. Akan dikirim saat online."
    │
    ▼
[Saat Online] → OfflineQueueProcessor.process()
    │
    ▼
RemoteDataSource.post() → Server
    │
    ├── [Success] → clear queue item → update cache
    └── [Error] → retry / failed queue
```

---

## 4. AUTHENTICATION DATA FLOW

```
App Launch
    │
    ▼
SplashController.init()
    │
    ▼
AuthRepository.checkSession()
    │
    ├── Read token from SecureStorage
    ├── Read active_mandate_id from SecureStorage
    │
    ├── [Token Ada]
    │       │
    │       ▼
    │   RemoteDataSource: GET /api/auth/me
    │       │
    │       ├── [200 OK]
    │       │       │
    │       │       ▼
    │       │   Parse UserModel → UserEntity
    │       │   Update LocalDS (cache_user_profile)
    │       │   Fetch mandates (if mandate_id saved)
    │       │   Router.go('/mandate-picker' or '/dashboard')
    │       │
    │       └── [401]
    │               │
    │               ▼
    │           DeviceTokenRefresh.attempt()
    │           [OK] → retry → [FAIL] → Login
    │
    └── [Token Tidak Ada]
            │
            ▼
        Router.go('/login')
```

---

## 5. MEDIA DATA FLOW

```
User tap [Ambil Foto]
    │
    ▼
ImagePickerService.pick()
    │
    ▼
ImageCompressorService.compress()
  (resize max 1920px, target < 1MB)
    │
    ▼
[Online?]
    │
    ├── [YES] → MediaRemoteDataSource.upload(file)
    │               POST /api/media (multipart)
    │               │
    │               ├── [201 Created]
    │               │       Parse MediaModel
    │               │       Save to LocalDS (cache)
    │               │       Return MediaEntity
    │               │
    │               └── [Error]
    │                       NetworkError → MediaUploadQueue
    │                       ValidationError → Show to user
    │
    └── [NO] → MediaUploadQueueService.enqueue()
                  Save file to local storage
                  Add to SQLite media_upload_queue
                  Show "Foto tersimpan, akan dikirim saat online"
```

---

## 6. SYNC DATA FLOW

```
SyncEngine (background isolate)
    │
    ├── Timer 15 menit → trigger DeltaSync
    │       │
    │       ▼
    │   SyncRemoteDataSource: POST /api/v1/sync
    │   Body: { last_sync_at, scope }
    │       │
    │       ▼
    │   Parse delta changes dari response
    │       │
    │       ▼
    │   For each changed entity:
    │     LocalDataSource.upsert(entity)
    │       │
    │       ▼
    │   For each deleted_id:
    │     LocalDataSource.softDelete(id)
    │       │
    │       ▼
    │   Update last_sync_timestamp
    │       │
    │       ▼
    │   Notify UI via Riverpod invalidation
    │
    ├── FCM Push received → trigger targeted sync
    │       (scope dari FCM payload)
    │
    └── Manual sync (user tap) → force full delta sync
```

---

## 7. GOVERNANCE APPROVAL DATA FLOW

```
User membuka Governance Inbox
    │
    ▼
GovernanceInboxController init
    │
    ▼
GovernanceRepository.getInbox()
    │
    ├── LocalDS: read cached inbox (stale check)
    │
    ├── RemoteDS: GET /api/governance/inbox (jika stale atau online)
    │
    └── Return list ApprovalEntity ke Controller
    │
    ▼
UI menampilkan list
    │
    ▼
User tap item → open Detail
    │
    ▼
User tap [Setujui] atau [Tolak]
    │
    ▼
Confirm Bottom Sheet ("Tambahkan catatan opsional")
    │
    ▼
[Check Online — WAJIB ONLINE]
    │
    ├── [Online]
    │       │
    │       ▼
    │   RemoteDS: POST /api/governance/approvals/{id}/approve
    │   atau     POST /api/governance/approvals/{id}/reject
    │       │
    │       ├── [200 OK]
    │       │       Update local cache (status: approved)
    │       │       Router.pop() → Inbox list
    │       │       Toast sukses
    │       │
    │       └── [Error]
    │               Toast error dengan pesan
    │
    └── [Offline]
            │
            ▼
        Dialog: "Approval memerlukan koneksi internet.
                 Pastikan Anda terhubung ke internet sebelum
                 melanjutkan."
```

---

## 8. ERROR DATA FLOW

```
HTTP Response diterima di RemoteDataSource
    │
    ▼
HTTP Interceptor (Dio Interceptor)
    │
    ├── 401 → AuthRefreshInterceptor.handle()
    │           Attempt device token refresh
    │           [OK] → Retry original request
    │           [FAIL] → Emit AuthExpiredEvent → Router.go('/login')
    │
    ├── 403 → Throw ForbiddenException
    │           UseCase catch → return Left(ForbiddenFailure)
    │           Controller → emit ForbiddenState
    │           UI → navigate to /403 or show dialog
    │
    ├── 422 → Parse errors map dari response body
    │           Throw ValidationException(errors)
    │           Controller → emit ValidationErrorState
    │           UI → show field errors
    │
    ├── 500 → Throw ServerException
    │           Controller → emit ServerErrorState
    │           UI → show "Terjadi kesalahan server" + retry
    │
    └── Network Error → Throw NetworkException
                Controller → emit NetworkErrorState
                UI → show offline banner, serve from cache
```

---

## 9. DATA FLOW DIAGRAM — SIMPLE VERSION

```
Flutter UI
    │ User Action
    ▼
Riverpod Controller (State Manager)
    │ Calls
    ▼
UseCase (Business Action)
    │ Calls
    ▼
Repository (Interface — abstrak)
    │               │
    ▼               ▼
Remote DS       Local DS
(Dio → REST)    (Drift → SQLite)
    │               │
    ▼               ▼
Laravel API     SQLite DB
    │               │
    ▼               ▼
MySQL DB        SharedPrefs
    │           SecureStorage
    ▼
MinIO (Media)
```

---

## 10. DEPENDENCY FLOW (Riverpod Providers)

```
environmentProvider (app config, base URL)
    │
    ▼
dioProvider (HTTP client + interceptors)
    │
    ▼
remoteDataSourceProvider
    │
    ▼
localDataSourceProvider (Drift database)
    │
    ▼
repositoryProvider
    │
    ▼
useCaseProvider
    │
    ▼
controllerProvider (Riverpod AsyncNotifier)
    │
    ▼
UI (ConsumerWidget)
```

---

*Document Status: APPROVED*
