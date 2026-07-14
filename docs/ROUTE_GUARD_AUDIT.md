# Route Guard Audit

## GoRouter Redirect Logic

**File**: `mobile/app/lib/core/router/app_router.dart:71-95`

### Current Implementation
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
    return null;
  }

  if (RoutePaths.isProtected(location)) {
    return RoutePaths.home;
  }

  return null;
},
```

### Route Classification
```dart
static const _publicPrefixes = ['/p/', '/splash'];         // Always accessible
static const _authPrefixes = ['/auth/login', '/auth/register'];  // Redirect if authenticated
static const _protectedPrefixes = ['/g/', '/governance/'];  // Redirect if not authenticated
```

### Behavior Matrix

| Location | Authenticated? | activeRole? | Action | Redirect To |
|----------|---------------|-------------|--------|-------------|
| `/splash` | any | any | return null | (stay) |
| `/auth/login` | ✅ Yes | ✅ Not null | redirect | `/g/executive` |
| `/auth/login` | ✅ Yes | ❌ null | redirect | `/p/home` |
| `/auth/login` | ❌ No | - | return null | (stay) |
| `/auth/register` | ✅ Yes | any | redirect | `/p/home` or `/g/executive` |
| `/p/home` | ✅ Yes | any | return null | **(stay — no redirect!)** |
| `/p/profile` | ✅ Yes | any | return null | **(stay — no redirect!)** |
| `/auth/mandate` | ✅ Yes | any | return null | **(stay — not in _authPrefixes!)** |
| `/g/executive` | ❌ No | - | redirect | `/p/home` |
| `/g/executive` | ✅ Yes | any | return null | (stay) |

### Issues Found

#### Issue G1: `/auth/mandate` NOT in `_authPrefixes`
```dart
static const _authPrefixes = ['/auth/login', '/auth/register'];
// '/auth/mandate' is MISSING
```
If an authenticated user navigates to `/auth/mandate`, no redirect happens. The user sees the mandate picker despite already being logged in.

#### Issue G2: Authenticated users on public pages never redirected
When `auth.isAuthenticated && location is public (starts with /p/)`, the guard returns `null` (stay). This means:
- After login → navigated to `/p/home` → stays on public dashboard forever
- User must manually tap "Akun" tab to see AccountHomeScreen
- No auto-navigation to mandate picker or executive after login

#### Issue G3: No mandate-aware redirect
The redirect only distinguishes `activeRole != null`. It doesn't check:
- Whether the user has completed mandate selection
- Whether the user is in guest mode vs mandate mode
- What type of role the user has (for appropriate landing page)

---

## NavigationService Audit

**File**: `mobile/app/lib/core/services/navigation_service.dart`

### Methods
| Method | Uses | Detail |
|--------|------|--------|
| `goHome()` | `_go(RoutePaths.home)` | Route: `/p/home` |
| `goProfile()` | `_go(RoutePaths.profile)` | Route: `/p/profile` (same as Account) |
| `goExecutive()` | `_push(RoutePaths.executive)` | Route: `/g/executive` — uses `_push` not `_go`! |
| `goMandatePicker()` | `_go(RoutePaths.mandate)` | Route: `/auth/mandate` |

### Issue N1: `goExecutive()` uses `_push`, all others use `_go`
```dart
void goExecutive() => _push(RoutePaths.executive);       // PUSH (adds to stack)
void goHome() => _go(RoutePaths.home);                    // GO (replaces current)
```
`_push` adds a new route to the navigation stack. If called multiple times, it creates multiple executive screens on the stack. After logout, if user presses back, they might see a previous executive screen. Should be `_go` for consistency.

### Issue N2: No `goAccountHome()` method
Only `goProfile()` exists (which navigates to `/p/profile` — the same route as AccountHome). No semantic `goAccountHome()` distinction.

---

## Splash Screen Navigation

**File**: `mobile/app/lib/core/splash/splash_screen.dart`

### Logic
```dart
void _tryNavigate() {
  if (_hasNavigated) return;
  if (!_minTimeElapsed) return;

  final auth = ref.read(authStateProvider);
  if (auth.isLoading) return;

  _hasNavigated = true;
  if (auth.isAuthenticated && auth.activeRole != null) {
    nav.goExecutive();
  } else {
    nav.goHome();
  }
}
```

### Issue S1: Splash only checks `activeRole != null`
The splash navigates to executive if `activeRole != null`, else home. But `activeRole` is set during `loginWithDetails()` from the login response. It's set immediately on login. So after first login, on next app restart, splash will see `activeRole != null` and navigate to executive — bypassing the mandate picker entirely.

However, this is consistent with the current design where the mandate picker is never shown anyway.

### Issue S2: Splash listener can conflict with GoRouter redirect
```dart
ref.listen<AuthState>(authStateProvider, (_, auth) {
  if (!auth.isLoading && _minTimeElapsed) {
    _tryNavigate();
  }
});
```
The splash screen has BOTH a `ref.listen` AND the GoRouter redirect. Both can trigger navigation. The `_hasNavigated` guard prevents double-navigation, but the GoRouter redirect may fire first and navigate away before the splash's `_tryNavigate()` fires.
