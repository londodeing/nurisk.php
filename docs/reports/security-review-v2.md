# Security Review — Phase 15.6

**Project:** NURISK (Laravel Disaster Response App)  
**Date:** 2026-06-20  
**Scope:** Sanctum, Token Lifecycle, Authorization Middleware, Rate Limiting, API Abuse, Missing Protections

---

## 1. Sanctum Review

### Finding 1.1 — 30-Day Token Expiry
**Description:** Token expiry is configured at 30 days (`config/sanctum.php:53`):
```php
'expiration' => env('SANCTUM_TOKEN_EXPIRATION_DAYS', 30) * 24 * 60,
```
For a disaster response mobile app running on potentially unreliable networks, 30 days is reasonable — it avoids forcing re-authentication during field operations. However, there is no mechanism to force token refresh (e.g., no refresh-token rotation for Sanctum tokens). The device-level `DeviceAuthController::refreshToken()` issues a separate custom `device_token` (SHA-256 hashed, 30-day TTL) independent of Sanctum.

**Risk:** P3 — Tokens that are compromised remain valid for up to 30 days with no ability to rotate them without explicit revocation.

**Recommendation:** Consider implementing token rotation on sync or a dedicated token-refresh endpoint that re-issues Sanctum tokens with a fresh expiry. Alternatively, add a token-version column to allow server-enforced re-authentication on sensitive operations.

**Effort:** Medium  
**Reference:** `config/sanctum.php:53`, `app/Http/Controllers/Api/Auth/DeviceAuthController.php:47`

### Finding 1.2 — Token Abilities Not Used
**Description:** Sanctum token abilities (`tokenCan()` / `can()` checks) are never used. Tokens are created with `['*']` in tests (`tests/Feature/DeviceApiTest.php:30`, `tests/Feature/SecurityRegressionTest.php:114`) and no `$ability` parameter is passed in console commands (`app/Console/Commands/ProfileSyncEndpoints.php:113`). Authorization is fully delegated to `RoleMiddleware` (hierarchy-based) and `ScopeMiddleware` (region isolation).

**Risk:** P3 — While the delegation to RoleMiddleware is correct, the absence of ability checks means all tokens are functionally equivalent regardless of how they were issued. Any valid Sanctum token grants access to all routes within the role/scope guard.

**Recommendation:** Either explicitly document that abilities are intentionally unused (with rationale), or define fine-grained ability sets per role and validate them alongside the role middleware. Current approach is acceptable if layered middleware (role + scope) is considered sufficient.

**Effort:** Low (documentation) / High (implementation)  
**Reference:** `app/Http/Middleware/RoleMiddleware.php`, `app/Http/Middleware/ScopeMiddleware.php`

### Finding 1.3 — Token Prefix
**Description:** Sanctum tokens use the prefix `nurisk_` configured via `config/sanctum.php:68`:
```php
'token_prefix' => env('SANCTUM_TOKEN_PREFIX', 'nurisk_'),
```
This is good practice — it enables GitHub secret scanning and makes DB tokens easily identifiable.

**Risk:** None — correctly configured.

**Recommendation:** No action needed.

**Effort:** N/A  
**Reference:** `config/sanctum.php:68`

### Finding 1.4 — Expired Token Pruning Not Scheduled
**Description:** The console scheduling file (`routes/console.php`) registers three scheduled commands — `sync:prune-tombstones`, `queue:prune-failed`, and `nurisk:aggregate-metrics` — but does **not** schedule `sanctum:prune-expired`. Expired Sanctum tokens (past `expires_at`) accumulate indefinitely in the `personal_access_tokens` table.

```php
// routes/console.php — missing:
// Schedule::command('sanctum:prune-expired')->daily();
```

**Risk:** P2 — Over time, expired tokens bloat the `personal_access_tokens` table, degrading query performance on token lookups and wasting storage. In high-volume deployments this can become a maintenance issue.

**Recommendation:** Add `Schedule::command('sanctum:prune-expired')->daily()` to `routes/console.php`. Consider a 24-hour grace period (default) or 7 days for auditability.

**Effort:** Low  
**Reference:** `routes/console.php:5-15`

---

## 2. Token Lifecycle

### Finding 2.1 — No Sanctum Token Issuance Endpoint for Mobile API
**Description:** The API routes (`routes/api.php:25`) require `auth:sanctum` middleware, but there is **no API endpoint** that issues Sanctum bearer tokens to mobile clients. Token creation only occurs in:
- Test files (`$user->createToken(...)` in `tests/Feature/`)
- CLI commands (`app/Console/Commands/ProfileSyncEndpoints.php:113`, `app/Console/Commands/QueryProfileCommand.php:35`)

The web login (`app/Http/Controllers/Auth/LoginController.php`) uses session-based auth and does not issue tokens. The public `POST /api/v1/device/refresh-token` endpoint (`app/Http/Controllers/Api/Auth/DeviceAuthController.php`) issues a custom SHA-256 `device_token`, not a Sanctum token.

**Risk:** P1 — Mobile clients cannot obtain valid Sanctum tokens through any defined API endpoint. This is a critical gap — if the mobile app relies on Sanctum for API auth, it cannot function beyond test/CLI flows. It is unclear how tokens are provisioned in production.

**Recommendation:** Implement a Sanctum token-issuance endpoint (e.g., `POST /api/v1/auth/token`) that authenticates via phone/password and returns a plain-text token. Consider integrating this with the existing `device_uuid` column for device-level token tracking. Alternatively, if the custom `device_token` is the intended auth mechanism, document this and update the middleware stack accordingly.

**Effort:** Medium  
**Reference:** `routes/api.php:25`, `app/Http/Controllers/Api/Auth/DeviceAuthController.php`, `app/Http/Controllers/Auth/LoginController.php`

### Finding 2.2 — `device_uuid` Not Populated at Token Creation
**Description:** A migration (`database/migrations/2026_06_20_110340_add_device_uuid_to_personal_access_tokens.php`) adds a nullable `device_uuid` column to the `personal_access_tokens` table. The `DeviceApiController::destroy()` method (`app/Http/Controllers/Api/Device/DeviceApiController.php:47`) relies on this column:
```php
$user->tokens()->where('device_uuid', $uuid)->delete();
```

However, **no code populates `device_uuid` when a Sanctum token is created**. The test (`tests/Feature/DeviceApiTest.php:63-65`) manually sets it:
```php
$deviceToken->accessToken->device_uuid = $device->uuid_device;
$deviceToken->accessToken->save();
```
This means every token has `device_uuid = null`, so `DeviceApiController::destroy()` and `logoutAll()` cannot selectively revoke tokens by device — they match nothing.

**Risk:** P1 — Device-level token revocation is effectively broken. The `destroy()` call will never delete any tokens because `device_uuid` is always null. `logoutAll()` still works because it calls `$user->tokens()->delete()` without filtering by `device_uuid`.

**Recommendation:** Populate `device_uuid` at token-creation time. Either:
- Extend `createToken()` on `AuthUser` to accept an optional `device_uuid` parameter (override `HasApiTokens::createToken()`)
- Or set it via an observer/listener on the `tokenCreating` event

**Effort:** Medium  
**Reference:** `app/Http/Controllers/Api/Device/DeviceApiController.php:47`, `database/migrations/2026_06_20_110340_add_device_uuid_to_personal_access_tokens.php`, `app/Models/AuthUser.php:16`

### Finding 2.3 — Token Expiry 30-Day Default
**Description:** As noted in Finding 1.1, the 30-day expiry is reasonable for mobile field use. The `DeviceAuthController` also issues a separate custom device token with the same 30-day TTL (`app/Http/Controllers/Api/Auth/DeviceAuthController.php:47`):
```php
$expiresAt = now()->addDays(30);
```

**Risk:** P3 — No forced token rotation on privilege escalation. If a user is promoted from `relawan` to `pcnu`, their existing tokens retain the old role context until expiry.

**Recommendation:** Implement a token-versioning mechanism or force token refresh on role/scope changes in `AuthUserObserver`.

**Effort:** Medium  
**Reference:** `app/Http/Controllers/Api/Auth/DeviceAuthController.php:47`, `config/sanctum.php:53`

### Finding 2.4 — AuthUserObserver Token Revocation
**Description:** `AuthUserObserver` (`app/Observers/AuthUserObserver.php`) correctly revokes all Sanctum tokens when:
- Account status changes from `aktif` to any other status (`updated` event, line 12)
- Account is deleted / force-deleted (`deleted` / `forceDeleted` events, lines 23, 33)

This covers deactivation, suspension, and account removal.

**Risk:** None — correctly implemented. Tokens are revoked on all non-active status transitions and deletion.

**Recommendation:** No action needed — this is well-implemented.

**Effort:** N/A  
**Reference:** `app/Observers/AuthUserObserver.php`

---

## 3. Privilege Escalation

### Finding 3.1 — RoleMiddleware Uses `>=` Hierarchy Check
**Description:** `RoleMiddleware` (`app/Http/Middleware/RoleMiddleware.php`) uses a hierarchical `>=` comparison:
```php
private const ROLE_HIERARCHY = [
    'super_admin' => 100,
    'pwnu'        => 80,
    'pcnu'        => 60,
    'relawan'     => 40,
];
// ...
if ($userLevel >= $requiredLevel) {
    $allowed = true;
}
```

This means **any role with level >= required level passes**. For example, on `role:relawan`, a relawan (40) passes because 40 >= 40. A pcnu (60) also passes. On `role:super_admin,pwnu,pcnu,relawan`, every role passes because the lowest required level is 40.

The route group (`routes/api.php:25`) uses:
```php
Route::middleware(['auth:sanctum', 'role:super_admin,pwnu,pcnu,relawan'])->group(function () { ... });
```

**Risk:** P2 — All four roles listed means the hierarchy check is redundant for API routes (every role passes). The actual risk is:
- If an admin configures a narrow route like `role:super_admin` but the DB has `level_otoritas` for a non-super_admin set incorrectly to >= 100, that user gains super_admin access.
- The dual-match logic (hierarchy check AND exact-match check) can produce unexpected results: if a user's role name matches one of the allowed roles, they pass regardless of their hierarchy level.

**Recommendation:** Add validation in the `AuthRole` model/seed to enforce correct `level_otoritas` values (super_admin=100, pwnu=80, pcnu=60, relawan=40). Consider adding a database constraint or a form rule. For API routes, consider whether all four roles genuinely need access to every endpoint.

**Effort:** Low  
**Reference:** `app/Http/Middleware/RoleMiddleware.php:12-17, 40`

### Finding 3.2 — `level_otoritas` Integrity
**Description:** The `level_otoritas` value comes from the `auth_roles` table via `$user->peran->level_otoritas`. There is no guard against this value being set arbitrarily in the database (no validation in model `$fillable` for `AuthRole`, no DB constraint on value range).

**Risk:** P2 — If `level_otoritas` is ever set incorrectly (e.g., 100 for a `pcnu` role), the hierarchical check grants full access. This could happen through:
- Direct DB manipulation
- A bug in role management UI
- Data migration errors

**Recommendation:** Add a validation rule/observer on `AuthRole` that restricts `level_otoritas` to one of the four predefined values. Alternatively, use a backed enum or a DB `CHECK` constraint.

**Effort:** Low  
**Reference:** `app/Http/Middleware/RoleMiddleware.php:12-17`

---

## 4. Horizontal Access (Same Role, Different Wilayah)

### Finding 4.1 — ScopeMiddleware Applied Selectively
**Description:** `ScopeMiddleware` (`app/Http/Middleware/ScopeMiddleware.php`) prevents cross-wilayah access by checking `default_scope_type` and `default_scope_id`. `ScopeEnclosure` (`app/Http/Middleware/ScopeEnclosure.php`) is a lighter variant that only checks `scope_type`.

The `bootstrap/app.php` registers these aliases:
```php
'scope'     => \App\Http\Middleware\ScopeMiddleware::class,
'scopebasic' => \App\Http\Middleware\ScopeEnclosure::class,
```

These middleware are **NOT applied globally** — they must be manually added to routes. In `routes/api.php`, the `scope` and `scopebasic` middleware are referenced only via route groups/comments that suggest selective application. Looking at the route definitions, `scope` and `scopebasic` are **not explicitly applied** on any route group in `routes/api.php`. The `SyncApiController` performs inline scope validation via `AuthorizationContextService::canAccessInsiden()` and `validateRecordScope()`, but this is specific to the sync/bootstrap flows.

**Risk:** P1 — API endpoints that do not go through the sync controller (e.g., `relawan/*`, `devices/*`, `assessment`, `sitrep`, `penugasan`, `mobilisasi` endpoints) likely have **no scope middleware** applied. This means a `pcnu` user from Wilayah A could potentially access resources from Wilayah B through these endpoints. The inline scope validation in `SyncApiController` only covers sync, bootstrap, and metrics endpoints.

**Recommendation:** Apply `scope` middleware to all API route groups/prefixes in `routes/api.php`. Audit each endpoint to ensure scope validation is in place. Add the middleware explicitly:
```php
Route::prefix('v1')->middleware(['scope:pcnu,pwnu'])->group(function () { ... });
```
For endpoints that should be accessible to all scopes, document the rationale.

**Effort:** Medium  
**Reference:** `routes/api.php`, `app/Http/Middleware/ScopeMiddleware.php`, `app/Http/Middleware/ScopeEnclosure.php`

### Finding 4.2 — SyncController Inline Scope Is Correct
**Description:** The `SyncApiController` has comprehensive inline scope validation:
- `canAccessInsiden()` checks against `getAccessiblePcnuIds()` (lines 126, 138, 151)
- `validateRecordScope()` checks scope on upsert/delete (lines 164, 180)
- Query scope filtering via `getSyncScopeFilter()` (lines 268-273, 315-318, 455-461)
- `AuthorizationContextService::getAccessiblePcnuIds()` correctly isolates:
  - super_admin → all access (null filter)
  - pwnu → all PCNU under province
  - pcnu → only own PCNU
  - relawan → PCNU of assigned incidents

**Risk:** None — inline scope validation in SyncController correctly implements region isolation.

**Recommendation:** No action needed for SyncController — it is a reference implementation for scope isolation.

**Effort:** N/A  
**Reference:** `app/Http/Controllers/Api/Operasi/SyncApiController.php:120-173, 373-398`, `app/Services/Auth/AuthorizationContextService.php:131-163`

---

## 5. Vertical Access (Different Role Levels)

### Finding 5.1 — API Routes Allow All Roles Equally
**Description:** All API routes in the `auth:sanctum` group use:
```php
Route::middleware(['auth:sanctum', 'role:super_admin,pwnu,pcnu,relawan'])->group(function () { ... });
```

This means `relawan` has the same API route access as `super_admin` to every endpoint in this group, including:
- `POST /api/v1/sync` — sync operations
- `POST /api/v1/bootstrap` — full snapshot generation
- `GET /api/v1/devices` — list devices
- `DELETE /api/v1/devices/{uuid}` — revoke devices
- `POST /api/v1/devices/logout-all` — logout all devices
- `GET relawan/pendaftaran/{pendaftaran}/approve` — approve registrations (should this be `pcnu`+ only?)
- `GET relawan/pendaftaran/{pendaftaran}/reject` — reject registrations
- `GET relawan/pendaftaran/{pendaftaran}/assign` — assign volunteers

The actual authorization for some of these relies on scope isolation and inline controller logic, but the **role check is permissive**.

**Risk:** P2 — `relawan` users can access endpoints that should arguably require `pcnu` or higher (approve/reject registrations, manage devices, generate bootstrap snapshots). While scope isolation may prevent data leakage, a relawan can send requests to these endpoints and may trigger side effects.

**Recommendation:** Audit each route prefix and apply tighter role restrictions. For example:
- `relawan/*` routes → should remain accessible to relawan (they manage their own profile)
- `devices/*` → should stay as-is (users manage their own devices)
- `relawan/pendaftaran/{pendaftaran}/approve` → should be `role:super_admin,pwnu,pcnu` (approvals are management functions)

Consider splitting the main API group into sub-groups with different role middleware.

**Effort:** Medium  
**Reference:** `routes/api.php:25-106`

### Finding 5.2 — Web Routes Have Proper Role Separation
**Description:** Web routes (`routes/web.php`) correctly separate role access:
- `jabatan` index: `role:super_admin,pwnu,pcnu` (line 23)
- `jabatan` CRUD (non-index): `role:super_admin` (line 26)
- Insiden, Pleno, Eskalasi, Surat groups: `role:super_admin,pwnu,pcnu` (lines 38, 64)

**Risk:** None — web routes implement appropriate role-based access.

**Recommendation:** No action needed.

**Effort:** N/A  
**Reference:** `routes/web.php:22-29, 38-87`

---

## 6. API Abuse

### Finding 6.1 — Rate Limiting: 60 req/min for API
**Description:** Rate limiting is defined in `app/Providers/AppServiceProvider.php:25`:
```php
RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id_pengguna ?? $request->ip()));
```
Applied via `bootstrap/app.php:28`:
```php
$middleware->throttleApi('api');
```
Keyed by user ID (authenticated) or IP (unauthenticated). 60 requests/minute is appropriate for a sync-heavy mobile app where each sync sends batched changes.

**Risk:** None — configuration is reasonable.

**Recommendation:** Consider monitoring whether mobile clients hit this limit during large sync operations. If so, consider a higher limit for the sync endpoint specifically, or implement burst allowance.

**Effort:** Low  
**Reference:** `app/Providers/AppServiceProvider.php:25`

### Finding 6.2 — Login Rate Limiting: 10 req/min (Dual-Keyed)
**Description:** Login rate limiting is dual-keyed (`app/Providers/AppServiceProvider.php:121-124`):
```php
RateLimiter::for('login', fn (Request $request) => [
    Limit::perMinute(10)->by($request->ip()),
    Limit::perMinute(10)->by($request->input('no_hp', $request->ip())),
]);
```
This means 10 attempts per minute per IP **and** 10 attempts per minute per phone number. Both must be satisfied for the request to proceed. This is a strong brute-force protection.

**Risk:** None — dual-key rate limiting is excellent for login abuse prevention.

**Recommendation:** No action needed — this is a model implementation.

**Effort:** N/A  
**Reference:** `app/Providers/AppServiceProvider.php:121-124`

### Finding 6.3 — No Payload Size Limit on Sync Endpoint
**Description:** The `sync` endpoint (`POST /api/v1/sync`) accepts a JSON payload with a `changes` array. There is **no size limit** configured — no `php.ini` `post_max_size` reduction, no Nginx `client_max_body_size` override, and no middleware-level size check. The sync validation requires `changes` to be `present|array` but does not cap array size or payload bytes.

**Risk:** P2 — An attacker could send a multi-megabyte sync payload, consuming server memory and CPU for validation/processing. While PHP's `post_max_size` default (8MB) provides some protection, the lack of application-level size control means a single large request could degrade server performance for other users.

**Recommendation:** Add a `Illuminate\Http\Request` max-size validation. Either:
- Use a form request with a `max` rule on the changes array (e.g., `'changes' => 'present|array|max:500'`)
- Or add middleware that checks `Content-Length` header against a threshold (e.g., 2MB)
- Or add a `Str::length(json_encode($request->all())) < 2_000_000` check in the controller

**Effort:** Low  
**Reference:** `app/Http/Controllers/Api/Operasi/SyncApiController.php:39-43`

### Finding 6.4 — Bootstrap Endpoint Has No Special Rate Limit
**Description:** The bootstrap endpoint (`POST /api/v1/bootstrap`) generates a full database snapshot of the user's scope. This is an expensive operation that can involve querying multiple tables and serializing hundreds or thousands of records. It shares the same `throttle:api` limit (60 req/min) as lightweight `GET /sync/status` calls.

**Risk:** P2 — A malicious user or misbehaving client could call bootstrap 60 times per minute, triggering repeated database scans and file writes. This could impact server performance for all users in the same scope.

**Recommendation:** Apply a tighter rate limit specifically for the bootstrap endpoint, either via a named limiter or by applying `throttle:10,1` directly on the route. Consider caching bootstrap snapshots with a longer TTL if snapshot freshness requirements allow.

**Effort:** Low  
**Reference:** `routes/api.php:49`, `app/Http/Controllers/Api/Operasi/SyncApiController.php:440-512`

---

## 7. Missing Protections

### Finding 7.1 — SQL Injection
**Description:** The application uses Laravel Eloquent ORM throughout with parameterized queries. The `SyncApiController` uses `$modelClass::where($uuidColumn, uuid)`, `$record->update($data)`, and `$modelClass::create($data)` — all safe from SQL injection. Raw SQL is used in `orderByRaw('COALESCE(last_sync_at, dibuat_pada) desc')` (`DeviceApiController.php:21`) but with hardcoded column references — no user input is interpolated.

**Risk:** None — Eloquent ORM properly parameterizes queries. Raw SQL is limited to hardcoded expressions.

**Recommendation:** No action needed. Maintain this practice for any future raw SQL usage.

**Effort:** N/A  
**Reference:** `app/Http/Controllers/Api/Operasi/SyncApiController.php`, `app/Http/Controllers/Api/Device/DeviceApiController.php:21`

### Finding 7.2 — Cross-Site Scripting (XSS)
**Description:** The API returns JSON responses with `Content-Type: application/json`. No HTML is rendered server-side for API responses. Web views use Blade templating with automatic escaping. Security headers (`SecurityHeadersMiddleware`) include `X-Content-Type-Options: nosniff` and a Content-Security-Policy.

**Risk:** None — JSON API and CSP headers effectively mitigate XSS risk.

**Recommendation:** No action needed.

**Effort:** N/A  
**Reference:** `app/Http/Middleware/SecurityHeadersMiddleware.php`

### Finding 7.3 — Cross-Site Request Forgery (CSRF)
**Description:** API routes use Sanctum token auth (bearer tokens in `Authorization` header), not session-based cookies. Sanctum tokens are immune to CSRF because the `Authorization` header is not automatically attached by browsers. Web routes use Laravel's CSRF protection automatically.

**Risk:** None — Sanctum API auth is inherently CSRF-safe. Web routes are CSRF-protected.

**Recommendation:** No action needed.

**Effort:** N/A  
**Reference:** `routes/api.php:25` (`auth:sanctum` middleware)

### Finding 7.4 — Insecure Direct Object Reference (IDOR)
**Description:** Object lookups are scoped to authenticated user context. Examples:
- `DeviceApiController::destroy()` (`DeviceApiController.php:41`): `$user->mobileDevices()->where('uuid_device', ...)` — scoped to user's devices
- `SyncApiController::sync()` inline validation: `canAccessInsiden()` checks `AuthorizationContextService::canAccessInsiden()`

However, some route-model bindings in `routes/web.php` (e.g., `{insiden}`, `{surat}`) rely on Implicit Route Model Binding, which loads the model by primary key without ownership checks. The actual enforcement relies on the `InsidenPolicy`, `SuratPolicy`, etc.

**Risk:** P2 — API endpoints that use route-model binding (e.g., `GET penugasan/{uuid}`, `PATCH penugasan/{uuid}`, `GET assessment`, `POST assessment`) may allow access to records outside the user's scope if the controller does not explicitly validate. The `RelawanPenugasanController`, `AssessmentApiController`, etc., must validate scope on every lookup. This is partially mitigated by the fact that the `scope` middleware is intended to be applied on these routes (Finding 4.1).

**Recommendation:** Audit all API controller methods that accept a UUID route parameter or `scope_type`/`scope_id` input to ensure they validate scope ownership before returning or mutating data. Apply `scope` middleware explicitly on these routes.

**Effort:** High  
**Reference:** `routes/api.php:59-104`, `app/Http/Middleware/ScopeMiddleware.php`

### Finding 7.5 — No User-Facing File Uploads
**Description:** The application generates PDFs server-side via Dompdf and serves snapshots as pre-generated JSON files. There are no user file-upload endpoints, eliminating an entire class of attack vectors (malicious file upload, path traversal, stored XSS via uploaded files).

**Risk:** None — no file upload surface exists.

**Recommendation:** No action needed. Maintain this principle for future endpoints.

**Effort:** N/A  
**Reference:** Project-wide (no upload controllers found)

---

## 8. Recommendations Summary

| # | Finding | Risk | Effort | Reference |
|---|---------|------|--------|-----------|
| 1.1 | 30-day token expiry with no rotation | P3 | Medium | `config/sanctum.php:53` |
| 1.2 | Token abilities unused (delegated to RoleMiddleware) | P3 | Low | `app/Http/Middleware/RoleMiddleware.php` |
| 1.3 | Token prefix configured | — | — | `config/sanctum.php:68` |
| **1.4** | **`sanctum:prune-expired` not scheduled** | **P2** | **Low** | `routes/console.php` |
| **2.1** | **No Sanctum token-issuance endpoint for mobile** | **P1** | **Medium** | `routes/api.php:25` |
| **2.2** | **`device_uuid` never populated on tokens — device-level revocation broken** | **P1** | **Medium** | `app/Http/Controllers/Api/Device/DeviceApiController.php:47` |
| 2.3 | 30-day token expiry, no forced rotation on role change | P3 | Medium | `config/sanctum.php:53` |
| 2.4 | AuthUserObserver correctly revokes tokens on deactivation | — | — | `app/Observers/AuthUserObserver.php` |
| 3.1 | RoleMiddleware `>=` hierarchy check — all roles pass API route guard | P2 | Low | `app/Http/Middleware/RoleMiddleware.php:40` |
| 3.2 | `level_otoritas` integrity not enforced | P2 | Low | `app/Http/Middleware/RoleMiddleware.php:12-17` |
| **4.1** | **ScopeMiddleware not explicitly applied to most API routes** | **P1** | **Medium** | `routes/api.php`, `app/Http/Middleware/ScopeMiddleware.php` |
| 4.2 | SyncController inline scoping is correct | — | — | `app/Http/Controllers/Api/Operasi/SyncApiController.php` |
| **5.1** | **All API routes allow relawan equal access** | **P2** | **Medium** | `routes/api.php:25` |
| 5.2 | Web routes have proper role separation | — | — | `routes/web.php` |
| 6.1 | API rate limit 60 req/min — appropriate | — | — | `app/Providers/AppServiceProvider.php:25` |
| 6.2 | Login rate limit dual-keyed — excellent | — | — | `app/Providers/AppServiceProvider.php:121-124` |
| **6.3** | **No payload size limit on sync endpoint** | **P2** | **Low** | `app/Http/Controllers/Api/Operasi/SyncApiController.php:39-43` |
| **6.4** | **Bootstrap endpoint has no special rate limit** | **P2** | **Low** | `routes/api.php:49` |
| 7.1 | SQL injection — Eloquent ORM, safe | — | — | Project-wide |
| 7.2 | XSS — JSON API, CSP headers | — | — | `app/Http/Middleware/SecurityHeadersMiddleware.php` |
| 7.3 | CSRF — Sanctum bearer tokens, safe | — | — | `routes/api.php:25` |
| **7.4** | **IDOR — route-model bindings may lack scope validation** | **P2** | **High** | `routes/api.php:59-104` |
| 7.5 | No file upload surface | — | — | Project-wide |

### Priority Action Items

1. **P1 — Critical** (address immediately):
   - Implement Sanctum token-issuance endpoint for mobile clients (Finding 2.1)
   - Fix `device_uuid` population on token creation to restore device-level revocation (Finding 2.2)
   - Apply `scope` middleware to all API route groups (Finding 4.1)

2. **P2 — High** (address this sprint):
   - Add payload size limit to sync endpoint (Finding 6.3)
   - Add tighter rate limit for bootstrap endpoint (Finding 6.4)
   - Schedule `sanctum:prune-expired` (Finding 1.4)
   - Audit and tighten role middleware on API routes for vertical access (Finding 5.1)
   - Audit IDOR on route-model bindings (Finding 7.4)
   - Enforce `level_otoritas` integrity (Finding 3.2)

3. **P3 — Medium** (address next sprint):
   - Consider token rotation mechanism (Finding 1.1, 2.3)
   - Document token ability strategy (Finding 1.2)
