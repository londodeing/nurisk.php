# Riverpod State Flow Audit

## Provider Dependency Graph

```
authStateProvider (NotifierProvider<AuthStateNotifier, AuthState>)
  ↑ read by:
    ├── authApiClientProvider (Provider<Dio>) — via interceptor
    ├── appRouterProvider (Provider<GoRouter>) — via redirect callback
    ├── LoginScreen — via login()
    ├── MandatePickerScreen — via setMandate()
    ├── SplashScreen — via _tryNavigate()
    ├── AccountHomeScreen — via main.dart listener (logout redirect)
    └── NavigationService — indirect via router

profileProvider (AsyncNotifierProvider<ProfileNotifier, ProfileData?>)
  ↑ read by:
    ├── ProfileScreen (OLD — still exists but no longer in router)
    └── ReportWizardScreen — phone autofill

accountHomeProvider (AsyncNotifierProvider<AccountHomeNotifier, AccountHomeData>)
  ↑ read by:
    ├── AccountHomeScreen — via ref.watch()
    └── NO ONE ELSE

accountRepositoryProvider (Provider<AccountRepository>)
  ↑ read by:
    ├── accountHomeProvider
    └── NO ONE ELSE

accountRemoteDatasourceProvider (Provider<AccountRemoteDatasource>)
  ↑ read by:
    ├── accountRepositoryProvider
    └── NO ONE ELSE
```

## AuthStateProvider State Transitions

### Sequence A: Fresh Login
```
1. App start → AuthStateNotifier.build()
     → _loadState() called (fire-and-forget)
     → returns AuthState(isLoading: true)

2. _loadState() reads FlutterSecureStorage
     → No token found
     → state = AuthState(isLoading: false)

3. User logs in → LoginScreen._login()
     → POST /api/auth/login → 200
     → authStateProvider.notifier.loginWithDetails(token, userId, userName, role, ...)
       → Writes to FlutterSecureStorage
       → state = AuthState(isAuthenticated: true, isLoading: false, token: "...", activeRole: "pcnu", ...)

4. Meanwhile, _loadState() from step 1 may still be running
     → If it completes AFTER step 3, it reads FROM SECURE STORAGE again
     → Since loginWithDetails already wrote to storage, _loadState reads the new values
     → No data loss (but wasted network call for verifySessionWithDatabase)
```

### Sequence B: App Restart (Already Logged In)
```
1. App start → AuthStateNotifier.build()
     → _loadState() called (fire-and-forget)
     → returns AuthState(isLoading: true)

2. _loadState() reads FlutterSecureStorage
     → Token found!
     → state = AuthState(isAuthenticated: true, isLoading: false, token: "...", activeRole: "pcnu", ...)

3. verifySessionWithDatabase() called
     → GET /api/profile
     → If 401/403 → logout() → state = AuthState(isLoading: false)
     → If 200 → _verifyAttempts = 0 (success)

4. GoRouter redirect fires
     → auth.isAuthenticated = true, activeRole = "pcnu"
     → If on auth page → redirect to executive
```

### Sequence C: Concurrent Access
```
Thread 1: _loadState() running in background
            → await storage.read('auth_token') → "old_token"
            → await storage.read('auth_user_id') → "1"
            → ...
            → STATE UPDATE: AuthState(token: "old_token", ...)  ← OVERWRITES

Thread 2: login() called by user
            → loginWithDetails("new_token", "1", ...)
            → FlutterSecureStorage.write(...)  ← writes new values
            → STATE UPDATE: AuthState(token: "new_token", ...)
```

**Race condition exists**: If `_loadState()` completes AFTER `loginWithDetails()`, it reads from storage again. Since `loginWithDetails()` already wrote new values to storage, `_loadState()` would read them and overwrite state with the SAME values — so no actual data loss. But `verifySessionWithDatabase()` will be called from the stale `_loadState()` context, potentially causing a spurious logout if the old network call fails.

## accountHomeProvider Behavior

### Build Trigger
`accountHomeProvider` builds when first watched by `AccountHomeScreen`. It:
1. Reads `accountRepositoryProvider`
2. Reads `authApiClientProvider` (which reads `authStateProvider`)
3. Calls `GET /api/account/home`

### Auto-refresh Triggers
| Event | Auto-refresh? | Detail |
|-------|--------------|--------|
| Login | ❌ NO | Provider was not watching before login (screen not mounted) |
| Mandate switch | ❌ NO | Provider does NOT watch `authStateProvider` |
| Logout | ❌ NO | Provider doesn't listen for auth changes |
| Screen revisit | ✅ YES | `AsyncNotifierProvider` builds once and caches. But if user navigates away and back, Riverpod returns cached value — does NOT refetch |
| Manual pull-to-refresh | ✅ YES | `refresh()` method exists and called by refresh button |

### Watch Chain
```
AccountHomeScreen.build()
  → ref.watch(accountHomeProvider)
    → accountHomeProvider.build()
      → ref.read(authApiClientProvider)
        → authStateProvider (read inside Dio interceptor, not watched)
      → repository.getAccountHome(dio: dio)
        → datasource.getAccountHome(dio: dio)
          → dio.get('account/home')
```

**Key observation**: `accountHomeProvider` does NOT `ref.watch(authStateProvider)`. It only `ref.read(authApiClientProvider)`. This means the provider never rebuilds when auth state changes. If the user logs out and logs in as a different user, the account home still shows the old user's data until manual refresh.

## Provider Lifecycle

| Provider | Type | AutoDispose? | Lifetime |
|----------|------|-------------|----------|
| `authStateProvider` | `NotifierProvider` | No | App lifetime |
| `accountHomeProvider` | `AsyncNotifierProvider` | No | Until navigated away from AccountHomeScreen |
| `accountRepositoryProvider` | `Provider` | No | App lifetime |
| `accountRemoteDatasourceProvider` | `Provider` | No | App lifetime |
| `authApiClientProvider` | `Provider` | No | App lifetime |

**All providers survive login — no unintended disposal.**
