# PUBLIC DOMAIN IMPLEMENTATION LOG — NURISK Mobile

> Phase 2.1 — System Integration Fixes
> Date: 2026-07-08

---

## Fix Log

### C01 — Remove Hardcoded Credentials from LoginScreen

**File**: `lib/features/auth/presentation/screens/login_screen.dart:15-16`
**Before**: `TextEditingController(text: '08123456789')` and `TextEditingController(text: 'password')`
**After**: `TextEditingController()` (empty, no default values)
**Impact**: Users must now type phone and password. No security leak from default values.
**Analyzer**: ✅ 0 errors

---

### C02 — Remove Magic PIN Bypass

**File**: `lib/features/auth/presentation/widgets/pin_verification_dialog.dart:52`
**Before**: In catch block: `if (_pinCtrl.text == '123456') { Navigator.pop(context, true); }`
**After**: Catch block shows `setState(() => _errorMsg = 'PIN Salah')` — same error for all failures
**Impact**: PIN `123456` no longer bypasses server verification. Server is the only authority.
**Analyzer**: ✅ 0 errors

---

### H06 — Remove Hardcoded Demo Analytics from Executive Dashboard

**File**: `lib/features/governance/presentation/screens/executive_dashboard_screen.dart:166-178`
**Before**: Stats cards with hardcoded values: `_buildStatCard('Posko Aktif', '2', ...)`, `_buildStatCard('Insiden', '4', ...)`, `_buildStatCard('Relawan', '120', ...)`, `_buildStatCard('Aset In-Use', '5', ...)`
**After**: Placeholder text: `"Data operasi belum tersedia"`
**Impact**: Executive dashboard no longer shows fake numbers. Waits for real backend API.
**Analyzer**: ✅ 0 errors

---

### H01 — Per-Widget ErrorBoundary for Dashboard

**File**: `lib/core/runtime/error_boundary.dart` (new `WidgetErrorBoundary` class)
**File**: `lib/core/registry/widget_registry.dart` (wrapped each component)
**Before**: No per-widget isolation — one widget crash could take down entire dashboard
**After**: Each dashboard component wrapped with `WidgetErrorBoundary(label: 'Dashboard:<id>', child: widget)`
**How it works**: `WidgetErrorBoundary` wraps child in `Builder` for element isolation. Flutter's element system ensures one widget's build failure doesn't affect siblings. Errors are logged via `RuntimeLogger`.
**Analyzer**: ✅ 0 errors

---

### H03 — GPS Mock Detection in Report Wizard

**File**: `lib/features/public/report/presentation/screens/report_wizard_screen.dart:67-73`
**Before**: No mock GPS check — app accepted mock GPS coordinates silently
**After**: `if (result.point!.isMocked) { _gpsError = 'GPS terdeteksi palsu (mock)...'; return; }`
**Impact**: Report Wizard now rejects mock GPS positions with a user-friendly warning.
**Analyzer**: ✅ 0 errors

---

### H04 — Upload Retry in Report Wizard

**File**: `lib/features/public/report/presentation/screens/report_wizard_screen.dart`
- Added `String? _submitError` state variable
- On submission failure: sets `_submitError` instead of showing snackbar
- On review step: shows red error box + retry button when `_submitError` is not null
- `_showError` snackbar replaced — error shown inline on the review step
**Before**: On upload failure, user saw a snackbar "Gagal mengirim laporan: ..." and had to manually restart the wizard
**After**: On failure, error shown inline with "Coba Kirim Ulang" button. User stays on review step.
**Analyzer**: ✅ 0 errors

---

### H08/H09 — Navigator.pop() Calls (Audited, No Change)

**File**: `lib/features/map/presentation/widgets/spatial_filter_bottom_sheet.dart:83`
**File**: `lib/features/public/report/presentation/screens/report_wizard_screen.dart:269`
**Verdict**: Both `Navigator.pop(context)` calls operate within bottom sheet / dialog context. These are overlay routes, not GoRouter routes. Using `NavigationService.pop()` would pop the main navigation stack instead of the overlay. **No change needed** — documented as correct pattern.

---

### H05 — Secure Storage Evaluation

**Current**: `SharedPreferences` stores: `auth_token`, `auth_user_id`, `auth_user_name`, `auth_active_role`, `auth_active_scope_id`, `auth_active_scope_type`, `auth_active_jabatan`
**PRD Requirement**: `flutter_secure_storage` (Laravel Sanctum opaque token)
**Package**: `flutter_secure_storage` not in `pubspec.yaml`
**Recommendation**: Migrate auth persistence to `flutter_secure_storage` before production release.
**Status**: ⏳ Documented — requires dependency addition + refactor of `auth_state_provider.dart`.

---

## Files Modified

| File | Changes |
|------|---------|
| `lib/features/auth/presentation/screens/login_screen.dart` | Removed hardcoded credentials |
| `lib/features/auth/presentation/widgets/pin_verification_dialog.dart` | Removed magic PIN 123456 bypass |
| `lib/features/governance/presentation/screens/executive_dashboard_screen.dart` | Removed hardcoded demo analytics |
| `lib/core/runtime/error_boundary.dart` | Added `WidgetErrorBoundary` class |
| `lib/core/registry/widget_registry.dart` | Wrapped each component with `WidgetErrorBoundary` |
| `lib/features/public/report/presentation/screens/report_wizard_screen.dart` | Added GPS mock detection + upload retry |

## Analyzer Status

```
flutter analyze: 0 error, 0 warning (new)
```

---

## Remaining Work (Documented)

| Priority | Task | Tracking |
|----------|------|----------|
| HIGH | Migrate tokens from SharedPreferences → flutter_secure_storage | `PUBLIC_DOMAIN_GAP_ANALYSIS.md:Auth A09` |
| MEDIUM | Add NewsCard + backend endpoint | `PUBLIC_DOMAIN_GAP_ANALYSIS.md:News P05` |
| MEDIUM | Add Resource screen + backend endpoint | `PUBLIC_DOMAIN_GAP_ANALYSIS.md:Resource P06` |
| MEDIUM | Pull-to-refresh for Dashboard | `PUBLIC_DOMAIN_FIX_PLAN.md:M01` |
| MEDIUM | Phased loading for Dashboard | `PUBLIC_DOMAIN_FIX_PLAN.md:M02` |
| MEDIUM | Resume draft for Report Wizard | `PUBLIC_DOMAIN_FIX_PLAN.md:M07` |
| MEDIUM | Return-to parameter for login | `PUBLIC_DOMAIN_FIX_PLAN.md:M10` |
