# PUBLIC DOMAIN IMPLEMENTATION LOG

| Gelombang | Task | Status | Date |
|-----------|------|--------|------|
| — | Audit selesai. Fix Plan diterbitkan. | ✅ | 2026-07-09 |
| Critical | C1 — Hapus duplicate toggleLayer di COP Map timer | ✅ | 2026-07-09 |
| Critical | C2 — Integrasi AppLifecycleObserver ke COP Map timer | ✅ | 2026-07-09 |
| Critical | C3 — Pindahkan submit laporan dari raw Dio ke repository | ✅ | 2026-07-09 |
| Critical | C4 — Refactor tracking fetch dari raw Dio ke datasource + lifecycle | ✅ | 2026-07-09 |
| Critical | C5 — Pindahkan map config fetch dari raw Dio ke datasource | ✅ | 2026-07-09 |
| High | H3 — Ganti placeholder Resource tab dengan ResourceScreen | ✅ | 2026-07-09 |
| High | H4 — Ganti placeholder Tracking route dengan ReportTrackingScreen | ✅ | 2026-07-09 |
| High | H5 — Tambah error handling ke AuthStateNotifier._loadState() | ✅ | 2026-07-09 |
| High | H6 — Tracking lifecycle (terintegrasi di C4) | ✅ | 2026-07-09 |
| High | H7 — AutoDispose + retry untuk laporan FutureProviders | ✅ | 2026-07-09 |
| High | H8 — Duplicate error boundary: hanya 1 yang aktif (tidak perlu diubah) | ✅ | 2026-07-09 |
| High | H9 — Fix WarningProvider timer/observer registration order | ✅ | 2026-07-09 |
| High | H10 — Fix splash navigation race condition (_hasNavigated guard) | ✅ | 2026-07-09 |
| High | H11 — Dashboard KPI refresh: jangan clear state on failure | ✅ | 2026-07-09 |
| High | H12 — Config local datasource: implementasi SharedPreferences cache | ✅ | 2026-07-09 |
| High | H11b — Weather + Config + Warning refresh: same fix (jangan clear state) | ✅ | 2026-07-09 |

---

## Detail Perubahan

### C1 — COP Map double toggle
**File**: `cop_map_screen.dart:40-42`
**Change**: Hapus baris kedua `toggleLayer` yang menyebabkan flicker setiap 30 detik.

### C2 — COP Map lifecycle
**File**: `cop_map_screen.dart:56-63`
**Change**: `onBackground()` batalkan timer, `onForeground()` restart timer + refresh layer.

### C3 — Report submit via repository
**Files**: 
- `laporan_repository.dart` — tambah parameter `fotoPath`
- `laporan_repository_impl.dart` — pass `fotoPath` ke datasource
- `report_wizard_screen.dart` — ganti raw Dio call dengan `laporanRepositoryProvider.createLaporan()`

### C4 — Tracking via datasource
**File**: `report_tracking_screen.dart`
**Change**: Ganti raw Dio `publicApiClientProvider.get('laporan/...')` dengan `laporanRemoteDatasourceProvider.getTracking()`. Tambah `AppLifecycleObserver` untuk pause/resume polling.

### C5 — Map config via datasource
**Files**:
- `map_layer_datasource.dart` — tambah method `fetchMapConfig()`
- `cop_map_screen.dart` — ganti raw Dio dengan `mapLayerDatasourceProvider.fetchMapConfig()`

### H3 — Resource tab
**File**: `app_router.dart:221-225`
**Change**: Ganti placeholder Scaffold dengan `ResourceScreen()`

### H4 — Tracking route
**File**: `app_router.dart:123-130`
**Change**: Ganti placeholder Scaffold dengan `ReportTrackingScreen(ticketId: code)`

### H5 — AuthState error handling
**File**: `auth_state_provider.dart:66-86`
**Change**: Wrap `_loadState()` body dalam try/catch, fallback ke `isLoading: false` jika error.

### H7 — Laporan providers autoDispose
**File**: `laporan_provider.dart`
**Change**: Tambah `.autoDispose` ke semua 4 FutureProvider agar bisa di-refresh via invalidation.

### H9 — WarningProvider timer/observer order
**File**: `warning_provider.dart:14-25`
**Change**: Pindahkan `registerObserver()` SEBELUM `_startPolling()`.

### H10 — Splash race condition
**File**: `splash_screen.dart`
**Change**: Tambah `_hasNavigated` guard untuk mencegah double-navigate.

### H11 — Refresh partial failure
**Files**: 
- `dashboard_kpi_provider.dart` — `refresh()` hanya update state jika sukses
- `weather_provider.dart` — same fix
- `config_provider.dart` — same fix
- `warning_provider.dart` — same fix

### H12 — Config local cache
**File**: `config_local_datasource.dart`
**Change**: Implementasi `getCachedConfig()`/`cacheConfig()` via SharedPreferences, bukan hardcoded + no-op.

---

## Verification Gate — Hasil Live Test Emulator

| Test | Result |
|------|--------|
| **B1 — App launch** | ✅ Runtime init, splash, dashboard |
| **B2 — Navigation** | ✅ Tab switching, routing |
| **B3 — Login API** | ✅ HTTP 200, token tersimpan |
| **B4 — No crash** | ✅ Tidak ada crash selama 60 detik |

### Bug Ditemukan & Diperbaiki

| Bug | Severity | Fix |
|-----|----------|-----|
| **`ref.listen` outside build()** | CRITICAL | Pindah `ref.listen` ke `build()` di `main.dart` |
| **Auth interceptor infinite 403 loop** | CRITICAL | Hapus auto-logout dari interceptor + retry limit di verifySession |
| **Splash double-navigate race** | HIGH | Guard `_hasNavigated` (dari H10) |

### Regression: Profile 403 (1x vs sebelumnya 10+)
- **Before fix**: 10+ profile 403 calls = infinite loop
- **After fix**: 1 profile 403 call = graceful error state
- **Root cause**: `authApiClient` interceptor memanggil `logout()` pada 403, yang memicu rebuild provider → loop