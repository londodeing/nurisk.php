# Laravel Auth Pipeline Audit

## Middleware Stack for /api/account/home

### Current Route Definition
```php
// routes/api.php:229-231
Route::prefix('account')->group(function () {
    Route::get('home', [AccountHomeController::class, 'index']);
});
```

### Middleware Applied: NONE

The route has:
- ❌ No `auth:sanctum` — Sanctum token authentication NOT enforced
- ❌ No `role` — Role check NOT enforced
- ❌ No `mandate.context` — Mandate resolution NOT performed
- ❌ No `scope` — Scope isolation NOT enforced
- ❌ No `CheckAccountStatus` — Account active check NOT performed
- ❌ No `ResolveMandateContext` — Mandate NOT resolved/injected into request

### Contrast with Protected Routes
```php
// routes/api.php:236
Route::middleware(['auth:sanctum', 'role:super_admin,pwnu,pcnu,relawan,trc'])->group(function () {
    // All operational, governance, and admin routes
});
```

### What Auth::guard('sanctum')->user() Does Without Middleware

Without the `auth:sanctum` middleware on the route, Sanctum's authentication still works at the guard level IF the middleware has been added to the `api` middleware group in `bootstrap/app.php` (Laravel 11+) or `app/Http/Kernel.php` (Laravel <11).

Sanctum works via:
1. The `EnsureFrontendRequestsAreStateful` middleware (for SPA/API hybrid)
2. The `auth:sanctum` route middleware (for token validation on protected routes)
3. Direct `Auth::guard('sanctum')->user()` call (reads token from Authorization header)

Without the route middleware, option 3 still works because `Auth::guard('sanctum')` reads the `Authorization: Bearer <token>` header from the request directly. However:

- **No 401 is returned** if token is missing or invalid — request proceeds with `$user = null`
- **No AuthenticateException** is thrown
- **No user hydration** for `$request->user()` (only `Auth::guard('sanctum')->user()` works)

---

## How the Mandate Pipeline SHOULD Work

### Complete Auth Pipeline for Account Home
```
1. Request arrives at /api/account/home
2. auth:sanctum middleware:
     → Reads Authorization: Bearer <token>
     → Validates token against personal_access_tokens table
     → Hydrates AuthUser into $request->user() / Auth::user()
     → Returns 401 if invalid/missing
3. CheckAccountStatus middleware (optional):
     → Verifies user.status_akun === 'aktif'
     → Verifies user has active mandate
4. ResolveMandateContext middleware (optional):
     → Calls MandateResolverService::getPrimaryMandate($user)
     → Injects _mandate into request attributes
5. AccountHomeController:
     → $user = Auth::user() (hydrated by Sanctum)
     → $mandate = $request->get('_mandate') (resolved by MandateContext)
     → Builds cards based on mandate + user
```

### Current (Broken) Pipeline
```
1. Request arrives at /api/account/home
2. NO MIDDLEWARE — request passes through
3. AccountHomeController:
     → $user = Auth::guard('sanctum')->user()
       → May return null if Sanctum didn't auto-authenticate
     → NO mandate context available
     → Builds cards with limited data (no mandate resolution)
```

---

## Sanctum Configuration Check

### personal_access_tokens table
Must exist for token validation. Migrations should have been run.

### AuthUser model
Must use `Laravel\Sanctum\HasApiTokens` trait:
```php
// Already confirmed: AuthUser uses HasApiTokens
```

### Auth config (config/auth.php)
```php
'guards' => [
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

Without `auth:sanctum` middleware, Sanctum falls back to reading the `Authorization` header directly. This usually works but is not guaranteed — the exact behavior depends on the Laravel version and kernel configuration.

---

## All Middleware Available (Not Applied to Account Home)

| Middleware | File | Would Apply If Added |
|-----------|------|---------------------|
| `auth:sanctum` | Laravel/Sanctum | ✅ Token validation + 401 |
| `role:super_admin,pwnu,...` | RoleMiddleware.php | ✅ Role check |
| `mandate.context` | ResolveMandateContext.php | ✅ Mandate resolution |
| `scope` | ScopeMiddleware.php | ✅ Scope isolation |
| `CheckAccountStatus` | CheckAccountStatus.php | ✅ Account active check |
