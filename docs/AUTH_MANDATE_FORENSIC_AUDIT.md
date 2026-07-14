# Auth → Mandate → Account Forensic Audit

## STEP 1 — Auth Login (POST /api/auth/login)

### Controller
`app/Http/Controllers/Api/Auth/AuthenticationApiController.php::login()`

### Route Registration
`routes/api.php:165` — `Route::post('login', [AuthenticationApiController::class, 'login'])`
- **MIDDLEWARE**: None (public route)
- Located inside `Route::prefix('auth')` group, no auth guard

### Payload Sent (Flutter `LoginScreen._login()`)
```dart
dio.post('auth/login', data: {
  'no_hp': _phoneCtrl.text.trim(),
  'kata_sandi': _passCtrl.text,
});
```

### Response Received (Backend)
```json
{
  "success": true,
  "data": {
    "token": "1|abc123...sanctum-token",
    "user": {
      "id_pengguna": 1,
      "no_hp": "08123456789",
      "default_scope_id": "1",
      "default_scope_type": "pcnu",
      "profil": { "nama_lengkap": "Nama User" },
      "peran": { "nama_peran": "pcnu" }
    }
  }
}
```

### Findings
| Item | Status | Detail |
|------|--------|--------|
| Status code | ✅ 200 | Success branch works |
| JWT | ✅ | Sanctum plainTextToken returned |
| Refresh token | ❌ NOT PROVIDED | No refresh token mechanism |
| Expires | ❌ NOT PROVIDED | Sanctum tokens have no expiry by default |
| Mandate list | ❌ **MISSING** | Response does NOT include `mandates[]` array |
| Role list | ✅ | `user.peran.nama_peran` returned |
| User ID | ✅ | `user.id_pengguna` returned |
| Organization ID | ✅ | `user.default_scope_id` returned |
| Mandate picker trigger | ❌ **NEVER TRIGGERED** | Flutter calls `goHome()` immediately — no mandate picker shown |

### CRITICAL ISSUE #1
**Login response does NOT include a `mandates` list.** The MandatePickerScreen expects a `mandates` array from the login response, but `AuthenticationApiController` returns only the user object. The `AuthApiController` (a different controller) DOES return mandates but is never called by Flutter.

### CRITICAL ISSUE #2
**After login, Flutter navigates to `/p/home` (public dashboard)** via `goHome()`. The MandatePickerScreen (`/auth/mandate`) is **never shown**. The `activeRole`, `activeScopeId`, `activeScopeType` are extracted from the login response's user object, not from a mandate selection.

---

## STEP 2 — Mandate Picker

### Controller
`app/Http/Controllers/Api/AuthApiController.php::selectMandate()`

### Route Registration
❌ **NOT REGISTERED**. The route `POST /auth/mandate` does not exist in `routes/api.php`.

The method `AuthApiController::selectMandate()` exists (line 65) but is never mapped to a route. The only `AuthApiController` route is:
```php
Route::post('pin/verify', [AuthApiController::class, 'verifyPin']);
```

### MandatePickerScreen (`mandate_picker_screen.dart`)
```dart
final dio = ref.read(publicApiClientProvider);  // ← PUBLIC client (strips auth headers!)
final res = await dio.post('auth/mandate', data: {
  'user_id': widget.userId,
  'mandate_id': mandateId,
});
```

### Findings
| Item | Status | Detail |
|------|--------|--------|
| Route exists | ❌ **404** | `POST /auth/mandate` is NOT in `routes/api.php` |
| Uses correct Dio client | ❌ **WRONG** | Uses `publicApiClientProvider` which **strips Authorization headers** |
| Mandate sent to backend | ❌ **NEVER REACHES** | Route doesn't exist |
| Response parsed | ❌ N/A | Never executed |
| `setMandate()` called | ❌ **NEVER** | MandatePickerScreen is never rendered |

### CRITICAL ISSUE #3
**`POST /auth/mandate` endpoint does not exist.** The `MandatePickerScreen` will always get a 404 error, showing a SnackBar "Gagal memilih mandat". Even if the route existed, `publicApiClientProvider` would strip the Bearer token, making authentication impossible.

### CRITICAL ISSUE #4
**MandatePickerScreen is never rendered.** There is zero code that navigates to `/auth/mandate` after login. The `LoginScreen` always calls `goHome()`.

---

## STEP 3 — Token Storage

### Storage Locations
| Storage | Used For | Status |
|---------|----------|--------|
| FlutterSecureStorage | Token + user details + mandate | ✅ Used in `AuthStateNotifier` |
| Riverpod (AuthState) | Runtime state | ✅ Provider holds current state |
| Memory (Dio interceptors) | Per-request headers | ✅ Read from `authStateProvider` |

### Secure Storage Keys (AuthStateNotifier)
```dart
auth_token          → stored, read, deleted on logout
auth_user_id        → stored, read, deleted on logout
auth_user_name      → stored, read, deleted on logout
auth_active_role    → stored, read, deleted on logout
auth_active_scope_id → stored, read, deleted on logout
auth_active_scope_type → stored, read, deleted on logout
auth_active_jabatan → stored, read, deleted on logout
```

### Findings
| Item | Status | Detail |
|------|--------|--------|
| Old token reused | ❌ **POTENTIAL** | `verifySessionWithDatabase()` calls `GET profile` on every app start — if 401, calls `logout()` |
| Race condition on login | ⚠️ **EXISTS** | `build()` calls `_loadState()` (async, no await). If login happens before `_loadState` completes, state may be overwritten |
| Token in SecureStorage | ✅ | `flutter_secure_storage` used |
| Storage cleared on logout | ✅ | `_storage.deleteAll()` called |
| Concurrent writes | ⚠️ **POSSIBLE** | `loginWithDetails()` and `_loadState()` can run concurrently — no mutex/lock |

### CRITICAL ISSUE #5
**Race condition in AuthStateNotifier.** `build()` fires `_loadState()` as fire-and-forget (no await). If login occurs before `_loadState()` completes, the `_loadState()` callback may overwrite the just-set login state:
```dart
@override
AuthState build() {
  _loadState();  // ← fire and forget, no await
  return const AuthState(isLoading: true);
}
```
Meanwhile, `_loadState()` is async. If token exists in storage, it calls `verifySessionWithDatabase()` which does a network call to `GET profile`. If this takes 2 seconds and user logs in during that window, the state could be clobbered.

---

## STEP 4 — AuthState Provider

### State Class (`auth_state_provider.dart:7-55`)
```dart
class AuthState {
  bool isAuthenticated;
  bool isLoading;
  String? token;
  String? userId;
  String? userName;
  String? activeRole;      // Lapis 1 (Role Global)
  String? activeScopeId;   // Lapis 3 (Scope Wilayah)
  String? activeScopeType; // e.g. pcnu, pwnu
  String? activeJabatan;   // Lapis 2 (Jabatan Struktural)
}
```

### State Transitions
| Event | `isAuthenticated` | `activeRole` | `isLoading` |
|-------|-------------------|--------------|-------------|
| App start (build) | false | null | true |
| After `_loadState()` with token | true | from storage | false |
| After `loginWithDetails()` | true | from login response | false (unchanged) |
| After `setMandate()` | true (unchanged) | updated | false (unchanged) |
| After `logout()` | false | null | false |

### Findings
| Item | Status | Detail |
|------|--------|--------|
| Authenticated flag set | ✅ | Set in `loginWithDetails()` |
| MandateActive flag | ❌ **NO SEPARATE FLAG** | Only `activeRole` field, no `isMandateActive` boolean |
| Provider changes to Authenticated+Mandate | ❌ **INCOMPLETE** | State goes from loading → authenticated in one step. No distinction between "has token" vs "has mandate" |
| Session recovery | ⚠️ | `verifySessionWithDatabase()` can auto-logout if profile 403 |

### CRITICAL ISSUE #6
**No `isMandateActive` state field.** The `AuthState` has no boolean to distinguish "user has a valid token" from "user has activated a mandate". The GoRouter redirect uses `activeRole != null` as a proxy for "has mandate", but `activeRole` is set during `loginWithDetails()` from the login response's `user.peran.nama_peran` — which is the GLOBAL role, not the mandate-specific role.

---

## STEP 5 — Navigation

### Navigation Flow After Login
```
LoginScreen._login() 
  → authStateProvider.notifier.loginWithDetails(...)
  → runtimeServicesProvider.navigation.goHome()     // /p/home
```

### Navigation Service Methods Used
```dart
void goHome() => _go(RoutePaths.home);              // /p/home
void goExecutive() => _push(RoutePaths.executive);   // /g/executive
void goMandatePicker(...) => _go(RoutePaths.mandate); // /auth/mandate
void goLogin() => _push(RoutePaths.login);
```

### Findings
| Step | Navigation Call | Expected | Actual |
|------|----------------|----------|--------|
| After login | `goHome()` | Mandate Picker or Executive | Public Dashboard |
| After mandate pick | `goToExecutive()` (in MandatePickerScreen) | Executive Dashboard | Never reached |
| Splash (fresh) | `nav.goExecutive()` if `activeRole != null` | Executive Dashboard | Depends on stored state |
| Splash (no session) | `nav.goHome()` | Public Dashboard | ✅ Correct |

### CRITICAL ISSUE #7
**Login → goHome() bypasses mandate picker.** The `LoginScreen` should either:
- Navigate to `/auth/mandate` (if mandates list exists in login response)
- Or navigate to executive/account home directly

Currently it always goes to the public dashboard.

### CRITICAL ISSUE #8
**GoRouter redirect for `/p/` routes always returns null.** The route guard only redirects:
- From `/auth/*` (to executive or home)
- To `/p/home` when accessing protected routes without auth

An authenticated user on `/p/home` stays there indefinitely with no redirect to executive or account home.

---

## STEP 6 — Account Home (GET /api/account/home)

### Route Registration
```php
Route::prefix('account')->group(function () {
    Route::get('home', [AccountHomeController::class, 'index']);
});
```

**MIDDLEWARE**: NONE. No `auth:sanctum`, no `role`, no `mandate.context`.

### Controller (`AccountHomeController.php`)
```php
public function index(Request $request): JsonResponse
{
    $user = Auth::guard('sanctum')->user();  // Optional auth
    $cards = $this->dashboardService->getCards($user);
    return response()->json([
        'success' => true,
        'data' => ['cards' => $cards],
    ]);
}
```

### Flutter Provider (`account_home_provider.dart`)
```dart
Future<AccountHomeData> build() async {
  final repository = ref.read(accountRepositoryProvider);
  final dio = ref.read(authApiClientProvider);     // Auth client — sends Bearer token
  return repository.getAccountHome(dio: dio);
}
```

### Findings
| Item | Status | Detail |
|------|--------|--------|
| Authorization Header | ✅ SENT | `authApiClientProvider` injects Bearer token from `authStateProvider` |
| Route middleware | ❌ **MISSING** | No `auth:sanctum` middleware on route |
| Auth guard in controller | ⚠️ Optional | `Auth::guard('sanctum')->user()` can return null |
| Backend authenticates | ⚠️ **UNRELIABLE** | Without `auth:sanctum` middleware, Sanctum may not validate token depending on config |
| Guest detection | ✅ | Returns guest cards when user is null |
| Error handling | ✅ | DioExceptionMapper used |

### CRITICAL ISSUE #9
**Route `GET /api/account/home` has NO `auth:sanctum` middleware.** While `Auth::guard('sanctum')->user()` may work for token-based auth, the lack of middleware means:
1. No automatic 401 response if token is missing/invalid
2. No `request->user()` hydration by Laravel's auth pipeline
3. `Auth::guard('sanctum')->user()` depends on the middleware stack having `StartSession` and `EncryptCookies` configured for API — which may not be the case

---

## STEP 7 — 403 Analysis

### Categories vs Actual Configuration
| Category | Present? | Detail |
|----------|----------|--------|
| A: Token not sent | ⚠️ POSSIBLE | Only if `authApiClientProvider` interceptor fails to read `authStateProvider` |
| B: Token expired | ❌ NO | Sanctum tokens have no expiry |
| C: Mandate not active | ❌ **N/A** | AccountHome route has no mandate middleware | 
| D: Policy Laravel | ❌ NO | No policy applied to AccountHomeController |
| E: Sanctum | ❌ NO | No `auth:sanctum` middleware on route |
| F: Permission | ❌ NO | No permission check in controller |
| G: Middleware | ❌ NO | Route has zero middleware |
| H: Role mismatch | ❌ NO | No role check |
| I: Organization mismatch | ❌ NO | No scope/organization check |
| J: Route wrong middleware | ❌ **N/A** | Route has no middleware to be wrong about |

### ACTUAL 403 SOURCE
The **only** source of 403 in this flow is `verifySessionWithDatabase()` calling `GET /api/profile` which may return 403 if the token is invalid. But since both `/api/profile` and `/api/account/home` have NO auth middleware, 403 is unlikely.

If a 403 DOES occur, the most likely cause is Sanctum's token validation failing because the route lacks `auth:sanctum` middleware — but Sanctum doesn't return 403; it returns 401 via AuthenticationException, or it silently returns null for `Auth::user()`.

---

## STEP 8 — Laravel Route/Middleware Audit

### Route File: `routes/api.php`
| Endpoint | Middleware | Auth Guard | Protected |
|----------|-----------|------------|-----------|
| `POST /api/auth/login` | None | Optional | No |
| `GET /api/profile` | None | Optional | No |
| `GET /api/account/home` | **NONE** | **Optional** | **No** |
| All `/api/operasi/*` | `auth:sanctum,role:...` | Required | Yes |
| All `/api/admin/*` | `auth:sanctum,role:...` | Required | Yes |
| All `/api/governance/*` | `auth:sanctum,role:...` | Required | Yes |

### FINDING
`GET /api/account/home` is a **public route** with the same access level as login and profile. Any unauthenticated request can reach the controller. The controller handles this gracefully (returns guest cards), but there's no enforced authentication.

---

## STEP 9 — Controller Audit

### AccountHomeController
```php
$user = Auth::guard('sanctum')->user();
$cards = $this->dashboardService->getCards($user);
```

### Issues
1. ✅ Controller correctly uses `Auth::guard('sanctum')` — no `request()->user()` (which would fail without middleware)
2. ⚠️ `Auth::guard('sanctum')->user()` behavior depends on whether the Sanctum middleware has run. Without `auth:sanctum` on the route, Sanctum's `EnsureFrontendRequestsAreStateful` middleware may not have executed
3. ❌ No middleware to ensure the user IS authenticated before returning cards

---

## STEP 10-11 — AccountDashboardService & Card Builders

### Service (`AccountDashboardService.php`)
- Takes `?AuthUser $user` (nullable)
- If `$user === null`, returns guest cards
- Otherwise builds: identity, mandate, statistics, quick_actions, tasks, approvals, resources, activities, notifications, settings

### Builders
| Builder | Uses Current Mandate? | Detail |
|---------|----------------------|--------|
| IdentityCardBuilder | ✅ Uses `$user->profil` | Reads from AuthUser model |
| MandateCardBuilder | ✅ Uses `$user->jabatanAktif()` | Gets active positions from DB |
| StatisticsCardBuilder | ✅ Uses `$user->peran->nama_peran` | Role-based KPIs |
| QuickActionCardBuilder | ✅ Uses `$user->peran->nama_peran` | Via QuickActionService |
| TaskCardBuilder | ✅ Uses `DecisionQueueService` | Tasks based on user |
| ApprovalCardBuilder | ✅ Uses `$user->peran->nama_peran` | Only for pcnu/pwnu/super_admin |
| ResourceCardBuilder | ✅ DB queries | Global resource counts |
| ActivityCardBuilder | ✅ `$user->penugasanAktif()` | User's assignments |

### Findings
✅ Builders correctly use the authenticated user from `Auth::guard('sanctum')`.
⚠️ Most builders depend on `$user->peran->nama_peran` (global role), NOT the active mandate from `OrgMandate`. The mandate picker flow is completely bypassed.

---

## STEP 12-13 — Flutter Account Provider & Riverpod

### AccountHomeProvider
```dart
final accountHomeProvider = AsyncNotifierProvider<AccountHomeNotifier, AccountHomeData>(AccountHomeNotifier.new);

class AccountHomeNotifier extends AsyncNotifier<AccountHomeData> {
  Future<AccountHomeData> build() async {
    final dio = ref.read(authApiClientProvider);
    return repository.getAccountHome(dio: dio);
  }
}
```

### Findings
| Item | Status | Detail |
|------|--------|--------|
| Provider auto-dispose | ❌ NO | `AsyncNotifierProvider` without autoDispose — persists |
| Provider invalidated on login | ❌ NO | No `ref.invalidate(accountHomeProvider)` call anywhere |
| Provider watches auth state | ❌ NO | Doesn't watch `authStateProvider` — won't auto-refresh on mandate change |
| Dio sends token | ✅ | `authApiClientProvider` interceptor reads `authStateProvider` |
| Error handled | ✅ | `account_home_screen.dart` has error state with retry |

### CRITICAL ISSUE #10
**`accountHomeProvider` does NOT watch `authStateProvider`.** If the auth state changes (e.g., mandate switch, login, logout), the provider doesn't automatically refresh. It only fetches once on first build. If the user switches mandate, the account page still shows old data until manual refresh.

---

## STEP 14 — GoRouter Redirect

### Current Redirect Logic (`app_router.dart:71-95`)
```dart
redirect: (context, state) {
  final location = state.uri.toString();
  final auth = ref.read(authStateProvider);

  if (location == RoutePaths.splash) return null;
  if (auth.isLoading) return null;

  if (auth.isAuthenticated) {
    if (RoutePaths.isAuthPage(location)) {
      return auth.activeRole != null ? RoutePaths.executive : RoutePaths.home;
    }
    return null;  // ← Allows authenticated users on ANY non-auth page
  }

  if (RoutePaths.isProtected(location)) {
    return RoutePaths.home;
  }

  return null;
}
```

### Findings
| Scenario | Actual Behavior | Expected Behavior |
|----------|----------------|-------------------|
| Unauthenticated on `/p/home` | Stays (`return null`) | ✅ OK |
| Unauthenticated on `/g/executive` | Redirects to `/p/home` | ✅ OK |
| Authenticated on `/auth/login` | Redirects to `/g/executive` (if role) or `/p/home` | ✅ OK |
| Authenticated on `/p/home` | **STAYS** (`return null`) | ⚠️ OK for public dashboard, but might want to redirect to account |
| Authenticated on mandate picker | **STAYS** (`return null`) | ❌ `/auth/mandate` is not in `_authPrefixes` — no redirect |

### CRITICAL ISSUE #11
**`/auth/mandate` is NOT in `RoutePaths._authPrefixes`.** The route guard treats `/auth/mandate` as a regular route. If an authenticated user lands on it, they stay there. Also, the route guard only checks `RoutePaths.isAuthPage(location)` which tests against `['/auth/login', '/auth/register']` — `/auth/mandate` is not in this list.

---

## STEP 15 — Lifecycle

### AppLifecycleService
- Registered in `RuntimeInitializer`
- Tracks app lifecycle phases (resumed, paused, etc.)
- No effect on auth state or provider disposal

### Findings
✅ Login does not cause provider dispose. Providers are scoped to the `ProviderScope` in `main.dart` and persist.
⚠️ `verifySessionWithDatabase()` runs on every app resume via `_loadState()`, which may cause unexpected logout if network is slow.
