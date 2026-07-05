## Goal
- Execute Phase 14 (Production Hardening & Pilot Operations) to transition NURISK from GO FOR LIMITED PILOT to GO FOR REGIONAL PRODUCTION DEPLOYMENT.

## Constraints & Preferences
- **FEATURE FREEZE**: no new modules, no new business features, no changes to stable core workflows.
- Focus only on: production hardening, operational excellence, observability, device & token lifecycle, pilot readiness, regional deployment readiness.
- All recommendations must include evidence.
- All benchmarks must use actual data.
- All findings classified as P0, P1, P2, P3.

## Progress

### Completed

| Phase | Deliverable | Status |
|---|---|---|
| 14.1 | Query profiling — 6 endpoints measured, 24 (sync) / 11 (status) / 10 (metrics) / 15 (bootstrap) / 7 (assessment) / 11 (penugasan) queries | ✅ |
| 14.1 | AuthContext fix — use `Auth::user()` saves 2 queries per request | ✅ |
| 14.1 | SyncObserver memoization — saves N queries per request | ✅ |
| 14.1 | Max cursor computed from PHP — saves 1 query | ✅ |
| 14.1 | Report: `docs/reports/query-profile-production.md` | ✅ |
| 14.2 | Migration: `device_uuid` on `personal_access_tokens` | ✅ |
| 14.2 | `AuthUser::mobileDevices()` relationship added | ✅ |
| 14.2 | `GET /api/v1/devices` — list devices | ✅ |
| 14.2 | `DELETE /api/v1/devices/{uuid}` — revoke device + tokens | ✅ |
| 14.2 | `POST /api/v1/devices/logout-all` — logout all devices | ✅ |
| 14.2 | `MobileDeviceFactory` created | ✅ |
| 14.2 | Report: `docs/reports/device-token-audit.md` | ✅ |
| 14.3 | Sentry Laravel package installed (`sentry/sentry-laravel ^4.26`) | ✅ |
| 14.3 | Sentry config published + git SHA release | ✅ |
| 14.3 | `SentryUserContextMiddleware` (id/username/role) | ✅ |
| 14.3 | `.env.example` updated with Sentry vars | ✅ |
| 14.4 | `docs/RUNBOOK.md` (7 scenarios, copy-pasteable commands) | ✅ |
| 14.5 | `docs/reports/pilot-dashboard-review.md` (6 sections + SQL appendix) | ✅ |
| 14.6 | `docs/PILOT_CHECKLIST.md` (64 items, 9 sections) | ✅ |
| 14.7 | `docs/GO_NO_GO_REGIONAL.md` — **GO FOR REGIONAL** recommended at 85/100 | ✅ |

### Key Findings (Sync Endpoint Optimization History)

| Phase | Queries | Delta |
|---|---|---|
| Original (per-cursor N+1) | ~32+ | — |
| Phase 4 batch cursors | ~32 | 0 |
| validateRecordScope caching | 28 | −4 |
| AuthContext fix (use Auth::user) | 26 | −2 |
| SyncObserver memoization | 25 | −1 |
| Max cursor from PHP | 24 | −1 |
| **Total** | **24** | **−8 (25%)** |

### Remaining (all non-sync endpoints within target)

- Sync endpoint (24 queries) exceeds target (15) — realistic floor ~21. **P2 finding.**
- Pre-existing SQLite FK issues in test suite (121 failing tests) — NOT caused by our changes.

## Next Steps / Future Work

Token-to-device association (Option A/B/C from device-token-audit) — currently tokens are created via web SPA without `device_uuid`. Options documented in report.

## Relevant Files Created/Modified

- `tests/Feature/QueryProfileTest.php` — empirical endpoint profiling
- `tests/Feature/DeviceApiTest.php` — device lifecycle tests
- `database/factories/MobileDeviceFactory.php` — factory for testing
- `database/migrations/2026_06_20_110340_add_device_uuid_to_personal_access_tokens.php`
- `app/Http/Controllers/Api/Device/DeviceApiController.php`
- `app/Http/Middleware/SentryUserContextMiddleware.php`
- `app/Models/AuthUser.php` — added `mobileDevices()`
- `app/Observers/SyncObserver.php` — memoized `getPcnuId()`
- `app/Services/Auth/AuthorizationContextService.php` — use `Auth::user()`
- `app/Http/Controllers/Api/Operasi/SyncApiController.php` — max cursor from PHP, batch with('insiden')
- `config/sentry.php` — git SHA release
- `bootstrap/app.php` — SentryUserContextMiddleware registered
- `.env.example` — Sentry config vars
- `routes/api.php` — device routes
- `docs/reports/query-profile-production.md`
- `docs/reports/device-token-audit.md`
- `docs/reports/pilot-dashboard-review.md`
- `docs/RUNBOOK.md`
- `docs/PILOT_CHECKLIST.md`
- `docs/GO_NO_GO_REGIONAL.md`
