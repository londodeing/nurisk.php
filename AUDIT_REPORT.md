# NURISK Comprehensive Codebase Audit Report

**Date:** 2026-06-18
**Scope:** Full codebase audit vs DATABASE_CONVENTION.md, DOMAIN_RULES.md, AUTHORIZATION_MATRIX.md, TESTING_RULES.md, AI_DEVELOPMENT_RULES.md
**Files audited:** 200+ (models, controllers, requests, policies, services, migrations, tests, routes, views)
**Syntax errors:** 0/200+ files (all pass `php -l`)

---

## Summary

| Severity | Count | Key Issues |
|----------|-------|------------|
| **CRITICAL** | 4 | No auth on API routes, hardcoded role IDs, mass assignment via `$request->all()`, hardcoded fallback user IDs |
| **HIGH** | 8 | Missing `$this->authorize()` in 9 controllers, wrong policy signature |
| **MEDIUM** | 6 | Contradictory timestamps, duplicate routes, form requests returning `true` for authorize |
| **LOW** | 6 | Debug logging, `dump()` calls, unused `User.php` model |

---

## CRITICAL

### C1 — Seluruh API Routes Tanpa Authentication Middleware

**File:** `routes/api.php` (lines 1-102), `bootstrap/app.php` (lines 7-24)
**Finding:** File `routes/api.php` didaftarkan di `bootstrap/app.php` tanpa middleware group `api` apapun. Tidak ada `->withMiddleware()` untuk API group. Comment di baris 18 menyatakan: *"Sementara ini diabaikan middleware authnya untuk fokus testing controller"*.
**Impact:** 56+ endpoint API (sync, assessment, sitrep, penugasan, klaster, mobilisasi, device auth, relawan) dapat diakses publik tanpa autentikasi. Siapapun bisa read/write semua data.
**Fix:** Tambahkan middleware `auth:sanctum` ke semua API route groups di `bootstrap/app.php`.

### C2 — Hardcoded Numeric Role IDs

**Files:**
- `app/Http/Controllers/Api/Operasi/MobilisasiApiController.php:62` — `in_array($user->id_peran, [3, 4])`
- `app/Services/Relawan/RelawanService.php:203` — `AuthUser::where('id_peran', 4)`

**Finding:** Role primary keys (`3`, `4`) di-hardcode. Jika seeder `auth_roles` berubah atau dijalankan di environment berbeda, ID tidak akan cocok.
**Impact:** Authorization logic silently fails — grants/denies access ke user yang salah.
**Fix:** Gunakan `AuthRole::where('nama_peran', 'relawan')->value('id_peran')` atau `AuthorizationContextService`.

### C3 — Mass Assignment via `$request->all()`

**Files:**
- `app/Http/Controllers/Api/Operasi/OperasiKlasterController.php:61` — `$klaster->update($request->all())`
- `app/Http/Controllers/Api/Operasi/OperasiTugasController.php:54` — `$tugas->update($request->all())`

**Finding:** `$request->all()` mengirim semua field termasuk yang tidak di-validate. Field unexpected bisa masuk ke model.
**Impact:** Potensi mass-assignment vulnerability.
**Fix:** Ganti ke `$request->validated()`.

### C4 — Hardcoded Fallback User ID `?? 1`

**Files:**
- `app/Http/Controllers/Api/Operasi/SyncApiController.php:45` — `Auth::id() ?? 1`
- `app/Http/Controllers/Api/Operasi/SyncApiController.php:167` — `Auth::id() ?? 1`
- `app/Http/Controllers/Api/Operasi/MobilisasiApiController.php:70` — `auth()->id() ?? 1`
- `app/Http/Controllers/Api/Operasi/PenugasanApiController.php:153` — `Auth::id() ?: 1`
- `app/Http/Controllers/Api/Auth/DeviceAuthController.php:30` — `auth()->id() ?? 1`

**Finding:** Jika `Auth::id()` return `null` (karena tidak ada auth), fallback ke user ID 1 (super_admin).
**Impact:** Audit trail corruption, privilege escalation.
**Fix:** Remove fallback. Pastikan request terautentikasi sebelum mencapai controller (lihat C1).

---

## HIGH

### H1 — KlasterApiController::store Authorization Signature Salah

**File:** `app/Http/Controllers/Api/Operasi/KlasterApiController.php:72`
**Code:** `$this->authorize('create', OperasiKlaster::class)`
**Seharusnya:** `$this->authorize('create', [OperasiKlaster::class, $insiden])`
**Impact:** Policy `create()` tidak menerima context insiden, grant access ke user yang tidak berhak.

### H2 — PlanoPesertaController::vote Missing Authorization

**File:** `app/Http/Controllers/Governance/PlanoPesertaController.php:41`
**Finding:** Method `vote()` tidak memiliki `$this->authorize()` call. Siapapun yang punya session bisa vote.
**Fix:** Tambahkan `$this->authorize('tambahPeserta', $pleno)` atau policy check baru.

### H3 — InsidenStatusController::update Authorization Hanya di Form Request

**File:** `app/Http/Controllers/Operasi/InsidenStatusController.php:17-30`
**Finding:** Controller tidak panggil `$this->authorize()`, authorization hanya di form request. Fragile.
**Fix:** Tambahkan `$this->authorize()` di controller untuk defense-in-depth.

### H4 — RelawanPendaftaranController Tidak Ada Authorization

**File:** `app/Http/Controllers/Api/Relawan/RelawanPendaftaranController.php` (4 methods)
**Finding:** Tidak ada method yang panggil `$this->authorize()` atau `Gate::authorize()`. Authorization hanya di form request.
**Fix:** Tambahkan `$this->authorize()` di setiap method.

### H5 — SyncApiController Tidak Ada Authorization

**File:** `app/Http/Controllers/Api/Operasi/SyncApiController.php` (sync, status, metrics methods)
**Finding:** Tidak ada authorization check. Siapapun dapat sync data, view server cursors, view metrics.
**Impact:** Dikombinasikan dengan C1, data bisa bebas diakses publik.
**Fix:** Tambahkan authorization + auth middleware.

### H6 — DeviceAuthController Tidak Ada Authorization

**File:** `app/Http/Controllers/Api/Auth/DeviceAuthController.php`
**Finding:** `refreshToken()` tidak ada authorization.
**Fix:** Tambahkan authentication requirement dan authorization check.

### H7 — BulkStubController Tidak Ada Authorization

**File:** `app/Http/Controllers/Api/Operasi/BulkStubController.php`
**Finding:** `logistikBulk()` dan `mobilisasiBulk()` tidak ada authorization.
**Fix:** Tambahkan `$this->authorize()`.

### H8 — ProfileController::destroy Missing Authorization

**File:** `app/Http/Controllers/ProfileController.php:43-59`
**Finding:** `destroy()` hanya validasi password, tidak ada explicit authorization.
**Fix:** Tambahkan `$this->authorize('delete', $user)`.

---

## MEDIUM

### M1 — Contradictory Timestamp Configuration

**Files:**
- `app/Models/AssessmentDampakManusia.php:13,16-17`
- `app/Models/AssessmentKebutuhanMendesak.php:13,16-17`

**Finding:** `$timestamps = false` tetapi juga define `const CREATED_AT` dan `const UPDATED_AT`. Constants adalah dead code.
**Fix:** Remove unused constants atau set `$timestamps = true`.

### M2 — Non-Standard `UPDATED_AT = null` Pattern

**Files (7 models):** `OperasiTugas`, `RiwayatStatusInsiden`, `OperasiPosaju`, `OperasiSuratKeluar`, `OperasiPleno`, `SyncAuditLog`, `SyncConflict`
**Finding:** `const UPDATED_AT = null` untuk disable updated_at column sambil keep `$timestamps = true`. Non-standard.
**Impact:** Code maintainability. Future developers may expect working `updated_at`.
**Fix:** Consider konsistensi — gunakan `$timestamps = false` + explicit `CREATED_AT` jika hanya butuh `dibuat_pada`.

### M3 — Duplicate Route Definitions

**File:** `routes/api.php:57-76,94`
**Finding:** `penugasan` routes didefinisikan di 2 prefix group berbeda (`v1` dan `relawan`). Group `relawan` di line 94 menggunakan implicit model binding `{penugasan}`.
**Impact:** Route conflicts, URL generation may produce unexpected results.
**Fix:** Consolidate penugasan routes under a single prefix group.

### M4 — Variable Naming Inconsistency

**File:** `app/Http/Controllers/Operasi/InsidenController.php:34`
**Code:** `$insideni = $query->paginate(15)->withQueryString();`
**Finding:** Variable `$insideni` (typo).
**Fix:** Rename to `$insiden` atau `$insidens`.

### M5 — Form Requests Return `true` untuk `authorize()`

**Files:** 22 form requests di `app/Http/Requests/Operasi/`, `app/Http/Requests/Governance/`, `app/Http/Requests/Relawan/`
**Finding:** Delegasi authorization sepenuhnya ke controller. Jika controller lupa panggil `$this->authorize()`, tidak ada backup.
**Fix:** Tambahkan Gate/Policy check di form request `authorize()` method, atau minimal comment.

### M6 — Hardcoded Role Names

**File:** `app/Http/Controllers/Api/Relawan/RelawanProfilController.php:62`
**Code:** `if (!$authContext->hasAnyRole(['super_admin', 'pwnu', 'pcnu']))`
**Finding:** Role names di-hardcode sebagai string. Jika role names berubah di database, check silently fails.
**Fix:** Gunakan `Gate::authorize()` dengan policy, atau gunakan config values.

---

## LOW

### L1 — Debug Logging in Production Code

**File:** `app/Http/Controllers/Api/Operasi/KlasterApiController.php:70-71`
**Code:** `\Log::info("In store. User ID: " . \Auth::id() . ...)`
**Finding:** Log statements expose internal authorization decisions.
**Fix:** Remove or guard with debug flag.

### L2 — `dump()` Call in SyncApiController

**File:** `app/Http/Controllers/Api/Operasi/SyncApiController.php:120`
**Code:** `dump("ERROR: id_insiden not set in SyncApiController!", ...)`
**Finding:** `dump()` outputs to response stream — breaks JSON responses.
**Fix:** Replace with `\Log::error()` or throw exception.

### L3 — Auth Middleware Intentionally Skipped

**File:** `routes/api.php:18`
**Finding:** Comment states auth middleware was intentionally skipped for development.
**Fix:** Add auth middleware before production deployment.

### L4 — Auto-Increment Column Restart Assumption

**Files:** Multiple seeders
**Finding:** Seeders may assume `id_peran` starts at 1, matching hardcoded IDs.
**Fix:** Use `firstOrCreate()` with matching on `nama_peran`.

### L5 — Default Laravel `User.php` Model Still Present

**File:** `app/Models/User.php`
**Finding:** Default `User` model references `users` table (not `auth_users`). Unused in production code.
**Fix:** Remove if unused, or update to extend `AuthUser`.

### L6 — Console Routes Hanya Berisi Default Command

**File:** `routes/console.php`
**Finding:** Only default `inspire` command.
**Fix:** Add custom Artisan commands as needed.

---

## Compliance by Document

### DATABASE_CONVENTION.md Compliance
| Check | Status | Notes |
|-------|--------|-------|
| Primary key naming (`id_{entity}`) | ✅ | Semua model comply |
| Indonesian timestamps | ⚠️ | 2 models have contradictory config (M1) |
| Soft delete `DELETED_AT='dihapus_pada'` | ✅ | 14 soft-delete models all comply |
| Migration format (no `$table->timestamps()`) | ✅ | All migrations use explicit column names |
| MySQL-only syntax | ✅ | All use `enum()`, `unsignedBigInteger()`, proper FK |
| Prefix domain tables | ✅ | All new tables use correct prefixes |

### DOMAIN_RULES.md Compliance
| Check | Status | Notes |
|-------|--------|-------|
| M07 Pleno state machine | ✅ | draft → ditinjau → disetujui → ditandatangani → final |
| Eskalasi hierarchical check | ✅ | Numeric comparison, not string |
| Insiden state transitions | ✅ | Via RiwayatStatusInsiden |

### AUTHORIZATION_MATRIX.md Compliance
| Check | Status | Notes |
|-------|--------|-------|
| All controllers use `$this->authorize()` | ❌ | 9 controllers missing authorization |
| API routes have auth middleware | ❌ | None — critical C1 |
| Web routes have proper middleware | ✅ | auth + role middleware present |
| Policies registered in AppServiceProvider | ✅ | PlanoPolicy, EskalasiPolicy registered |
| Scope enforcement | ⚠️ | Some controllers skip scope check |

### TESTING_RULES.md Compliance
| Check | Status | Notes |
|-------|--------|-------|
| Tests use DatabaseTransactions | ✅ | 49/50 feature tests use it |
| MySQL for testing | ✅ | `.env.testing` uses MySQL |
| Base test case pattern | ✅ | Tests extend TestCase |
| No auth tests skipped | ⚠️ | Some controllers without auth have no test coverage |
| State machine transition tests | ✅ | PlanoTest covers transitions |

### AI_DEVELOPMENT_RULES.md Compliance
| Check | Status | Notes |
|-------|--------|-------|
| Indonesian naming convention | ✅ | All tables/columns in Bahasa Indonesia |
| No Laravel morphTo (manual poly) | ✅ | Polymorphic reference manual |
| Proper model config (PK, timestamps) | ✅ | All models have proper config |
| Controller authorization | ❌ | See H1-H7 |
| Service layer pattern | ✅ | PlanoService, RelawanService, etc. |
| DB::transaction() for multi-table ops | ✅ | Pleno, eskalasi, mobilisasi use transactions |

---

## M07 Pleno & Eskalasi Implementation Audit

| Component | Status | Notes |
|-----------|--------|-------|
| Migration (5 tables) | ✅ | Sesuai SQL v37, proper FK, enum values, indexes |
| Models (5) | ✅ | SoftDeletes, scope, helper methods, proper PK/timestamp config |
| Policies (2) | ✅ | PlanoPolicy (7 methods), EskalasiPolicy (1 method) |
| Form Requests (4) | ✅ | StorePlanoRequest, StoreKeputusanRequest, UpdatePesertaVoteRequest, StoreEskalasiRequest |
| Service (PlanoService) | ✅ | CRUD, auto-generate nomor, finalisasi, eskalasi, jurnal logging |
| Controllers (4) | ⚠️ | PlanoPesertaController::vote missing authorization (H2) |
| Routes (10) | ✅ | auth + role:super_admin,pwnu,pcnu middleware |
| Views (3) | ✅ | index, show (4 tab), create |
| Factory (1) | ✅ | 2 states: sudahDitinjau, sudahFinal |
| Tests (2 files, 22 tests) | ✅ | PlanoTest (18), EskalasiTest (4) — syntax verified |

---

## Top Recommended Fixes (Ordered by Priority)

1. **C1** — Add `auth:sanctum` middleware to all API routes in `bootstrap/app.php`
2. **C2** — Replace hardcoded role IDs with dynamic lookup via `AuthRole`
3. **C4** — Remove `?? 1` fallback user IDs throughout codebase
4. **C3** — Replace `$request->all()` with `$request->validated()` in both controllers
5. **H1** — Fix `KlasterApiController::store` authorize signature to include `$insiden`
6. **H2** — Add `$this->authorize()` to `PlanoPesertaController::vote`
7. **H5** — Add authorization to `SyncApiController`
8. **H4** — Add `$this->authorize()` to `RelawanPendaftaranController`
9. **H7** — Add authorization to `BulkStubController`
10. **M1** — Fix contradictory timestamp config in 2 assessment models
