# SPRINT 2.1A — Runtime Stabilization

**Objective**: Zero crash, zero overflow, zero infinite loop, zero blank screen.
**Strategy**: Audit & fix runtime behavior — not add features.

---

## Tasks

| # | Task | File(s) | Status |
|---|------|---------|--------|
| S1 | Fix RenderFlex overflow (login screen keyboard) | `login_screen.dart` | ✅ |
| S2 | Fix WidgetErrorBoundary — stateful catch dengan fallback | `error_boundary.dart`, `widget_registry.dart` | ✅ |
| S3 | Audit Error UI — semua screen punya user-friendly message (bukan raw exception) | All screens + new `DioExceptionMapper` | ✅ |
| S4 | Audit Retry UI — semua error punya retry button yang berfungsi | All screens (verified existing) | ✅ |
| S5 | Audit Dio Exception — centralized `DioExceptionMapper` with per-type messages | New file `core/error/dio_exception_mapper.dart` | ✅ |
| S6 | Audit 401/403 — graceful handling tanpa infinite loop | `auth_api_client.dart`, `auth_state_provider.dart` | ✅ |
| S7 | Audit Guest/Profile switching — tidak ada state leak | `profile_screen.dart`, `auth_state_provider.dart` | ✅ |
| S8 | Audit Logout — clean redirect tanpa crash | `auth_state_provider.dart`, `main.dart`, `app_router.dart` | ✅ |

---

## Acceptance Criteria

| Criteria | Status |
|----------|--------|
| Tidak ada RenderFlex overflow | ✅ `SingleChildScrollView` added |
| WidgetErrorBoundary menampilkan fallback saat widget gagal | ✅ Stateful error catcher with fallback |
| Semua error menampilkan pesan readable (bukan raw exception) | ✅ `DioExceptionMapper` di 8+ lokasi |
| Semua retry button memicu refresh | ✅ Existing (verified) |
| 401/403 tidak infinite loop | ✅ Interceptor cleaned + verifySession retry limit |
| Logout → guest mode tanpa crash | ✅ Verified |
| Guest → Login → Logout → Guest tidak ada state leak | ✅ ProfileNotifier auto-reset via auth watcher |
| `flutter analyze` 0 errors | ✅ Zero errors |
| APK build success | ✅ |

## Files Modified/Created in Sprint 2.1A

| File | Change |
|------|--------|
| `core/error/dio_exception_mapper.dart` | **NEW** — centralized DioException → user-friendly Indonesian message |
| `core/runtime/error_boundary.dart` | Fix — WidgetErrorBoundary now stateful, catches widget render errors with fallback |
| `core/api/auth_api_client.dart` | Fix — remove auto-logout from interceptor (prevents infinite 403 loop) |
| `features/auth/.../auth_state_provider.dart` | Fix — retry limit on `verifySessionWithDatabase()` |
| `features/auth/.../login_screen.dart` | Fix — wrap in `SingleChildScrollView` (RenderFlex overflow) |
| `features/auth/.../main.dart` | Fix — move `ref.listen` from `addPostFrameCallback` to `build()` |
| `features/public/report/.../report_wizard_screen.dart` | Fix — 6 locations: raw `$err` / `e.toString()` → `DioExceptionMapper` |
| `features/public/report/.../report_tracking_screen.dart` | Fix — raw `$e` → `DioExceptionMapper` |
| `features/public/map/.../cop_map_screen.dart` | Fix — raw `$e` in SnackBar → `DioExceptionMapper` |
| `features/public/map/.../map_layer_notifier.dart` | Fix — raw `e.toString()` → `DioExceptionMapper` |
| `features/public/incident/.../incident_provider.dart` | Fix — raw `e.toString()` → `DioExceptionMapper` |
| `features/public/incident/.../incident_feed_list.dart` | Fix — raw loadMoreError → `DioExceptionMapper` |
| `features/profile/.../profile_screen.dart` | Fix — raw `$err` → `DioExceptionMapper` |
| `features/operasi/assessment/.../assessment_provider.dart` | Fix — 5 locations: raw `e.toString()` → `DioExceptionMapper` |