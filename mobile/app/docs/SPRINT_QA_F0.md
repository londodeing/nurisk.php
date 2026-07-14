# SPRINT QA-F0 — Application Runtime Foundation

> Sprint pertama. Bangun fondasi runtime yang **benar-benar dipakai** oleh domain yang sudah ada.
> **Tidak ada pre-building "just in case".**

---

## PRINSIP

> **"Setiap komponen runtime wajib memiliki minimal satu pemanggil nyata dari domain aplikasi sebelum dibuat. Dilarang membangun service untuk berjaga-jaga."**

---

## SCOPE — 7 Komponen

Yang dibuat hanya yang domain **benar-benar butuh sekarang**:

| # | Komponen | Masalah yang Diselesaikan | Pemanggil |
|---|----------|---------------------------|-----------|
| 1 | `RuntimeLogger` | Logging tanpa metadata, tidak terstruktur | Semua service |
| 2 | `RuntimeInitializer` | Bootstrap scattered di main.dart | `main.dart` |
| 3 | `AppLifecycleService` | Aplikasi restart setelah camera/GPS, polling terus jalan | `CopMapScreen`, `WarningNotifier` |
| 4 | `PermissionService` | Camera/GPS crash karena permission tidak dicek | `ReportWizardScreen`, `CopMapScreen` |
| 5 | `NavigationService` | Back keluar aplikasi, state hilang, navigasi scattered | Semua screen |
| 6 | `MediaService` | Camera → Force Close | `ReportWizardScreen:107` |
| 7 | `GeoService` | GPS → Force Close | `ReportWizardScreen:48` |

**7 komponen. Bukan 30. Bukan 41 task.**

---

## YANG TIDAK DIBUAT DI QA-F0

| Komponen | Alasan Ditunda | Sprint Tujuan |
|----------|---------------|---------------|
| `FeatureFlagService` | Belum ada domain yang butuh fitur toggling | QA-F3 |
| `RuntimeDashboard` | Belum ada pengguna; dev-only, buat saat butuh debugging | QA-F3 |
| `PerformanceMonitor` | Tidak menghentikan crash | QA-F4 |
| `BatteryMonitor` | Tidak menghentikan crash | QA-F4 |
| `MemoryMonitor` | Tidak menghentikan crash | QA-F4 |
| `CrashReporter` | RuntimeLogger + ErrorBoundary cukup untuk sekarang | QA-F3 |
| `SecureStorage` | SharedPreferences masih mencukupi | QA-F2 |
| `OfflineQueue` | Belum ada domain yang butuh offline submission | QA-F2 |
| `BackgroundSync` | Belum ada domain yang butuh auto-sync | QA-F2 |
| `CacheManager` | Drift langsung masih mencukupi | QA-F2 |
| `ConnectivityService` | Belum ada domain yang adaptif terhadap offline | QA-F2 |
| `NotificationService` | Belum ada domain yang kirim notifikasi | QA-F3 |
| `GalleryService` | image_picker langsung masih jalan (dengan permission) | QA-F1 |
| `FileService` | dart:io langsung masih mencukupi | QA-F1 |
| `StorageService` | path_provider langsung masih mencukupi | QA-F1 |

---

## TIMELINE — 2 Minggu

### Week 1: Foundation (3 hari)

**Day 1 — Runtime Logger & Initializer**

| ID | Task | File | Effort | Acceptance |
|----|------|------|--------|------------|
| F0-01 | `RuntimeLogger` — structured logging dengan metadata screen, feature, plugin | `diagnostics/runtime_logger.dart` | 2h | `RuntimeLogger.i('Login success', screen: 'login', feature: 'auth')` menghasilkan `[auth] [login] INFO Login success` |
| F0-02 | `RuntimeInitializer` — 3-phase bootstrap (core, storage, services) | `runtime/runtime_initializer.dart` | 3h | `initialize()` → phase 1 (Logger) → phase 2 (Lifecycle) → phase 3 (Services). Return `RuntimeState`. |
| F0-03 | `RuntimeState` — status: uninitialized / ok / degraded / failed | `runtime/runtime_state.dart` | 1h | Riverpod provider. Degraded = non-critical fail. Failed = critical fail. |
| F0-04 | `ErrorBoundary` — `FlutterError.onError` + `PlatformDispatcher.onError` | `runtime/error_boundary.dart` | 2h | Semua unhandled error tercatat ke RuntimeLogger |

**Day 2 — Lifecycle + Permission**

| ID | Task | File | Effort | Acceptance |
|----|------|------|--------|------------|
| F0-05 | `AppLifecycleService` — single WidgetsBindingObserver + subscriber pattern | `runtime/app_lifecycle_service.dart` | 3h | `LifecycleObserver.onPause()`, `onResume()`. WarningNotifier pause/resume polling. Map pause/resume rendering. |
| F0-06 | `PermissionService` — unified permission untuk Camera, Location, Storage | `services/permission_service.dart` | 4h | `requestCamera()` → check → request → handle denied/permanent. `requestLocation()` → sama. `openSettings()`. Semua via permission_handler. |

**Day 3 — Navigation**

| ID | Task | File | Effort | Acceptance |
|----|------|------|--------|------------|
| F0-07 | `NavigationService` — centralized GoRouter wrapper | `services/navigation_service.dart` | 5h | `goToHome()`, `goToMap()`, `goToReport()`, `goToProfile()`, `goToLogin()`, `goToExecutive()`, `pop()`, `canPop()`. Setiap navigasi logged. Auth redirect built-in. |

### Week 2: Plugin (3 hari)

**Day 4 — Camera Service**

| ID | Task | File | Effort | Acceptance |
|----|------|------|--------|------------|
| F0-08 | `MediaService` — ImagePicker(camera) wrapper + permission. Satu service untuk semua media (camera, gallery, video menyusul di QA-F1). | `platform/media_service.dart` | 3h | `takePhoto()` → PermissionService.requestCamera() → ImagePicker → return File?. No crash on deny. |
| F0-09 | Android: FileProvider + manifest permissions | `AndroidManifest.xml`, `file_paths.xml` | 1h | Camera returns valid file path. |

**Day 5 — Location Service**

| ID | Task | File | Effort | Acceptance |
|----|------|------|--------|------------|
| F0-10 | `GeoService` — Geolocator wrapper + permission + timeout. Mencakup GPS, reverse geocoding, distance (menyusul di QA-F1). | `platform/geo_service.dart` | 3h | `getCurrentPosition()` → PermissionService.requestLocation() → Geolocator → return LatLng?. No crash on deny/disabled/timeout. |

**Day 6 — Migration + Integration**

| ID | Task | File | Effort | Acceptance |
|----|------|------|--------|------------|
| F0-11 | Rewrite `main.dart` — ~20 baris, RuntimeInitializer bootstrap | `main.dart` | 1h | `main()` → `RuntimeInitializer.initialize()` → `runApp()`. Health check → ok/degraded/failed. |
| F0-12 | Migrate `ReportWizardScreen` — replace ImagePicker + Geolocator with services | `report_wizard_screen.dart` | 3h | Camera via `MediaService.takePhoto()`. GPS via `GeoService.getCurrentPosition()`. |
| F0-13 | Migrate `CopMapScreen` — add lifecycle + permission | `cop_map_screen.dart` | 2h | Registers as `LifecycleObserver`. Map pauses on background. Permission via `PermissionService`. |
| F0-14 | Migrate `WarningNotifier` — pause polling on background | `warning_provider.dart` | 1h | Registers as `LifecycleObserver`. Timer pauses on `onPause()`, resumes on `onResume()`. |
| F0-15 | Migrate all screens — replace context.go/pop with NavigationService | 7 files | 4h | No more `context.go()` in widget files. No more `Navigator.pop()` in widget files. |
| F0-16 | Integration test: camera, GPS, navigation, lifecycle | `test/` | 4h | All flows verified. |

---

## EFFORT SUMMARY

| Week | Fokus | Task | Jam |
|------|-------|------|-----|
| 1 | Foundation (Logger, Initializer, Lifecycle, Permission, Navigation) | 7 task | 20h |
| 2 | Plugin (Camera, Location, Migration, Test) | 9 task | 22h |
| **Total** | **7 komponen, 16 task** | **16** | **42h** |

**42 jam** ≈ 5-6 hari kerja efektif untuk 1 developer.

Bandingkan dengan versi sebelumnya: 41 task, 116 jam, 5 minggu.

---

## FILE STRUCTURE (Hanya yang Dibuat)

```
lib/core/
├── runtime/
│   ├── runtime_initializer.dart      ← Bootstrapper
│   ├── runtime_state.dart            ← RuntimeState Riverpod
│   ├── error_boundary.dart           ← Error capture
│   └── app_lifecycle_service.dart    ← Lifecycle observer
│
├── platform/
│   ├── media_service.dart            ← ImagePicker wrapper (camera + gallery)
│   └── geo_service.dart              ← Geolocator wrapper
│
├── services/
│   ├── navigation_service.dart       ← GoRouter wrapper
│   └── permission_service.dart       ← permission_handler wrapper
│
└── diagnostics/
    └── runtime_logger.dart           ← Structured logging (abstract)
```

**9 file baru. Bukan 30. Bukan 20. Hanya 9.**

---

## DEPENDENCY GRAPH

```
RuntimeLogger (no deps)
    │
    ├── ErrorBoundary (depends: Logger)
    ├── AppLifecycleService (depends: Logger)
    │
    ├── PermissionService (depends: Logger)
│   ├── MediaService (depends: Permission, Logger)
│   └── GeoService (depends: Permission, Logger)
    │
    ├── NavigationService (depends: Logger, GoRouter)
    │
    └── RuntimeInitializer (depends: all above)
        └── main.dart (depends: Initializer)
```

Tidak ada circular dependency. Setiap service independen.

---

## ACCEPTANCE CRITERIA

### Critical (harus lulus)

- ✅ Camera: open → grant → take photo → return file path (no crash)
- ✅ Camera: open → deny → show error (no crash)
- ✅ Camera: permanently denied → open settings dialog (no crash)
- ✅ GPS: open → grant → get location (no crash)
- ✅ GPS: open → deny → show error (no crash)
- ✅ GPS: GPS disabled → show "Aktifkan GPS" message (no crash)
- ✅ Back from Map tab → Home tab
- ✅ Back from Home → snackbar "Tekan sekali lagi untuk keluar"
- ✅ Back from Home → back again within 2s → exit
- ✅ App background → WarningNotifier polling paused → resume → polling resumed
- ✅ App background → Map rendering paused → resume → rendering resumed
- ✅ No direct `ImagePicker()`, `Geolocator.getCurrentPosition()`, `Permission.camera.request()` in `features/`
- ✅ No `context.go()` or `Navigator.pop()` in widget files
- ✅ `main.dart` ~20 baris
- ✅ `RuntimeInitializer.initialize()` non-blocking — degraded mode on non-critical failure

### Should Pass

- ✅ All existing features still compile and work
- ✅ `flutter analyze` passes with 0 errors in `lib/core/`
- ✅ `flutter build apk --debug` succeeds

### Future Sprints (NOT in QA-F0)

- ❌ `FeatureFlagService` — ditunda ke QA-F3
- ❌ `RuntimeDashboard` — ditunda ke QA-F3
- ❌ `OfflineQueue` — ditunda ke QA-F2
- ❌ `CacheManager` — ditunda ke QA-F2
- ❌ `PerformanceMonitor` — ditunda ke QA-F4
- ❌ `BatteryMonitor` — ditunda ke QA-F4
- ❌ `CrashReporter` — ditunda ke QA-F3
- ❌ `GalleryService` — ditunda ke QA-F1
- ❌ `NotificationService` — ditunda ke QA-F3
- ❌ `ConnectivityService` — ditunda ke QA-F2
- ❌ `SecureStorage` — ditunda ke QA-F2
- ❌ `BackgroundSync` — ditunda ke QA-F2
- ❌ `FeatureFlagService` — ditunda ke QA-F3

---

## RISK

| Risiko | Mitigasi |
|--------|----------|
| Overengineering | Prinsip ADR: hanya buat yang ada pemanggil nyata. 7 komponen, bukan 30. |
| Regression | Setiap migrasi dilakukan bertahap. Old code tetap jalan sampai new service siap. |
| PermissionService belum di-debug | Di-test di Week 2 bersamaan dengan CameraService + LocationService. |
| NavigationService ubah behavior existing | Acceptance criteria明确: back button, tab switch, exit confirmation harus identik dengan spesifikasi. |
