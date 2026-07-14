# RUNTIME ARCHITECTURE — NURISK Application Runtime Platform

> Konstitusi runtime aplikasi. Berlaku untuk seluruh domain selama siklus hidup NURISK.

---

## I. LAYER ARCHITECTURE

```
┌────────────────────────────────────────────────────────────────────────────┐
│                          DOMAIN LAYER                                      │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐  │
│  │  Public  │ │  Map     │ │  Lapor   │ │Governance│ │  Volunteer       │  │
│  │ Dashboard│ │ (COP)    │ │(Report)  │ │          │ │  Logistics       │  │
│  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘ │  Assessment      │  │
│       │            │            │            │       │  AI Assistant    │  │
│       │            │            │            │       └──────────────────┘  │
│       └────────────┴────────────┴────────────┴──────────────────┘          │
│                                  │                                         │
│                          ════════╪══════════════════════ DOMAIN BOUNDARY   │
│                                  ▼                                         │
│  ┌────────────────────────────────────────────────────────────────────────┐│
│  │                     APPLICATION RUNTIME PLATFORM ★★★                    ││
│  │                                                                         ││
│  │  ┌──────────────────────────────────────────────────────────────────┐  ││
│  │  │  SERVICES LAYER  (Riverpod + Business Logic)                     │  ││
│  │  │                                                                  │  ││
│  │  │  NavigationService   PermissionService   SessionService          │  ││
│  │  │  AuthService         ThemeService                                │  ││
│  │  └──────────────────────────┬───────────────────────────────────────┘  ││
│  │                             │                                           ││
│  │  ┌──────────────────────────▼───────────────────────────────────────┐  ││
│  │  │  PLATFORM LAYER  (Plugin Abstractions — never imports directly)   │  ││
│  │  │                                                                  │  ││
│  │  │  MediaService    GeoService                                     │  ││
│  │  │  NotificationService  StorageService  ConnectivityService       │  ││
│  │  └──────────────────────────┬───────────────────────────────────────┘  ││
│  │                             │                                           ││
│  │  ┌──────────────────────────▼───────────────────────────────────────┐  ││
│  │  │  INFRASTRUCTURE LAYER  (Data + Storage)                          │  ││
│  │  │                                                                  │  ││
│  │  │  CacheManager   OfflineQueue   BackgroundSync   SecureStorage   │  ││
│  │  │  DriftDatabase  ApiClient(dio)  FileManager                     │  ││
│  │  └──────────────────────────┬───────────────────────────────────────┘  ││
│  │                             │                                           ││
│  │  ┌──────────────────────────▼───────────────────────────────────────┐  ││
│  │  │  DIAGNOSTICS LAYER  (Observability)                               │  ││
│  │  │                                                                  │  ││
│  │  │  CrashReporter  PerformanceMonitor  BatteryMonitor              │  ││
│  │  │  MemoryMonitor  RuntimeLogger      RuntimeDashboard             │  ││
│  │  └──────────────────────────────────────────────────────────────────┘  ││
│  │                                                                         ││
│  │  ┌──────────────────────────────────────────────────────────────────┐  ││
│  │  │  RUNTIME LAYER  (Orchestration — the engine)                     │  ││
│  │  │                                                                  │  ││
│  │  │  AppRuntime         RuntimeInitializer    RuntimeState          │  ││
│  │  │  AppLifecycleService  RuntimeHealthCheck   RuntimeMetadata      │  ││
│  │  └──────────────────────────────────────────────────────────────────┘  ││
│  └────────────────────────────────────────────────────────────────────────┘│
│                                    │                                        │
│                            ════════╪══════════════ PLATFORM BOUNDARY        │
│                                    ▼                                        │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │                     NATIVE PLUGINS                                      │ │
│  │  image_picker  camera  geolocator  maplibre_gl  permission_handler    │ │
│  │  path_provider  shared_preferences  flutter_secure_storage            │ │
│  └────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────────┘
```

---

## II. DEPENDENCY RULES

### Rule 1: Domain → Services ONLY

```
✅ Domain → NavigationService.goToHome()
✅ Domain → PermissionService.requestCamera()
✅ Domain → MediaService.takePhoto()

❌ Domain → import 'package:image_picker/...'
❌ Domain → import 'package:geolocator/...'
❌ Domain → import 'package:permission_handler/...'
❌ Domain → context.go()
❌ Domain → Navigator.pop()
❌ Domain → Permission.camera.request()
```

### Rule 2: Services → Infrastructure + Platform ONLY

```
✅ Services → CacheManager.get()
✅ Services → SecureStorage.read()
✅ Services → ApiClient.post()
✅ Services → MediaService (Platform layer)
✅ Services → GeoService (Platform layer)

❌ Services → import 'package:drift/...' directly
❌ Services → import 'package:dio/...' directly
```

### Rule 3: Platform → Native Plugins ONLY

```
✅ Platform/MediaService → ImagePicker
✅ Platform/GeoService → Geolocator
✅ Platform/StorageService → path_provider

❌ Platform → Business logic
❌ Platform → Riverpod providers
❌ Platform → Navigation
```

### Rule 4: Infrastructure → Native Storage ONLY

```
✅ Infrastructure/CacheManager → drift
✅ Infrastructure/SecureStorage → flutter_secure_storage
✅ Infrastructure/ApiClient → dio

❌ Infrastructure → Navigation
❌ Infrastructure → UI
❌ Infrastructure → Business logic
```

### Rule 5: Diagnostics → Everything (read-only)

```
✅ Diagnostics/CrashReporter → captures from all layers
✅ Diagnostics/PerformanceMonitor → observes all layers

❌ Diagnostics → modifies state of other layers
```

### Rule 6: Runtime → Everything (orchestration ONLY)

```
✅ Runtime/RuntimeInitializer → initializes all layers
✅ Runtime/RuntimeHealthCheck → checks all layers
✅ Runtime/AppLifecycleService → notifies all layers

❌ Runtime → Business logic
❌ Runtime → UI rendering
```

---

## III. DIRECTORY STRUCTURE

```
lib/
├── core/
│   ├── runtime/                          ← THE ENGINE
│   │   ├── app_runtime.dart              ← Runtime singleton
│   │   ├── runtime_initializer.dart      ← Bootstrap sequence
│   │   ├── runtime_state.dart            ← Global runtime state
│   │   ├── runtime_health_check.dart     ← System health check
│   │   ├── runtime_metadata.dart         ← Version & metadata
│   │   ├── app_lifecycle_service.dart    ← Lifecycle observer
│   │   └── runtime_dashboard.dart        ← Hidden dev menu
│   │
│   ├── platform/                         ← PLUGIN ABSTRACTIONS
│   │   ├── camera_service.dart           ← ImagePicker wrapper
│   │   ├── location_service.dart         ← Geolocator wrapper
│   │   ├── gallery_service.dart          ← ImagePicker(gallery) wrapper
│   │   ├── notification_service.dart     ← Notification plugin wrapper
│   │   ├── storage_service.dart          ← Path provider wrapper
│   │   ├── connectivity_service.dart     ← Network status
│   │   └── file_service.dart             ← File I/O wrapper
│   │
│   ├── services/                         ← BUSINESS SERVICES
│   │   ├── navigation_service.dart       ← Nav centralized
│   │   ├── permission_service.dart       ← Permission centralized
│   │   ├── session_service.dart          ← Auth session
│   │   ├── auth_service.dart             ← Auth API + token
│   │   ├── theme_service.dart            ← Theme mode
│   │   └── feature_flag_service.dart     ← Feature flags
│   │
│   ├── infrastructure/                   ← DATA & STORAGE
│   │   ├── cache_manager.dart            ← SQLite cache + TTL
│   │   ├── offline_queue.dart            ← Offline report queue
│   │   ├── background_sync.dart          ← Auto-sync on reconnect
│   │   ├── secure_storage.dart           ← Token + sensitive data
│   │   ├── api_client.dart               ← Dio wrapper
│   │   ├── file_manager.dart             ← Temp file management
│   │   └── drift_database.dart           ← Drift config
│   │
│   ├── diagnostics/                      ← OBSERVABILITY
│   │   ├── crash_reporter.dart           ← Full context crash capture
│   │   ├── performance_monitor.dart      ← FPS, frame timing
│   │   ├── battery_monitor.dart          ← Battery level + drain
│   │   ├── memory_monitor.dart           ← Heap monitoring
│   │   └── runtime_logger.dart           ← Structured logging
│   │
│   ├── router/
│   │   ├── app_router.dart
│   │   └── route_paths.dart
│   ├── theme/
│   ├── splash/
│   └── api/
│
├── features/                             ← DOMAIN LAYER
│   ├── auth/
│   ├── profile/
│   ├── governance/
│   └── public/
│       ├── dashboard/
│       ├── map/
│       ├── report/
│       ├── weather/
│       └── warning/
│
└── main.dart                             ← ~15-20 baris
```

---

## IV. RUNTIME INITIALIZER BOOTSTRAP

```
main()
  │
  │  WidgetsFlutterBinding.ensureInitialized()
  │  AppErrorHandler.init()     ← early crash capture
  │
  ▼
RuntimeInitializer.initialize()
  │
  ├── Phase 1: CORE (fail = block)
  │   ├── RuntimeLogger.init()
  │   ├── CrashReporter.init()
  │   └── PerformanceMonitor.init()
  │
  ├── Phase 2: STORAGE (fail = block)
  │   ├── SecureStorage.init()
  │   ├── DriftDatabase.init()
  │   └── CacheManager.init()
  │
  ├── Phase 3: NETWORK (fail = warn)
  │   ├── ApiClient.init()
  │   ├── ConnectivityService.init()
  │   └── BackgroundSync.init()
  │
  ├── Phase 4: SESSION (fail = warn)
  │   ├── SessionService.restore()
  │   ├── AuthService.restoreToken()
  │   └── FeatureFlagService.load()
  │
  ├── Phase 5: PLATFORM (fail = continue)
  │   ├── CameraService.init()
  │   ├── LocationService.init()
  │   ├── NotificationService.init()
  │   └── StorageService.init()
  │
  ├── Phase 6: SERVICES (fail = warn)
  │   ├── NavigationService.init()
  │   ├── PermissionService.init()
  │   └── ThemeService.init()
  │
  ├── Phase 7: LIFECYCLE (never fails)
  │   ├── AppLifecycleService.init()
  │   ├── BatteryMonitor.start()
  │   └── MemoryMonitor.start()
  │
  ├── Phase 8: HEALTH CHECK
  │   └── RuntimeHealthCheck.run()
  │       ├── Permission statuses
  │       ├── Network connectivity
  │       ├── Database integrity
  │       ├── API reachability
  │       └── Plugin availability
  │
  └── Returns: RuntimeState
      ├── status: ok / degraded / failed
      ├── failedComponents: List<String>
      └── health: RuntimeHealthReport

      │
      ▼
runApp(ProviderScope(child: NuriskApp()))
  │
  ▼
NuriskApp checks RuntimeState
  ├── failed → InitializationFailedScreen
  ├── degraded → App + warning banner
  └── ok → App normal
```

---

## V. RUNTIME HEALTH CHECK

```dart
class RuntimeHealthCheck {
  Future<RuntimeHealthReport> run();

  Future<HealthStatus> checkPermission(Permission p);
  Future<HealthStatus> checkNetwork();
  Future<HealthStatus> checkDatabase();
  Future<HealthStatus> checkApi();
  Future<HealthStatus> checkStorage();
}

class RuntimeHealthReport {
  final bool isHealthy;          // all critical passed
  final bool isDegraded;         // non-critical failed
  final bool hasFailures;        // critical failed
  final List<HealthCheckItem> checks;
}

class HealthCheckItem {
  final String name;
  final HealthStatus status;     // ok, warning, critical
  final String? message;
  final Duration duration;
}

enum HealthStatus { ok, warning, critical }
```

### Health Check Matrix

| Check | Phase | Failure = | On Failure |
|-------|-------|-----------|------------|
| Logger init | 1 | BLOCK | App cannot start |
| CrashReporter init | 1 | BLOCK | App cannot start |
| SecureStorage init | 2 | BLOCK | Token unreadable |
| Drift init | 2 | BLOCK | Cache unavailable |
| ApiClient init | 3 | WARN | No API access |
| Connectivity | 3 | WARN | Offline mode |
| Session restore | 4 | WARN | Must re-login |
| Feature flags | 4 | CONTINUE | Default flags |
| Camera init | 5 | CONTINUE | Camera unavailable |
| Location init | 5 | CONTINUE | GPS unavailable |
| Navigation init | 6 | WARN | Route fallback |
| Permission check | 8 | WARN | Request at runtime |

---

## VI. RUNTIME METADATA

```dart
class RuntimeMetadata {
  static AppVersion get appVersion;          // 1.0.0+1 from pubspec
  static SchemaVersion get schemaVersion;    // Drift schema version
  static ApiVersion get apiVersion;          // API contract version
  static DatabaseVersion get databaseVersion; // Local DB version
  static CacheVersion get cacheVersion;      // Cache projection version
  static ProjectionVersion get projectionVersion; // Data projection
  
  static bool isCacheStale(CacheVersion required) {
    return cacheVersion < required;
  }
  
  static Future<void> invalidateCache() {
    // Clear all caches when projection changes
  }
}
```

### Version Invalidation Rules

| Version Bump | Action |
|-------------|--------|
| `appVersion` | App update required |
| `schemaVersion` | Run Drift migration |
| `apiVersion` | Clear API response cache |
| `projectionVersion` | Clear all local caches |
| `cacheVersion` | Clear time-sensitive cache |

---

## VII. FEATURE FLAGS

```dart
enum FeatureFlag {
  cop,
  governance,
  volunteer,
  assessment,
  bmkg,
  inarisk,
  donation,
  analytics,
  aiAssistant,
  logistics,
}

class FeatureFlagService {
  static Future<void> load();        // from API or local
  static bool isEnabled(FeatureFlag flag);
  static bool isEnabledByKey(String key);
  
  // Remote override (from backend)
  static Future<void> refreshFromApi();
  
  // Local override (developer mode)
  static void setLocalOverride(FeatureFlag flag, bool value);
}
```

### Feature Flag Sources (priority order)

1. **Local override** — Developer mode, for testing
2. **Remote config** — Backend API, for phased rollout
3. **Build config** — `.env` or compile-time, for release management
4. **Default** — Hardcoded defaults

---

## VIII. RUNTIME DASHBOARD

Hidden developer menu — diakses dari Profile screen (tap logo 5x atau long-press).

```
┌──────────────────────────────────────────────┐
│           RUNTIME DASHBOARD                    │
│                                                │
│  ┌──────────────────────────────────────────┐  │
│  │  SYSTEM STATUS                           │  │
│  │  ● App Runtime:  OK                    │  │
│  │  ● Navigation:   ACTIVE                │  │
│  │  ● API:          REACHABLE (230ms)     │  │
│  │  ● Database:     OK (3.2MB)            │  │
│  │  ● Session:      AUTHENTICATED          │  │
│  └──────────────────────────────────────────┘  │
│                                                │
│  ┌──────────────────────────────────────────┐  │
│  │  PLUGIN STATUS                           │  │
│  │  ● Camera:        AVAILABLE            │  │
│  │  ● GPS:           AVAILABLE (3 sats)   │  │
│  │  ● Gallery:       AVAILABLE            │  │
│  │  ● Notification:  GRANTED              │  │
│  │  ● Storage:       OK (2.1GB free)      │  │
│  │  ● MapLibre:      ACTIVE (30 FPS)      │  │
│  └──────────────────────────────────────────┘  │
│                                                │
│  ┌──────────────────────────────────────────┐  │
│  │  PERFORMANCE                             │  │
│  │  ● FPS:          58                      │  │
│  │  ● Memory:       128MB / 2GB            │  │
│  │  ● Battery:      72% (drain: 3%/hr)     │  │
│  │  ● Cold Start:   1.8s                   │  │
│  │  ● Frame Drop:   0.3%                   │  │
│  └──────────────────────────────────────────┘  │
│                                                │
│  ┌──────────────────────────────────────────┐  │
│  │  VERSION                                 │  │
│  │  ● App:          1.0.0+1                │  │
│  │  ● Schema:       2                       │  │
│  │  ● API:          v3.2                    │  │
│  │  ● Projection:   5                       │  │
│  │  ● Flutter:      3.31.0                  │  │
│  │  ● Dart:         3.12.2                  │  │
│  │  ● Kotlin:       2.3.20                  │  │
│  │  ● AGP:          9.0.1                   │  │
│  └──────────────────────────────────────────┘  │
│                                                │
│  ┌──────────────────────────────────────────┐  │
│  │  FEATURE FLAGS                           │  │
│  │  [x] COP                                 │  │
│  │  [x] Governance                          │  │
│  │  [ ] Volunteer (coming Q3)              │  │
│  │  [x] Assessment                          │  │
│  │  [x] BMKG Integration                    │  │
│  │  [ ] InaRISK (planned)                  │  │
│  │  [ ] Donation                            │  │
│  │  [x] Analytics                           │  │
│  │  [ ] AI Assistant                        │  │
│  │  [ ] Logistics                           │  │
│  └──────────────────────────────────────────┘  │
│                                                │
│  [ FORCE CRASH ] [ CLEAR CACHE ] [ LOGOUT ]    │
└──────────────────────────────────────────────┘
```

---

## IX. main.dart (Target)

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/runtime/app_runtime.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import 'package:nurisk_mobile/core/runtime/runtime_dashboard.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final runtime = await RuntimeInitializer.initialize();

  runApp(
    ProviderScope(
      overrides: [
        runtimeStateProvider.overrideWithValue(runtime),
      ],
      child: const NuriskApp(),
    ),
  );
}

class NuriskApp extends ConsumerWidget {
  const NuriskApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final runtime = ref.watch(runtimeStateProvider);

    if (runtime.status == RuntimeStatus.failed) {
      return MaterialApp(
        home: InitializationFailedScreen(runtime.health),
      );
    }

    return MaterialApp.router(
      routerConfig: ref.watch(appRouterProvider),
      title: 'NURISK',
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: ThemeMode.system,
      localizationsDelegates: [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: [
        const Locale('id', 'ID'),
        const Locale('en', 'US'),
      ],
      locale: const Locale('id', 'ID'),
      builder: (context, child) {
        return RuntimeDashboardOverlay(child: child!);
      },
    );
  }
}
```

---

## X. ENFORCEMENT & CODE REVIEW

### Automated Lint Rules

```yaml
# analysis_options.yaml
rules:
  # Domain → Services layer enforcement
  # (custom lint rule or convention check)
  
  # No direct plugin imports in domain
  # (will be enforced via custom lint)
  
  # No Navigator calls outside navigation_service.dart
  # (will be enforced via custom lint)
```

### Code Review Checklist

Untuk setiap PR, periksa:

- [ ] Tidak ada `import 'package:image_picker'` di `features/`
- [ ] Tidak ada `import 'package:geolocator'` di `features/`
- [ ] Tidak ada `import 'package:permission_handler'` di `features/`
- [ ] Tidak ada `import 'package:dio'` di `features/`
- [ ] Tidak ada `import 'package:drift'` di `features/`
- [ ] Tidak ada `context.go()` di widget selain via `NavigationService`
- [ ] Tidak ada `Navigator.pop()` di widget selain via `NavigationService`
- [ ] Tidak ada direct `Permission.xxx.request()` di widget
- [ ] Tidak ada empty catch blocks
- [ ] Semua catch memanggil `CrashReporter` atau `RuntimeLogger`
- [ ] Semua async gaps memiliki `mounted` check

---

## XI. ADR-001: NO JUST-IN-CASE ARCHITECTURE

### Status

ACCEPTED — berlaku sejak QA-F0. Tidak dapat diubah tanpa ADR baru.

### Context

Setiap sprint proposal cenderung membuat service/platform/infrastructure untuk antisipasi masa depan (FeatureFlagService, Dashboard, PerformanceMonitor, OfflineQueue, dll) meskipun belum ada domain yang membutuhkan.

### Decision

> **Runtime Platform hanya boleh berisi kapabilitas teknis (technical capabilities) yang benar-benar dipakai oleh minimal satu domain aplikasi yang sudah ada.**
>
> Dilarang membangun service, platform, infrastructure, atau diagnostics "untuk berjaga-jaga" tanpa pemanggil nyata.

### Enforcement

Setiap komponen runtime baru di PR harus menyertakan:

```
Pemanggil: ReportWizardScreen.takePhoto()  ← domain nyata
Alasan:   Camera crash tanpa permission    ← problem nyata
```

Jika tidak ada, PR ditolak.

### Consequences

**Positif**:
- Kode runtime tetap kecil dan terfokus
- Tidak ada dead code
- Setiap service teruji oleh domain yang memakainya
- Onboarding developer baru lebih cepat (lebih sedikit file)

**Negatif**:
- Saat domain baru butuh service yang belum ada, harus dibuat saat itu juga
- Tidak ada "buffer" infrastruktur

### When to Revisit

Aturan ini bisa dilonggarkan ketika jumlah domain > 5 dan pola service sudah stabil. Saat itu, membuat `ConnectivityService` sebelum domain membutuhkan mungkin masuk akal. Tapi untuk sekarang (0-2 domain), aturan ini ketat.

---

## ADR-002: RUNTIME TIDAK BOLEH MENGETAHUI DOMAIN

### Status

ACCEPTED — berlaku sejak QA-F0.

### Decision

> **Runtime Platform tidak boleh memiliki pengetahuan tentang domain aplikasi. Runtime hanya menyediakan API teknis. Domain yang memutuskan kapan dan bagaimana API tersebut dipanggil.**

### Contoh

✅ Benar — Domain panggil Runtime:
```dart
// Di ReportWizardScreen (domain)
final photo = await mediaService.takePhoto();
```

❌ Salah — Runtime tahu domain:
```dart
// Di Runtime — jangan pernah!
class MediaService {
  Future<File?> takePhoto() {
    if (currentScreen == 'ReportWizard') { ... } // JANGAN
  }
}
```

### Enforcement

- Runtime service tidak boleh import file dari `features/`
- Runtime service tidak boleh memiliki parameter/conditional yang spesifik ke domain tertentu
- Semua API runtime bersifat generic

---

## ADR-003: DOMAIN DILARANG AKSES PLUGIN LANGSUNG

### Status

ACCEPTED — berlaku sejak QA-F0.

### Decision

> **Domain tidak boleh memanggil native plugin atau Flutter SDK secara langsung. Semua akses harus melalui Runtime Service.**

### Contoh

❌ Domain langsung:
```dart
// Di ReportWizardScreen — JANGAN
import 'package:image_picker/image_picker.dart';
final picker = ImagePicker();
final file = await picker.pickImage(source: ImageSource.camera);
```

✅ Domain melalui Runtime:
```dart
// Di ReportWizardScreen
import 'package:nurisk_mobile/core/platform/media_service.dart';
final file = await mediaService.takePhoto();
```

### Enforcement

- Code review: tolak PR yang meng-import `image_picker`, `geolocator`, `permission_handler`, `maplibre_gl`, `go_router` (kecuali NavigationService), `dio`, `drift` di `features/`
- Custom lint rule (future): blokir import plugin di direktori `features/`

---

## XII. LOGGER ABSTRACTION

`RuntimeLogger` harus memiliki abstraction layer agar backend logging bisa diganti tanpa mengubah domain:

```dart
// Abstraksi
abstract class LogBackend {
  void debug(String message, {Map<String, dynamic>? metadata});
  void info(String message, {Map<String, dynamic>? metadata});
  void warning(String message, {Map<String, dynamic>? metadata});
  void error(String message, Object? error, StackTrace? stack, {Map<String, dynamic>? metadata});
  void fatal(String message, Object? error, StackTrace? stack, {Map<String, dynamic>? metadata});
}

// Implementasi Console (default)
class ConsoleLogBackend implements LogBackend { ... }

// Implementasi Sentry (masa depan)
class SentryLogBackend implements LogBackend { ... }

// Implementasi Firebase Crashlytics (masa depan)
class FirebaseLogBackend implements LogBackend { ... }

// RuntimeLogger adalah facade
class RuntimeLogger {
  static LogBackend _backend = ConsoleLogBackend();
  
  static void configure(LogBackend backend) {
    _backend = backend;
  }
  
  static void i(String message, {String? screen, String? feature}) {
    _backend.info(message, metadata: {'screen': screen, 'feature': feature});
  }
  // ...
}
```

**Domain tidak pernah tahu backend mana yang dipakai. Cukup panggil `RuntimeLogger.i()`.**

---

## XIII. SPRINT ROADMAP (Revised)

```
QA-F0 (Sekarang):   7 komponen — RuntimeInitializer, Logger, Lifecycle,
                    Permission, Navigation, MediaService, GeoService
                    ↓
QA-F1 (Next):       StorageService, ConnectivityService, FileService,
                    ErrorBoundary enhancement, AndroidManifest cleanup
                    ↓
QA-F2 (Next):       OfflineQueue, CacheManager, SecureStorage,
                    BackgroundSync, SQLite migration strategy
                    ↓
QA-F3 (Next):       RuntimeDashboard (dev-only), CrashReporter,
                    NotificationService
                    ↓
QA-F4 (Next):       PerformanceMonitor, BatteryMonitor,
                    MemoryMonitor, FPS optimization
```

Setiap sprint hanya membangun komponen yang **benar-benar dibutuhkan** oleh domain yang sedang dikerjakan. Tidak ada pre-building.

---

## XIII. COMPONENT REGISTRY

```
Application Runtime Platform — Component Map (QA-F0 only)

runtime/
├── runtime_initializer.dart      [RuntimeInitializer] — Bootstrap
├── runtime_state.dart            [RuntimeState]       — RuntimeState Riverpod
├── app_lifecycle_service.dart    [AppLifecycleService]— Observer pattern
└── error_boundary.dart           [ErrorBoundary]      — Error capture

platform/
├── media_service.dart            [MediaService]       — ImagePicker (camera)
└── geo_service.dart              [GeoService]         — Geolocator

services/
├── navigation_service.dart       [NavigationService]  — GoRouter wrapper
└── permission_service.dart       [PermissionService]  — permission_handler wrapper

diagnostics/
└── runtime_logger.dart           [RuntimeLogger]      — Structured logging (abstrak)

**Total QA-F0: 9 files.**

---

Komponen di luar QA-F0 akan dibuat saat domain benar-benar membutuhkan (ADR-001).
