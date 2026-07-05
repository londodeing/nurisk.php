# Security Closure Report — Sprint 13.5

**Date:** 2026-06-20
**Phase:** Sprint 13.5 — Security Closure Sprint
**Status:** CLOSED

---

## Closure Summary

All findings from the Phase 13 Security Review have been remediated and verified.
The application is now ready for LIMITED PILOT deployment.

---

## Findings Closure

### CR-01 — Weak Authorization (Token Escalation)

**Severity:** CRITICAL
**Status:** ✅ CLOSED
**Evidence:**
- `app/Http/Middleware/RoleMiddleware.php`: Hierarchical role check using `level_otoritas` (super_admin=100, pwnu=80, pcnu=60, relawan=40). Lower-level roles cannot satisfy higher-level requirements.
- `app/Http/Middleware/ScopeMiddleware.php`: Wilayah-isolation with automatic 403 + audit log for cross-wilayah access attempts.
- `app/Observers/AuthUserObserver.php`: Revokes all tokens on `status_akun` change, user deletion, or force-deletion.
- `tests/Feature/SecurityRegressionTest.php::test_pcnu_cannot_write_jabatan` ✅
- `tests/Feature/SecurityRegressionTest.php::test_pwnu_cannot_access_super_admin_endpoints` ✅
- `tests/Feature/SecurityRegressionTest.php::test_relawan_cannot_access_admin_web_routes` ✅
- `tests/Feature/SecurityRegressionTest.php::test_pcnu_cannot_access_other_wilayah` ✅

### CR-02 — No Token Expiry

**Severity:** CRITICAL
**Status:** ✅ CLOSED
**Evidence:**
- `config/sanctum.php`: `expiration` changed to `env('SANCTUM_TOKEN_EXPIRATION_DAYS', 30) * 24 * 60`. Tokens now expire after 30 days by default.
- `config/sanctum.php`: `token_prefix` set to `'nurisk_'` for easy identification of leaked tokens.
- `.env` / `.env.example`: `SANCTUM_TOKEN_EXPIRATION_DAYS=30` added.
- `tests/Feature/SecurityRegressionTest.php::test_expired_token_returns_401` ✅
- `tests/Feature/SecurityRegressionTest.php::test_revoked_token_returns_401` ✅

### HR-02 — Unprotected Sensitive Mass Assignment

**Severity:** HIGH
**Status:** ✅ CLOSED
**Evidence:**
- `app/Models/AuthUser.php`: `$fillable` hardened to whitelist-only (`no_hp`, `is_tersedia`, `terakhir_masuk`). Fields `id_peran`, `kata_sandi`, `status_akun`, `default_scope_type`, `default_scope_id` removed from fillable.
- `tests/Feature/SecurityRegressionTest.php::test_cannot_set_role_via_payload` ✅
- `tests/Feature/SecurityRegressionTest.php::test_cannot_set_status_akun_via_payload` ✅
- `tests/Feature/SecurityRegressionTest.php::test_cannot_set_default_scope_type_via_payload` ✅

### HR-05 — Missing Brute-Force Protection

**Severity:** HIGH
**Status:** ✅ CLOSED
**Evidence:**
- `app/Providers/AppServiceProvider.php`: Login rate limiter registered — 10 requests/minute per IP + 10 requests/minute per `no_hp` field.
- `routes/auth.php`: Login route now uses `throttle:login` (named limiter).
- `tests/Feature/SecurityRegressionTest.php::test_login_throttling_active` ✅

---

## Security Regression Test Results

**Command:** `php artisan test tests/Feature/SecurityRegressionTest.php`
**Result:** 16 passed, 0 failed, 0 skipped
**Coverage:** Role-based access (6), Token lifecycle (3), Mass assignment (3), Wilayah isolation (1), Login throttling (1), Guest access (2)

---

## Recommendation

The four security findings (CR-01, CR-02, HR-02, HR-05) are fully remediated.
The application meets the security criteria for LIMITED PILOT deployment.

---

## Appendices

- `docs/GO_NO_GO_PILOT_V2.md` — Updated GO/NO-GO decision
- `tests/Feature/SecurityRegressionTest.php` — Security regression test suite
- Phase 13 reports directory for load test, query profile, etc.

*End of Security Closure Report*
