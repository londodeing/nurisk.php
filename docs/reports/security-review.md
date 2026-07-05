# Security Review (Task 13.7)

## Sanctum Token Lifecycle

| Setting | Value | Risk |
|---|---|---|
| `expiration` | `null` (config/sanctum.php:53) | **Tokens never expire.** No forced re-authentication. |
| `token_prefix` | `env('SANCTUM_TOKEN_PREFIX', '')` | Empty by default — no secret scanning protection. |
| `guard` | `['web']` | Web guard used for Sanctum authentication. |
| `stateful` | APP_URL + localhost:3000, etc. | First-party SPA domains. |

**Findings:**
- **No token expiration**: `expiration => null` means issued tokens are valid indefinitely unless manually revoked. If a token leaks, it can be used forever. Set `expiration` to a reasonable TTL (e.g., 7 days for mobile, 15 minutes for web SPAs).
- **No token refresh mechanism**: There is no dedicated refresh token endpoint. The `DeviceAuthController::refreshToken` endpoint (`routes/api.php:20`) exists but its implementation was not audited — it may or may not implement token rotation.
- **Revocation**: Sanctum provides `$user->tokens()->delete()` but no automated revocation hook (e.g., on password change or account deactivation) is visible in `AuthUser.php`.
- **`kata_sandi` in `$fillable`**: The password field is mass-assignable (`AuthUser.php:25`). If any controller uses `AuthUser::create($request->all())` or similar, an attacker could set their own password.

### Recommendations
- Set `'expiration' => 60 * 24 * 7` (7 days) for API tokens in `config/sanctum.php`.
- Implement token rotation on every refresh.
- Add a listener on `password_changed` / `status_akun` update to revoke all existing tokens.
- Remove `kata_sandi` from `$fillable` and use a dedicated method for password updates.

## Permission Escalation Risks

### Role Hierarchy
- `AuthRole` model has `level_otoritas` field, but **`RoleMiddleware` never uses it** — it only checks `nama_peran` via string comparison (`RoleMiddleware.php:30`).
- Authority levels exist in the schema but are not enforced at the middleware layer.

### Authorization Gaps
| Area | Auth Mechanism | Gaps |
|---|---|---|
| Web routes (admin) | `role:super_admin,pwnu,pcnu` and `role:super_admin` | String-based role check, no hierarchy enforcement |
| Web routes (governance) | `role:super_admin,pwnu,pcnu` | Broad role set for sensitive operations |
| API routes | `auth:sanctum` only | **No `role` or `scope` middleware on any API route**. Any authenticated user can access all API endpoints regardless of role. |
| Policies (Gates) | Registered for ~17 models | Well-structured, but only apply when explicitly called via `Gate::allows()` or `$this->authorize()` |

**Specific risks:**
- **API authorization gap**: `routes/api.php:25` wraps all authenticated routes in `Route::middleware('auth:sanctum')` but never adds `role` or `scope` middleware. Any mobile device user with a valid token can call `POST /api/operasi/posaju`, `POST /api/v1/sync`, etc.
- **Scope bypass**: `ScopeEnclosure` middleware exists but is **only used in web routes** — not applied to any API route.
- **Missing policies**: `DokumenSuratUtama`, `OperasiPleno`, `OperasiEskalasi`, `PenggunaJabatan` have policies based on gate registrations, but other models without registered policies fall back to the default `Gate::denies()`.

### Recommendations
- **Add role middleware to API routes** — wrap the authenticated API group with `scope` and `role` middleware.
- **Enforce `level_otoritas` hierarchy** in `RoleMiddleware` instead of flat string comparison, so higher-level roles inherit permissions.
- **Audit all models** — ensure every model used in API endpoints has a registered policy.
- **Add authorization assertions** to API controllers as a safety net.

## File Upload Validation

**No file upload validation exists in the codebase.** The search for `file`, `upload`, `mimes`, `dimensions`, `image`, `pdf` in request classes and controllers returned no file validation rules.

The only file-related functionality is:
- `SuratController::downloadPdf` — serves existing PDF files but does not accept uploads.
- `SuratPdfService` — generates PDFs server-side via Dompdf, no user file input.
- `HealthCheckController::checkStorage` — writes/reads a temp health-check file (not user-controlled).

**Risk**: If file upload is planned or added in the future, there is no precedent or pattern for secure file validation. Any future upload endpoint would need:
- `mimes:pdf,jpg,png,...` or `mimetypes:` validation.
- File size limits via `max:`.
- Virus scanning (ClamAV) for uploads in production.
- Storage outside the web root or with a secure URL strategy.

**Recommendation**: If the app does not accept file uploads by design, document this explicitly. If uploads are planned, add validation rules as a prerequisite before shipping the feature.

## Mass Assignment

**All models use `$fillable`** (whitelist approach). No model uses `$guarded = []`. This is the correct approach.

### Notable `$fillable` items
| Model | Fields of Concern | Reasoning |
|---|---|---|
| `AuthUser` | `kata_sandi`, `id_peran`, `status_akun`, `default_scope_type`, `default_scope_id` | Password, role, and status are all mass-assignable. If a controller uses `User::create($request->all())`, user could escalate privileges. |
| `AuthPenggunaProfil` | — | Checked: appears safe |
| `RelawanPendaftaran` | — | Checked: appears safe |

**Recommendation**: Remove `kata_sandi`, `id_peran`, `status_akun`, `default_scope_type`, and `default_scope_id` from `AuthUser::$fillable`. These should only be set through dedicated service methods with authorization checks.

## Rate Limiting

| Endpoint | Limit | Applied Via |
|---|---|---|
| All API routes | 60 req/min | `RateLimiter::for('api')` → `$middleware->throttleApi('api')` |
| Login (`POST /login`) | 10 req/min | `middleware('throttle:10,1')` on route |
| Password reset | 60 sec between attempts | `config/auth.php:104` |

**Findings:**
- The global API rate limit of 60 req/min is reasonable for most use cases but may be low for mobile sync operations (sync endpoint sends batched data).
- Login throttling at 10 req/min is good practice.
- No distinction between authenticated vs. unauthenticated API rate limits (the `Limit::by()` callback uses `user()->id_pengguna ?? $request->ip()`).
- No per-endpoint rate limiting (e.g., stricter limits on `POST /sync`, looser on `GET /wilayah/desa`).

### Recommendations
- Consider separate rate limits for sync endpoints (e.g., `Limit::perMinute(30)` for `sync`, `Limit::perMinute(120)` for read-only endpoints).
- Add rate limit headers (`X-RateLimit-Limit`, `X-RateLimit-Remaining`) to API responses via a response middleware.
- Monitor rate limit hits as an observability metric to tune limits.

## CORS Configuration

| Setting | Value | Assessment |
|---|---|---|
| `paths` | `api/*`, `sanctum/csrf-cookie` | Appropriate |
| `allowed_methods` | GET, POST, PUT, PATCH, DELETE, OPTIONS | Acceptable |
| `allowed_origins` | `env('APP_URL')` | **Good** — not using wildcard `*` |
| `allowed_headers` | Content-Type, Authorization, X-Requested-With, X-Correlation-ID, X-Request-ID, Accept | Appropriate |
| `exposed_headers` | X-Correlation-ID, X-Request-ID | Matches correlation middleware |
| `supports_credentials` | `false` | Acceptable for token-based API |
| `max_age` | `0` | Preflight sent on every request — could be increased to 86400 for performance |

**Recommendations:**
- Set `max_age` to `86400` (24 hours) to reduce preflight overhead.
- Ensure `APP_URL` in production is pinned to a specific origin (the code comment already warns about this).
- Validate that CORS is not overly permissive for the `sanctum/csrf-cookie` path (cookie-based auth should have strict origin validation).

## Summary of Findings by Severity

| Severity | Issue | Location |
|---|---|---|
| **Critical** | API routes have no role/scope middleware | `routes/api.php:25` |
| **High** | Sanctum tokens never expire | `config/sanctum.php:53` |
| **High** | `kata_sandi`, `id_peran`, `status_akun` in `$fillable` | `app/Models/AuthUser.php:24` |
| **Medium** | Role middleware uses flat string comparison, ignoring `level_otoritas` | `app/Http/Middleware/RoleMiddleware.php:30` |
| **Medium** | No token revocation on deactivation/password change | `app/Models/AuthUser.php` |
| **Medium** | `X-Request-ID` response header never set (dead code) | `app/Http/Middleware/CorrelationIdMiddleware.php:24` |
| **Low** | `max_age` is 0 in CORS config | `config/cors.php:39` |
| **Low** | `token_prefix` empty by default | `config/sanctum.php:68` |
