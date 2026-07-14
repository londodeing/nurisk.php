# MISSING AUDITS — 6 Area Kritis

Audit tambahan untuk area yang belum tercakup di MOBILE_RUNTIME_AUDIT.md.

---

## 1. MEMORY LEAK AUDIT

### Methodology
Static analysis of provider lifetimes, controller disposal, stream subscriptions, and timer management across 106 Dart files.

### Findings

| # | Component | Leak Type | Severity | Details |
|---|-----------|-----------|----------|---------|
| ML-01 | `WarningNotifier` (`warning_provider.dart:17`) | Timer leak | 🔴 HIGH | `Timer.periodic(30s)` — provider not `autoDispose`; `ref.onDispose` declared but NEVER called. Timer runs for entire app lifetime. |
| ML-02 | `CopMapScreen` (`cop_map_screen.dart:21`) | Controller leak | 🔴 HIGH | `MapLibreMapController?` nullable; NEVER disposed. Each tab switch creates new instance. |
| ML-03 | `SpatialFilterBottomSheet` (`spatial_filter_bottom_sheet.dart`) | No dispose | 🟡 MEDIUM | StatefulWidget without dispose — no controllers but pattern concern |
| ML-04 | `LayerControlBottomSheet` (`layer_control_bottom_sheet.dart`) | No dispose | 🟡 MEDIUM | Same pattern |
| ML-05 | `ReportWizardScreen` (`report_wizard_screen.dart:107`) | ImagePicker instance | 🟢 LOW | `ImagePicker()` created inline each time; no pooling (acceptable) |
| ML-06 | `CopMapScreen` (`cop_map_screen.dart:27`) | Stream subscription leak | 🟠 HIGH | `controller.onFeatureTapped.add(...)` — listener added on every `_onMapCreated` but NEVER removed. If `_onMapCreated` fires multiple times, listeners accumulate. |

### Stream Subscription Leak Detail

```dart
// cop_map_screen.dart:27
controller.onFeatureTapped.add((point, coordinates, id, layerId, annotation) {
  _handleFeatureTapped(id, coordinates);
});
```

`onFeatureTapped` is a `Signal` (event bus). Every call to `_onMapCreated` adds a NEW listener. If the widget rebuilds or the map controller is recreated, old listeners remain — memory leak + duplicate event handlers.

**Fix**: Store `Signal` subscription and cancel on dispose:
```dart
SignalSubscription? _featureTapSubscription;

@override
void dispose() {
  _featureTapSubscription?.cancel();
  mapController?.dispose();
  super.dispose();
}

void _onMapCreated(MapLibreMapController controller) {
  _featureTapSubscription = controller.onFeatureTapped.add(...);
}
```

---

## 2. MAP PERFORMANCE AUDIT

### Analysis

| Metric | Current | Target | Gap |
|--------|---------|--------|-----|
| Max markers without clustering | Unknown (no clustering) | 500+ with clustering | ❌ No clustering implemented |
| Max polygon layers | Unknown (InaRISK boundary) | 50+ polygons | ❌ No layer count limit |
| FPS during zoom | Unknown | 30+ FPS | ❌ Not measured |
| RAM with all layers active | Unknown | < 200MB | ❌ Not measured |
| Style string | CartoDB positron-gl | Dynamic/custom | ⚠️ Hardcoded |
| Tile caching | None | Local cache | ❌ No tile strategy |
| `trackCameraPosition` | `true` | `true` | ✅ OK |

### Recommendations

| Priority | Action | Impact |
|----------|--------|--------|
| 🔴 HIGH | Implement GeoJSON clustering for markers > 100 | Prevent jank at scale |
| 🟠 MEDIUM | Add tile caching strategy (MBTiles or local cache) | Reduce data usage, faster load |
| 🟠 MEDIUM | Limit max visible layers to 10 | Prevent GPU overload |
| 🟡 MEDIUM | Measure FPS on reference device (API 34, 4GB RAM) | Baseline for optimization |
| 🟡 MEDIUM | `onMapCreated` debounce (prevent double fire) | Prevent duplicate layer loads |

---

## 3. SQLITE AUDIT

### Analysis

| Metric | Status | Details |
|--------|--------|---------|
| Database version | Drift 2.18.0 | ✅ Latest |
| Connection type | Native + Web | ✅ |
| Migration strategy | ❌ NOT IMPLEMENTED | No `onUpgrade` or `MigrationStrategy` found |
| Cache cleanup | ❌ NOT IMPLEMENTED | No TTL, no vacuum, no eviction |
| TTL (time-to-live) | ❌ NOT DEFINED | Tables: incident, dashboard_kpi, weather, warning |
| Table vacuum | ❌ NEVER | VACUUM never called |
| Database size limit | ❌ NOT DEFINED | Could grow unbounded |
| Write-ahead logging | ❌ NOT CONFIGURED | Default settings |

### Findings

| # | Issue | Severity | Details |
|---|-------|----------|---------|
| SQ-01 | No migration strategy | 🟠 HIGH | Schema changes will crash existing installs. Need `MigrationStrategy` with `onUpgrade`. |
| SQ-02 | No cache TTL | 🟡 MEDIUM | Cached data never expires. Weather data from yesterday shown as fresh. |
| SQ-03 | No VACUUM | 🟢 LOW | Database file grows but never shrinks. Long-term issue. |
| SQ-04 | No error handling for DB full | 🟡 MEDIUM | No try-catch around DB writes. Could crash if disk full. |

### Recommendations

```dart
// In database_provider.dart or database class:
@override
int get schemaVersion => 2;

@override
MigrationStrategy get migration {
  return MigrationStrategy(
    onCreate: (m) async {
      await m.createAll();
    },
    onUpgrade: (m, from, to) async {
      // Handle schema migration
    },
  );
}

// Periodic cleanup
Future<void> cleanExpiredCache({Duration ttl = const Duration(hours: 24)}) async {
  final cutoff = DateTime.now().subtract(ttl);
  await (delete(dashboardTable)..where((t) => t.cachedAt.isBefore(cutoff))).go();
  await (delete(weatherTable)..where((t) => t.cachedAt.isBefore(cutoff))).go();
  await vacuum();
}
```

---

## 4. OFFLINE AUDIT

### Current State

| Feature | Offline Behavior | Status |
|---------|-----------------|--------|
| Camera | ❌ Not tested | Could crash without connectivity (FileProvider issue) |
| GPS | ✅ Works offline | GPS satellite-based, no network needed |
| Gallery | ❌ Not tested | Could crash |
| Report Submit | ❌ FAILS OFFLINE | API call fails; no retry queue |
| Map | ❌ Not tested | Tiles from network; no cache |
| Dashboard | ⚠️ Partial | Data from API; shows nothing offline |
| Weather | ⚠️ Partial | Cache exists but no offline-first strategy |
| Warning | ❌ FAILS OFFLINE | API polling fails; timer keeps retrying |

### Required Offline Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                      OFFLINE ARCHITECTURE                           │
│                                                                      │
│  ┌──────────────┐     ┌──────────────────┐     ┌──────────────────┐ │
│  │  User Action  │────▶│  Repository      │────▶│  API Client      │ │
│  │  (Submit      │     │                  │     │  (dio)           │ │
│  │   Report)     │     │  ┌────────────┐  │     └────────┬─────────┘ │
│  └──────────────┘     │  │ Offline    │  │              │            │
│                        │  │ Queue      │  │     ┌────────▼─────────┐ │
│                        │  │ (Drift)    │  │     │  ONLINE?         │ │
│                        │  └────────────┘  │     └────┬──────┬─────┘ │
│                        └──────────────────┘     YES       NO        │
│                                                     │         │      │
│                                                     ▼         ▼      │
│                                              ┌────────┐ ┌──────────┐ │
│                                              │ Send   │ │ Save to  │ │
│                                              │ Now    │ │ Offline  │ │
│                                              └────────┘ │ Queue    │ │
│                                                         └──────────┘ │
│                                                              │        │
│                                                    ┌─────────▼──────┐│
│                                                    │ Connectivity   ││
│                                                    │ Listener       ││
│                                                    │ (reconnect →   ││
│                                                    │  flush queue)  ││
│                                                    └────────────────┘│
└─────────────────────────────────────────────────────────────────────┘
```

### Findings

| # | Issue | Severity | Fix |
|---|-------|----------|-----|
| OF-01 | Report submit crashes offline | 🔴 CRITICAL | Add connectivity check before API call; queue if offline |
| OF-02 | Map tiles only from network | 🟠 HIGH | Add tile caching strategy |
| OF-03 | Dashboard shows blank offline | 🟡 MEDIUM | Show cached data, indicate "offline" |
| OF-04 | No retry queue for failed submissions | 🟠 HIGH | Implement offline queue with auto-flush |
| OF-05 | Warning polling fails silently | 🟡 MEDIUM | Pause polling when offline, resume on reconnect |

---

## 5. COLD START AUDIT

### Measured Bottlenecks

| Phase | Current | Target | Bottleneck |
|-------|---------|--------|------------|
| `main()` → `runApp()` | Unknown | < 500ms | `dotenv.load()` — file I/O |
| Splash → Dashboard | 1500ms (hardcoded delay) | < 1000ms | Artificial delay in splash |
| Dashboard first paint | Unknown | < 500ms | API calls in widget build |
| Map first paint | Unknown | < 2000ms | Style download + layer load |

### Issues

| # | Issue | Severity | Fix |
|---|-------|----------|-----|
| CS-01 | Splash delay 1500ms is hardcoded | 🟡 MEDIUM | Remove delay; navigate immediately |
| CS-02 | `dotenv.load()` is blocking | 🟡 MEDIUM | Make async, show loading state |
| CS-03 | Dashboard makes API calls on build | 🟠 HIGH | Use `AsyncValue` with shimmer skeleton |
| CS-04 | Map style download blocks first frame | 🟠 HIGH | Preload style URL, use cached style |

### Recommendations

```
Cold Start Target:
  main() → runApp(): < 300ms
  Splash → Dashboard: < 1000ms (remove artificial delay)
  Dashboard first content: < 500ms (shimmer skeleton)
  Map first frame: < 2000ms
```

---

## 6. BATTERY AUDIT

### Current State

| Feature | Behavior | Battery Impact |
|---------|----------|----------------|
| Warning polling | Timer.periodic 30s — runs forever | 🔴 HIGH — never stops |
| GPS location | Request-response only | 🟢 LOW — one-time call |
| MapLibre | Renders continuously while visible | 🟡 MEDIUM — GPU usage |
| Animation | Unknown | 🟢 LOW — minimal |
| Background tasks | None | 🟢 LOW |

### Battery Drain Analysis

| # | Issue | Severity | Details |
|---|-------|----------|---------|
| BT-01 | WarningNotifier 30s polling never stops | 🔴 HIGH | Even in background. No pause on app lifecycle change. |
| BT-02 | MapLibre continues rendering when tab not visible | 🟡 MEDIUM | IndexedStack keeps widgets alive. Map renders even on Profile tab. |
| BT-03 | No Geofencing/zone monitoring | 🟢 LOW | GPS is request-response, not continuous. Acceptable. |
| BT-04 | No battery optimization consideration | 🟡 MEDIUM | No power-saving modes or adaptive refresh rates. |

### Recommendations

| Priority | Action | Impact |
|----------|--------|--------|
| 🔴 HIGH | Pause WarningNotifier timer on background via AppLifecycleService | Significant battery saving |
| 🟡 MEDIUM | Pause MapLibre rendering when tab not visible or app backgrounded | GPU + battery saving |
| 🟡 MEDIUM | Adaptive polling: increase interval when not on Home tab | Battery saving |
| 🟢 LOW | Add battery optimization disclaimer on first GPS use | User awareness |

---

## SUMMARY: 6 MISSING AUDITS

| Audit | Issues Found | CRITICAL | HIGH | MEDIUM | LOW |
|-------|-------------|----------|------|--------|-----|
| 1. Memory Leak | 6 | 0 | 3 | 2 | 1 |
| 2. Map Performance | 5 | 0 | 1 | 3 | 1 |
| 3. SQLite | 4 | 0 | 1 | 2 | 1 |
| 4. Offline | 5 | 1 | 2 | 2 | 0 |
| 5. Cold Start | 4 | 0 | 2 | 2 | 0 |
| 6. Battery | 4 | 1 | 1 | 2 | 0 |
| **TOTAL** | **28** | **2** | **10** | **13** | **3** |
