# Root Cause Analysis

## Sequence Diagram — Actual vs Expected

```
EXPECTED FLOW:
  Splash → Login → POST /auth/login → [Mandates[]] → Mandate Picker → POST /auth/mandate
  → [Active Mandate] → Store Token → AuthState(MandateActive) → Navigation → AccountHome
  → GET /api/account/home (with auth) → Backend BFF → Role → Jabatan → Dynamic Cards → Render

ACTUAL FLOW:
  Splash → Login → POST /auth/login → [NO mandates in response] → goHome()
  → Public Dashboard (/p/home) → User taps "Akun" tab → AccountHome
  → GET /api/account/home (WITHOUT auth middleware on route, but WITH Bearer token)
  → Auth::guard('sanctum')->user() → Backend tries to return cards based on user
```

## GAP ANALYSIS

| Step | Expected | Actual | Broken? |
|------|----------|--------|---------|
| 1. POST /auth/login | Returns token + user + mandates[] | Returns token + user ONLY (no mandates) | ❌ |
| 2. Mandate Picker shown | User selects mandate | **NEVER SHOWN** — login calls goHome() | ❌ |
| 3. POST /auth/mandate | Activates mandate on backend | **ROUTE DOES NOT EXIST** — 404 | ❌ |
| 4. Store active mandate | AuthState has mandate info | AuthState has role from login (not from mandate picker) | ⚠️ |
| 5. Navigate to Account | go to executive or account home | go to /p/home (public dashboard) | ❌ |
| 6. GET /api/account/home | With auth:sanctum middleware | **NO auth middleware** on route | ⚠️ |
| 7. Auth::guard('sanctum') | Returns AuthUser | May return null without middleware | ⚠️ |
| 8. Build cards with mandate | Uses active mandate from context | Uses global role from user.peran | ⚠️ |

---

## PRIMARY ROOT CAUSE

### RC1: `POST /auth/mandate` endpoint does not exist in routes

**File**: `routes/api.php`
**Evidence**: `AuthApiController::selectMandate()` exists at `app/Http/Controllers/Api/AuthApiController.php:65` but NO route maps to it. The only `AuthApiController` route is `POST pin/verify`.

**Impact**: Even if the MandatePickerScreen were triggered, it would get a 404 error. The mandate selection flow is completely dead.

**Severity**: CRITICAL — Complete flow blocker.

---

## SECONDARY ROOT CAUSES

### RC2: Login navigates to public dashboard, not mandate picker

**File**: `mobile/app/lib/features/auth/presentation/screens/login_screen.dart:71`
**Code**: `ref.read(runtimeServicesProvider).navigation.goHome();`
**Impact**: After successful login with token + role, user lands on `/p/home` (public dashboard). No mandate picker is ever shown.

### RC3: Login response does not include mandates array

**File**: `app/Http/Controllers/Api/Auth/AuthenticationApiController.php:59-66`
**Impact**: The MandatePickerScreen expects a `mandates` parameter to render the list. Since mandates are never in the response, the picker can't function.

### RC4: GET /api/account/home has no auth middleware

**File**: `routes/api.php:229-231`
**Impact**: The route has zero middleware. No `auth:sanctum`, no role check, no mandate context. While `Auth::guard('sanctum')->user()` works for authenticated requests, unauthenticated requests silently return guest cards instead of 401.

### RC5: MandatePickerScreen uses publicApiClientProvider

**File**: `mobile/app/lib/features/auth/presentation/screens/mandate_picker_screen.dart:30`
**Code**: `final dio = ref.read(publicApiClientProvider);`
**Impact**: If the `/auth/mandate` endpoint existed, the request would be sent WITHOUT Bearer token (public client strips auth headers). Backend couldn't authenticate the request.

### RC6: Race condition in AuthStateNotifier.build()

**File**: `mobile/app/lib/features/auth/presentation/notifiers/auth_state_provider.dart:63-66`
**Code**:
```dart
AuthState build() {
  _loadState();  // Fire-and-forget async
  return const AuthState(isLoading: true);
}
```
**Impact**: `_loadState()` runs async without await. If login occurs while it's running, state may be overwritten. Also calls `verifySessionWithDatabase()` which may logout if `GET /profile` fails.

### RC7: accountHomeProvider does not watch authStateProvider

**File**: `mobile/app/lib/features/account/presentation/notifiers/account_home_provider.dart`
**Impact**: AccountHomeScreen doesn't auto-refresh when auth state changes (login/logout/mandate switch). User must manually refresh.

### RC8: Router guard doesn't redirect authenticated users from public pages

**File**: `mobile/app/lib/core/router/app_router.dart:79-86`
**Impact**: Authenticated users on `/p/home`, `/p/map`, `/p/report`, `/p/profile` stay there indefinitely. No redirect to executive or account home.

---

## ARCHITECTURE ISSUES

### A1: Two parallel auth controllers
`AuthenticationApiController` (used by Flutter, no mandates) vs `AuthApiController` (has mandates, NOT used by Flutter). The Flutter app calls one, but the mandate logic lives in the other.

### A2: No mandate activation endpoint
The system has OrgMandate models, builders that expect mandates, and a MandatePickerScreen — but no endpoint to activate/select a mandate.

### A3: Account route misclassified
`/api/account/home` is a BFF endpoint that needs authentication, but has no auth middleware. It's treated as a public route.

### A4: Dual navigation sources
Both GoRouter redirect AND SplashScreen `_tryNavigate()` can trigger navigation. No single source of truth for post-auth navigation.

---

## IMPLEMENTATION ISSUES

### I1: Missing route registration
`AuthApiController::selectMandate` (line 65) implemented but never routed. 3-line fix (add route to `routes/api.php`).

### I2: Wrong Dio client in MandatePickerScreen
Uses `publicApiClientProvider` instead of `authApiClientProvider`. Should use authenticated client.

### I3: Login navigation wrong
Login always goes to public dashboard. Should check user role and navigate to appropriate destination.

### I4: No route guard for /auth/mandate
Not included in `_authPrefixes` list, so no redirect logic applies to it.

---

## RUNTIME ISSUES

### R1: verifySessionWithDatabase() can auto-logout
If `GET /api/profile` returns 401/403 (e.g., during network issues), it calls `logout()`. This is overly aggressive — should use more granular error handling.

### R2: _loadState() fire-and-forget
The build method doesn't await `_loadState()`. This means the initial state is always `isLoading: true`, and the actual state arrives later. Any code reading `authStateProvider` in `build()` must handle `isLoading`.
