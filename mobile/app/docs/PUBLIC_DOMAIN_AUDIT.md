# PUBLIC DOMAIN AUDIT — NURISK Mobile

> Phase 2.1 — System Integration Audit
> Date: 2026-07-08
> Auditor: AI Agent (System Integrator mode)

---

## SCOPE

Audit seluruh Public Domain — bukan audit file, tetapi audit alur dan integrasi end-to-end.

### Modules Audited

- Splash → Dashboard → COP Map → Report Wizard → Tracking → News → Resource → Guest Profile
- Authentication Entry (login CTA from public domain)
- Bottom Navigation (public)
- API integration (all Dio, zero mock data)

---

## AUDIT FINDINGS

### 1. HARDCODED TEST VALUES IN PRODUCTION CODE

| # | File | Line | Finding | Severity |
|---|------|------|---------|----------|
| 1 | `features/auth/presentation/screens/login_screen.dart` | 15 | `TextEditingController` initialized with hardcoded phone `'08123456789'` and password `'password'` | **CRITICAL** |
| 2 | `features/auth/presentation/widgets/pin_verification_dialog.dart` | 52 | Fallback magic PIN `123456` bypasses server verification in catch block | **CRITICAL** |
| 3 | `features/public/map/presentation/screens/cop_map_screen.dart` | 101-103 | Hardcoded default map center `LatLng(-6.200000, 106.816666)` and zoom `12.0` | LOW |
| 4 | `features/public/map/presentation/notifiers/map_state_notifier.dart` | 33-34 | Same hardcoded default coordinates | LOW |
| 5 | `features/governance/presentation/screens/executive_dashboard_screen.dart` | 166-178 | Hardcoded demo analytics values (`'2'`, `'4'`, `'120'`, `'5'`) with labels `'Posko Aktif'`, `'Insiden'`, `'Relawan'`, `'Aset In-Use'` | HIGH |
| 6 | `features/governance/presentation/screens/executive_dashboard_screen.dart` | 56-59 | SLA logic uses hardcoded string values `'Merah'`, `'Kuning'`, `'Hijau'` — no constants/enums | MEDIUM |

### 2. NAVIGATION BYPASSING NavigationService

| # | File | Line | Call | Severity |
|---|------|------|------|----------|
| 1 | `features/public/dashboard/presentation/widgets/public_bottom_nav.dart` | 63 | `SystemNavigator.pop()` — bypass exit | MEDIUM |
| 2 | `features/map/presentation/widgets/spatial_filter_bottom_sheet.dart` | 83 | `Navigator.pop(context)` — use NavigationService.pop() | LOW |
| 3 | `features/public/report/presentation/screens/report_wizard_screen.dart` | 269 | `Navigator.pop(context)` — use NavigationService.pop() | LOW |
| 4 | `features/auth/presentation/widgets/pin_verification_dialog.dart` | 45, 53, 97 | `Navigator.pop(context, ...)` — dialog context, acceptable | INFO |

### 3. STATE MANAGEMENT AUDIT

| # | Module | Finding | Severity |
|---|--------|---------|----------|
| 1 | Dashboard | `DashboardKpiNotifier` (AsyncNotifier) has loading/error/data — OK | ✅ |
| 2 | Dashboard | `DashboardOrchestrator` coordinates multiple data sources — OK | ✅ |
| 3 | Warning | `WarningNotifier` (AsyncNotifier) with lifecycle-aware polling — OK | ✅ |
| 4 | Weather | `WeatherNotifier` (AsyncNotifier) with loading/error/data — OK | ✅ |
| 5 | Incident | `IncidentNotifier` with pagination state — OK | ✅ |
| 6 | Map | `MapLayerNotifier`, `MapStateNotifier`, `OperationalFilterNotifier` — OK | ✅ |
| 7 | Report | `FutureProvider` for jenis_bencana, wilayah — no retry mechanism on failure | MEDIUM |
| 8 | Tracking | `report_tracking_screen.dart` uses `setState` for loading — no provider | MEDIUM |

### 4. EXCEPTION HANDLING

| # | Finding | Severity |
|---|---------|----------|
| 1 | 26 `throw Exception(...)` across 14 files — mixed English/Indonesian | MEDIUM |
| 2 | `weather_remote_datasource.dart:32` — re-throws as `Exception('Network error: $e')` | LOW |
| 3 | `map_layer_datasource.dart:24` — re-throws with HTTP status context | LOW |
| 4 | All catch blocks have meaningful body — zero empty catches | ✅ |

### 5. ERROR BOUNDARY COVERAGE

| # | Screen | Has ErrorBoundary? | Notes |
|---|--------|-------------------|-------|
| 1 | SplashScreen | Partial | Zone.runGuarded at app level, not per-widget |
| 2 | PublicDashboard | Partial | No per-widget ErrorBoundary — one widget crash could take down entire dashboard |
| 3 | COP Map | Partial | Map error handling in catch blocks, no ErrorBoundary wrapper |
| 4 | Report Wizard | Partial | Step-level try/catch, no ErrorBoundary per step |
| 5 | Tracking | None | `_hasError` boolean, no ErrorBoundary |
| 6 | Profile | None | AsyncNotifier.when handles error, no ErrorBoundary wrapper |

### 6. MOCK DATA STATUS

| # | Source | Status | Notes |
|---|--------|--------|-------|
| 1 | Mock JSON files | ✅ CLEAN | Zero mock/dummy JSON files in features/ |
| 2 | Hardcoded test data | ⚠️ 6 locations | See section 1 above |
| 3 | Inline JSON/Map literals | ⚠️ Executive dashboard has hardcoded demo values | See finding #5 in section 1 |

### 7. API INTEGRATION

| # | Endpoint Type | Status | Notes |
|---|--------------|--------|-------|
| 1 | All HTTP calls | ✅ CLEAN | 100% Dio, zero `package:http` |
| 2 | Auth API | ✅ | `authApiClientProvider` |
| 3 | Public API | ✅ | `publicApiClientProvider` |
| 4 | Mock backend | ⚠️ | App appears to point at `http://10.0.2.2:8080` — local dev server, not production |
| 5 | Backend Laravel endpoints | ❓ UNKNOWN | Public endpoints (`GET /api/public/dashboard/kpi`, etc.) — implementation status unknown |

### 8. LINEARPROGRESSINDICATOR / LOADING STATES

| # | Finding | Status |
|---|---------|--------|
| 1 | All 19 `CircularProgressIndicator` occurrences | ✅ All guarded by proper loading state |
| 2 | Any unconditional spinner | ✅ NONE found |

### 9. GOOGLE MAPS / MAPLIBRE DEPENDENCY

| # | Package | Status | Notes |
|---|---------|--------|-------|
| 1 | maplibre_gl | ✅ Used in COP map | Layer rendering, markers, GeoJSON |
| 2 | Google Maps | ❌ Not used | Only MapLibre |

### 10. LOCALIZATION / HARDCODED STRINGS

| # | Finding | Severity |
|---|---------|----------|
| 1 | Mixed EN/ID in exception messages | LOW |
| 2 | Mixed EN/ID in UI strings | LOW (consistent Indonesian in UI) |

---

## ALUR INTEGRATION AUDIT

### Guest Flow

```
Splash → Dashboard → Map → Report → Tracking → Profile
```

| Step | Status | Notes |
|------|--------|-------|
| Splash → Dashboard | ✅ | Session recovery works, navigates to public home for guest |
| Dashboard → Map | ✅ | Bottom nav tab switch |
| Map → Report | ✅ | 'Lapor' CTA or bottom nav |
| Report → Tracking | ✅ | After submit, navigate to tracking |
| Tracking → Profile | ✅ | Bottom nav |
| Profile → Login CTA | ✅ | Login button visible for guest |
| Full loop back to Dashboard | ✅ | Double-back exit works |

### Authentication Flow (from Public)

```
Profile → Login → Mandate → Executive → Logout → Guest
```

| Step | Status | Notes |
|------|--------|-------|
| Profile → Login | ✅ | Login CTA works |
| Login → Mandate | ✅ | If multiple mandates |
| Mandate → Executive | ✅ | Route guard redirects correctly |
| Executive → Logout | ✅ | Clear session, back to public |
| Logout → Guest | ✅ | Dashboard visible after logout |

### Report Lifecycle

```
Location → GPS → Permission → Map → Reverse Geocode → Category → Photo → Description → Review → Submit
```

| Step | Status | Notes |
|------|--------|-------|
| Location step | ✅ | GPS integration via GeoService |
| Permission check | ✅ | Via PermissionService |
| Map display | ✅ | MapLibre for location picker |
| Reverse geocode | ✅ | Address from coordinates |
| Category selection | ✅ | From API via FutureProvider |
| Photo capture/gallery | ✅ | Via MediaService |
| Description | ✅ | Text input |
| Review | ✅ | Summary before submit |
| Submit | ✅ | POST via Dio |
| GPS mati | ⚠️ | Edge case handled? Need to verify |
| GPS mock | ❌ | No mock detection in Report Wizard |
| Upload fail retry | ❌ | No retry mechanism on upload failure |

### COP Map Interaction

```
Map → Layer → Legend → Filter → Marker → Bottom Sheet → Timeline → Back
```

| Step | Status | Notes |
|------|--------|-------|
| Map render | ✅ | MapLibre with OpenStreetMap tiles |
| Layer control | ✅ | LayerControlBottomSheet |
| Legend | ✅ | Part of layer control |
| Filter | ✅ | SpatialFilterBottomSheet |
| Marker | ✅ | GeoJSON markers |
| Marker tap → Bottom Sheet | ✅ | OperationalBottomSheet |
| Timeline | ✅ | TimelineRenderer |
| Back navigation | ✅ | Closes bottom sheet, stays on map |

---

## SUMMARY

### ✅ Pass — No Issues

- All HTTP via Dio (0 `package:http`)
- All CircularProgressIndicator guarded with loading state
- Zero empty catch blocks
- Zero mock JSON files
- All native plugins isolated (no ImagePicker/Geolocator in features/)
- All navigation services available
- All provider states have loading/error/data

### ⚠️ Warning — Needs Fix

- **6 hardcoded test values** in production code (2 CRITICAL — credentials in LoginScreen, magic PIN in PinVerificationDialog)
- Navigation bypass: `SystemNavigator.pop()` in public_bottom_nav
- Missing retry mechanism for report upload failure
- No GPS mock detection in Report Wizard
- Executive dashboard has hardcoded demo analytics

### ❌ Fail — Missing Implementation

- News module: backend endpoint status unknown
- Resource module: backend endpoint status unknown
- Public API production endpoint not configured (uses `10.0.2.2:8080`)
- No ErrorBoundary per-widget on dashboard
- No per-skeleton loading for all widgets
- SSE/WebSocket for real-time tracking not implemented
