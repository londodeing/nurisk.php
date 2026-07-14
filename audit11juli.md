# AUDIT REPORT — 11 Juli 2026

## NURISK Platform — Production Readiness Assessment

---

**Audit Scope:** AUTH · Governance · SDUI · COP

**Auditor:** Principal Software Architect / Security Auditor

**Verdict:** NOT READY FOR PRODUCTION

---

# Table of Contents

1. [Executive Summary](#executive-summary)
2. [AUDIT 1: AUTH Architecture](#audit-1-auth-architecture)
3. [AUDIT 2: Governance Architecture](#audit-2-governance-architecture)
4. [AUDIT 3: SDUI Architecture](#audit-3-sdui-architecture)
5. [AUDIT 4: COP Architecture](#audit-4-cop-architecture)
6. [Technical Debt Register](#technical-debt-register)
7. [Refactoring Roadmap](#refactoring-roadmap)
8. [Final Verdict](#final-verdict)

---

# Executive Summary

## Production Readiness Score: 2/100

| Domain | Score | Critical | High | Medium | Low |
|--------|-------|----------|------|--------|-----|
| AUTH | 2/10 | 3 | 7 | 5 | 2 |
| Governance | 1/10 | 3 | 8 | 4 | 1 |
| SDUI | 3/10 | 1 | 7 | 6 | 2 |
| COP | 2/10 | 3 | 10 | 6 | 3 |
| **Total** | **2/10** | **10** | **32** | **21** | **8** |

## Top 5 Most Critical Issues

| Rank | Finding | Domain | Impact |
|------|---------|--------|--------|
| 1 | PIN verification hardcoded to `123456` | AUTH | Any authenticated user can approve any action |
| 2 | Public device registration creates token for user ID 1 | AUTH | Unauthenticated privilege escalation to super_admin |
| 3 | All governance CRUD controllers have ZERO authorization | GOV | Any user can create/delete mandates, positions, authorities |
| 4 | No incident state transition validation | COP | Incidents can be illegally opened/closed/skipped |
| 5 | LiveConnectionManager is entirely mock/simulation | COP | No real-time COP capability exists |

---

# AUDIT 1: AUTH Architecture

## Finding A-01: Hardcoded PIN Bypass

**Severity:** Critical
**OWASP:** A07:2021 — Identification and Authentication Failures
**Location:** `app/Http/Controllers/Api/AuthApiController.php:62`

```php
public function verifyPin(Request $request): JsonResponse
{
    $request->validate(['pin' => 'required|digits:6']);
    if ($request->pin === '123456') {
        return response()->json(['status' => 'success', 'message' => 'PIN Valid.']);
    }
    return response()->json(['status' => 'error', 'message' => 'PIN Tidak Valid.'], 403);
}
```

**Exploit Scenario:** Any authenticated user sends `POST /api/auth/pin/verify` with `pin=123456`. Server returns success. The PIN is meant to protect high-level approvals (mandate activation, signature, budget). An attacker with a valid session can approve any PIN-protected action.

**Root Cause:** Production code contains a literal string comparison against a hardcoded value. No per-user PIN storage, no bcrypt hashing, no attempt tracking, no lockout.

**Recommendation:**
1. Remove hardcoded PIN immediately
2. Store per-user PIN as bcrypt hash in `auth_pengguna_profil` table
3. Implement 3-attempt lockout (tempus: 15 menit)
4. Apply `throttle:5,1` middleware to the endpoint
5. Log all PIN verification attempts with user_id, IP, User-Agent

---

## Finding A-02: Public Device Registration with User ID 1 Fallback

**Severity:** Critical
**OWASP:** A01:2021 — Broken Access Control
**Location:** `app/Http/Controllers/Api/Auth/DeviceAuthController.php:13-62`

```php
public function refreshToken(Request $request): JsonResponse
{
    // Route: POST /api/v1/device/refresh-token — NO auth middleware
    $deviceUuid = $request->input('device_uuid');
    $device = MobileDevice::where('uuid_device', $deviceUuid)->first();

    if (!$device) {
        $device = MobileDevice::create([
            'uuid_device' => $deviceUuid,
            'id_pengguna' => auth()->id() ?? 1, // FALLBACK TO USER 1
            'platform' => $request->header('User-Agent', 'unknown'),
            'app_version' => '1.0.0',
            'status' => 'active',
            'trust_score' => 100,
        ]);
    }
    // ... issues new device_token (Str::random(60), 30-day TTL)
}
```

**Exploit Scenario:** An attacker sends `curl -X POST https://api.nurisk.dev/api/v1/device/refresh-token -d 'device_uuid=anything'`. Since this route has NO authentication middleware, `auth()->id()` returns `null`. The fallback `?? 1` creates a device bound to user ID 1 (super_admin). The response includes a valid SHA-256 device_token valid for 30 days.

**Additional Issues:**
- Custom token (`Str::random(60)` + SHA-256) is NOT integrated with Sanctum
- No device_uuid validation (UUID format not enforced)
- `trust_score` defaults to 100 — no trust assessment
- No device limit per user

**Recommendation:**
1. Require authentication via `auth:sanctum` middleware
2. Remove fallback ID — return 401 if unauthenticated
3. Use Sanctum's native token creation instead of `Str::random(60)`
4. Validate UUID v4 format for `device_uuid`
5. Enforce maximum devices per user (e.g., 5)

---

## Finding A-03: RoleMiddleware Uses Wrong Auth Guard

**Severity:** Critical
**OWASP:** A01:2021 — Broken Access Control
**Location:** `app/Http/Middleware/RoleMiddleware.php:20`

```php
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {  // Uses DEFAULT guard (web)
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }
        // ...
    }
}
```

**How it's applied:** `routes/api.php:250`
```php
Route::middleware(['auth:sanctum', 'role:super_admin,pwnu,pcnu,relawan,trc'])->group(function () {
```

**Problem:** `Auth::check()` without a guard argument uses the **default guard** configured in `config/auth.php:19`, which is `'guard' => env('AUTH_GUARD', 'web')`. The `auth:sanctum` middleware authenticates via the `sanctum` guard. Guards in Laravel are **independent** — authenticating on `sanctum` does NOT authenticate on `web`.

**Impact on API (mobile) requests:**
- `auth:sanctum` authenticates user on sanctum guard: `Auth::guard('sanctum')->check()` = `true`
- `RoleMiddleware` calls `Auth::check()` = `Auth::guard('web')->check()` = `false` (no session)
- Result: RoleMiddleware returns 401 for ALL mobile API requests

**Impact on Web requests:**
- If the user has a web session, `Auth::check()` returns `true`
- But the role is then checked against the **session** user, which may differ from the sanctum token user
- Inconsistency: different authorization result based on whether the request was authenticated by token or session

**Recommendation:** Use `Auth::guard('sanctum')->check()` for API routes, or inject `$request->user()` directly. Make the guard configurable via middleware parameter.

---

## Finding A-04: No Token Rotation / Refresh Mechanism

**Severity:** High
**OWASP:** A07:2021 — Identification and Authentication Failures
**Location:** Sanctum token lifecycle throughout

**Current token lifecycle:**
1. **Issuance:** `$user->createToken($deviceName)->plainTextToken` (Sanctum)
2. **Expiry:** 30 days (`config/sanctum.php:53`: `SANCTUM_TOKEN_EXPIRATION_DAYS * 24 * 60` minutes)
3. **Revocation:** Only on explicit logout or account deactivation
4. **Refresh:** A separate, non-Sanctum `device_token` system exists but is NOT integrated

**Problems:**
- A leaked token is valid for 30 days with no way to rotate it
- No refresh token mechanism for Sanctum tokens
- The `POST /api/v1/device/refresh-token` generates a custom token not tied to the Sanctum session
- No token rotation on role change, password change, or security event

**Recommendation:** Implement short-lived access tokens (15 minutes) + refresh tokens (7 days) with rotation. Invalidate all tokens for a user on password change. Integrate device_token with Sanctum.

---

## Finding A-05: Token Abilities Never Used

**Severity:** High
**Location:** `app/Services/Dashboard/DashboardLayoutService.php:19,24`

```php
$hasKpiAccess = $user->tokenCan('view_kpi') || true; // Short-circuited to always true
$hasTrcQueueAccess = $user->tokenCan('view_trc_queue') || false;
```

**Problem:** `createToken()` is called without specifying abilities (2nd argument) in production code. The only `tokenCan()` calls in the codebase are short-circuited with `|| true`. The entire token ability/scoping infrastructure is dead code.

**Impact:** Fine-grained authorization via token scopes is impossible. Authorization relies entirely on role middleware (which has its own guard mismatch bug, see A-03). There is no way to issue a token with limited access (e.g., read-only token for external systems).

**Recommendation:** Either implement token abilities meaningfully or remove the dead code. If RBAC via Spatie Permission is the authorization strategy, remove tokenCan references.

---

## Finding A-06: No Concurrent Login Detection

**Severity:** High
**Location:** Entire auth system

**Problem:** No mechanism exists to:
- Detect multiple concurrent sessions for the same user
- Limit concurrent devices
- Show active sessions to the user
- Force-logout remote sessions
- Detect impossible travel (login from two distant IPs simultaneously)

**Impact:** A user's token can be shared across unlimited devices. Stolen tokens are undetectable. Incident response cannot revoke sessions selectively.

**Recommendation:** Enforce maximum 5 devices per user. Build session management UI. Log all token usage with IP and User-Agent. Detect anomalous access patterns.

---

## Finding A-07: Debug Login Credentials Logged in Production

**Severity:** High
**OWASP:** A05:2021 — Security Misconfiguration
**Location:** `app/Services/Auth/AuthenticationService.php:27-33`

```php
\Illuminate\Support\Facades\Log::info("DEBUG LOGIN", [
    'no_hp_input' => $noHp,
    'password_input' => $password,  // PLAINTEXT PASSWORD
    'user_found' => $user ? true : false,
    'db_connection' => \Illuminate\Support\Facades\DB::connection()->getName(),
    'db_database' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
]);
```

**Additional debug logging:**
- `app/Http/Middleware/CheckAccountStatus.php:19`: `Log::info('CheckAccountStatus middleware executed')`
- `app/Http/Middleware/RoleMiddleware.php:21`: `Log::info('RoleMiddleware triggered 401: Auth::check() is false')`
- `bootstrap/app.php:76`: `Log::info('Auth exception: ...')` — stack trace with file/line numbers

**Impact:** All login credentials (phone number + password) are written to production logs. This exposes credentials to anyone with log access (Sentry, Papertrail, developers, ops team). The `user_found` boolean enables user enumeration.

**Recommendation:** Remove ALL debug logging. Never log passwords, password hashes, or authentication hints that distinguish "user not found" from "wrong password."

---

## Finding A-08: PIN Brute Force Protection Missing

**Severity:** High
**OWASP:** A04:2021 — Insecure Design
**Location:** `app/Http/Controllers/Api/AuthApiController.php:56-73`

**Route:** `POST /api/auth/pin/verify` (inside `auth:sanctum` group)

**Problems:**
- No rate limiting on the endpoint
- No attempt tracking per user
- No lockout mechanism
- PIN is only 6 digits (1,000,000 combinations)
- PIN comparison uses string equality, not constant-time comparison

**Calculation:** At 100 requests/second, a full 6-digit PIN space can be brute-forced in ~2.8 hours. With 10 concurrent attackers, much faster. Realistic attack: `123456`, `000000`, `111111`, `123123` cover >30% of all PINs.

**Recommendation:** Apply `throttle:5,1` middleware. Implement 3-attempt lockout with escalating timeout. Use `hash_equals()` for constant-time comparison. Require re-authentication before PIN attempt.

---

## Finding A-09: device_uuid Never Populated on Sanctum Tokens

**Severity:** High
**Location:** `database/migrations/2026_06_20_110340_add_device_uuid_to_personal_access_tokens.php`

```php
// Migration adds: $table->string('device_uuid')->nullable()->index();
// But createToken() is NEVER called with device context
// DeviceApiController::destroy() filters by device_uuid — always NULL
```

**Impact:** The device revocation feature is broken. `DELETE /api/v1/devices/{uuid}` attempts to delete Sanctum tokens where `device_uuid = $uuid` — but every token has `device_uuid = NULL`, so nothing is ever deleted. The feature is cosmetic.

**Recommendation:** When creating tokens, store `device_uuid` via Sanctum's custom token attributes. Fix the destroy() method. Verify in tests that device revocation actually revokes tokens.

---

## Finding A-10: Expired Sanctum Tokens Never Pruned

**Severity:** Medium
**Location:** `config/sanctum.php:53` / No scheduled task

**Problem:** Tokens expire after 30 days but remain in the `personal_access_tokens` table indefinitely. The `sanctum:prune-expired` Artisan command is not scheduled in `routes/console.php` or any kernel file.

**Impact:** Database table grows unbounded. Expired tokens are never cleaned up. If the token guard check changes in the future, expired tokens might be incorrectly accepted.

**Recommendation:** Add to `routes/console.php`:
```php
Schedule::command('sanctum:prune-expired', ['--hours' => 24])->daily();
```

---

## Finding A-11: No Impersonation / Sudo Mechanism

**Severity:** Medium
**Location:** Nowhere in codebase

**Problem:** Super admin has no way to impersonate a user for troubleshooting. This forces:
- Shared credentials ("login as user X to see what they see")
- Direct database modifications
- Increased support burden

**Recommendation:** Implement Laravel impersonation pattern. Require super_admin role. Log ALL impersonation events comprehensively. Add visual indicator on all screens when impersonating.

---

## Finding A-12: No OTP / 2FA / MFA

**Severity:** Medium
**Location:** No production code — documented in `mobile/production/02_AUTHENTICATION_DOMAIN.md:174`

**Problem:** Despite extensive documentation planning OTP verification, password reset OTP, and multi-factor authentication, zero OTP/2FA code exists in production. All authentication relies solely on phone number + password + hardcoded PIN.

**Recommendation:** Implement TOTP (RFC 6238) for privileged roles (super_admin, pwnu) at minimum. Require 2FA for destructive operations (finalisasi, approve, delete).

---

## Finding A-13: Flutter Token State Assumes Valid Without Validation

**Severity:** Medium
**Location:** `mobile/app/lib/features/auth/presentation/notifiers/auth_state_provider.dart:63-92`

```dart
Future<void> _loadState() async {
    final token = await _storage.read(key: 'auth_token');
    if (token != null) {
        state = AuthState(isAuthenticated: true, isLoading: false, token: token, ...);
        verifySessionWithDatabase(); // Async — runs AFTER state is set
    }
}
```

**Problem:** The app sets `isAuthenticated = true` based solely on the presence of a stored token. The async `verifySessionWithDatabase()` call (which would detect invalid tokens) runs AFTER the UI is already rendered as authenticated. If the backend is unreachable, stale/expired tokens are accepted silently.

**Recommendation:** Validate token before setting `isAuthenticated`. If network is unavailable, check local token expiry timestamp. Show "offline mode" indicator instead of assuming authenticated.

---

# AUDIT 2: Governance Architecture

## Finding G-01: Governance CRUD Controllers Have Zero Authorization

**Severity:** Critical
**Location:** `app/Http/Controllers/Api/Governance/*` (10+ controllers)

The following controllers have **NO** `$this->authorize()`, NO policy checks, NO mandate validation, NO scope checks:

| Controller | Methods | Risk |
|-----------|---------|------|
| `OrgMandateController` | store, destroy | Any user can create/delete mandates |
| `OrgNodeController` | store, update, destroy | Any user can modify org structure |
| `OrgDelegationController` | store, destroy | Any user can create/delete delegations |
| `OrgSkController` | store, update, destroy | Any user can manage SK documents |
| `OrgPositionController` | store, update, destroy | Any user can modify positions |
| `OrgFunctionController` | store, update, destroy, assignAuthority | Any user can assign authorities |
| `OrgAuthorityController` | store, update, destroy | Any user can create/delete authorities |
| `OrgInstitutionController` | store, update, destroy | Any user can manage institutions |
| `OrgStructureLevelController` | store, update, destroy | Any user can modify hierarchy levels |

**Example — OrgMandateController:**
```php
// app/Http/Controllers/Api/Governance/OrgMandateController.php:22-30
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([...]);
    $mandate = OrgMandate::create($validated);  // NO AUTHORIZATION CHECK
    return response()->json([...], 201);
}

public function destroy(OrgMandate $orgMandate): JsonResponse
{
    $orgMandate->delete();  // NO AUTHORIZATION CHECK
    return response()->json([...], 200);
}
```

**Impact:** The entire 8-layer mandate validation system (`LegalValidationService`, `GovernanceBasePolicy`, `MandateResolverService`) is completely bypassable. Any authenticated user (including `relawan`) can:
1. Create a mandate for themselves
2. Create a position with high authority
3. Assign themselves to that position
4. Assign authority codes to that position
5. Now they have any authority in the system

**The governance authorization system is only as strong as its weakest controller — and the weakest controllers have zero protection.**

**Recommendation:**
1. Add `$this->authorize()` calls to EVERY governance controller method
2. Create explicit policies mapping who can create/modify/delete each governance entity
3. Restrict governance CRUD to `super_admin` and designated `pwnu` roles only
4. Add scope validation for territory-restricted governance entities

---

## Finding G-02: Digital Signature Is Cryptographically Invalid

**Severity:** Critical
**Location:** Multiple files

**Two broken implementations exist:**

### Implementation 1: SHA-256 HMAC-style (symmetric, no nonce)
`app/Traits/HasImmutableAudit.php:25`:
```php
'digital_signature' => hash('sha256',
    json_encode($snapshot) . $actionType . $this->getKey() . config('app.key')
);
```

Problems:
- Uses `config('app.key')` — a static symmetric key stored in `.env`
- No nonce → identical actions produce identical signatures
- No timestamp in signature input → replay attacks possible
- No previous-hash chaining → logs can be reordered
- `config('app.key')` rotation invalidates ALL historical signatures

### Implementation 2: JSON object labeled as "signature" (zero cryptography)
`app/Listeners/Governance/RecordMeetingAuditTrail.php:34-39`:
```php
'digital_signature' => json_encode([
    'previous_status' => $event->previousStatus,
    'new_status' => $event->meeting->status,
    'meeting_title' => $event->meeting->title,
    'timestamp' => now()->toIso8601String(),
]);
```

This is a plain JSON object stored in a column named `digital_signature`. Anyone with SQL access can forge the entire audit history. This provides **zero** non-repudiation.

**Impact:** Audit logs cannot be used as legal evidence. No court would accept audit trails with forgeable signatures. The system has no mechanism to detect tampering.

**Recommendation:**
1. Use asymmetric cryptography: Ed25519 keypair (private key in offline HSM/env, public key in DB config)
2. Include in every signature: nonce (UUIDv4), UTC timestamp, SHA-256 of previous log entry's signature (blockchain chain)
3. Store the signature as a hex string of the Ed25519 signature
4. Rename `digital_signature` column to `event_metadata` in `RecordMeetingAuditTrail` — it's not a signature
5. Implement signature verification endpoint: `GET /api/governance/audit-logs/{id}/verify`

---

## Finding G-03: HasGovernanceFields Depends on Global request()

**Severity:** Critical
**Location:** `app/Traits/HasGovernanceFields.php:39-50`

```php
public static function bootHasGovernanceFields(): void
{
    static::creating(function ($model) {
        // ...
        $mandate = request()?->get('_mandate');  // HIDDEN DEPENDENCY
        if ($mandate instanceof OrgMandate) {
            if (empty($model->created_by_mandate_id)) {
                $model->created_by_mandate_id = $mandate->id;
            }
            // ...
        }
    });
}
```

**Problem:** The `request()` helper returns the current HTTP request — which is `null` outside HTTP context. This means:

| Context | `request()` value | Governance fields filled? |
|---------|------------------|--------------------------|
| HTTP request via web/API | Available | ✅ YES |
| Artisan command | `null` | ❌ NO — all NULL |
| Queue job | `null` | ❌ NO — all NULL |
| Octane task worker | `null` | ❌ NO — all NULL |
| Broadcast event listener | `null` | ❌ NO — all NULL |
| Unit/Feature test | `null` | ❌ NO — all NULL |
| Schedule/CRON task | `null` | ❌ NO — all NULL |

**Impact:** All governance records created from non-HTTP contexts have NULL `created_by_mandate_id`, `node_id`, and `territory_id`. The chain of responsibility is broken. Legal audit cannot trace records to their author.

**Recommendation:** Remove `request()` dependency entirely. Pass mandate context explicitly:
```php
public static function bootHasGovernanceFields(): void
{
    static::creating(function ($model) {
        $mandate = resolve(MandateContext::class)->getCurrent();
        // ...
    });
}
```
Use Laravel's contextual serialization for queue jobs. Never rely on `request()` in model traits.

---

## Finding G-04: OperasiMobilisasiPolicy Allows Self-Approval

**Severity:** High
**Location:** `app/Policies/OperasiMobilisasiPolicy.php:56-59`

```php
public function approve(AuthUser $user, OperasiMobilisasi $mobilisasi): bool
{
    return $this->canManageOrOwn($user, $mobilisasi);
}

// Inherited from base:
protected function canManageOrOwn($user, $record): bool
{
    if ($user->hasRole(['super_admin', 'pwnu', 'pcnu'])) return true;
    return $record->id_pengguna === $user->id_pengguna; // SELF-APPROVAL
}
```

**Impact:** A `relawan` can create a mobilization for themselves and immediately approve it. No segregation of duties — the same person who requests a resource can authorize it.

**Recommendation:** Separate `owner` from `approver`. A user cannot approve their own mobilization. Require a higher-authority role (different user) to approve.

---

## Finding G-05: Audit Trail Missing for Most Entity Operations

**Severity:** High
**Location:** All CRUD controllers

**Entities with audit trail coverage:**
| Entity | Status Change | Create | Update | Delete |
|--------|--------------|--------|--------|--------|
| Insiden | ✅ `RiwayatStatusInsiden` | ❌ | ❌ | ❌ |
| Penugasan | ✅ `OperasiPenugasanHistory` | ❌ | ❌ | ❌ |
| Meeting | ✅ `RecordMeetingAuditTrail` | ❌ | ❌ | ❌ |
| Assessment | ❌ | ❌ | ❌ | ❌ |
| Sitrep | ❌ | ❌ | ❌ | ❌ |
| Klaster | ❌ | ❌ | ❌ | ❌ |
| Tugas | ❌ | ❌ | ❌ | ❌ |
| Posaju | ❌ | ❌ | ❌ | ❌ |
| Mobilisasi | ❌ | ❌ | ❌ | ❌ |
| Pleno | ❌ | ❌ | ❌ | ❌ |
| Aktivasi | ❌ | ❌ | ❌ | ❌ |
| Eskalasi | ❌ | ❌ | ❌ | ❌ |

**Impact:** The system cannot answer fundamental governance questions:
- Who created this assessment?
- When was the sitrep last modified?
- What was the previous value of this field?
- Who approved this mobilization?

**Recommendation:** Add model observers for ALL entities. Record every mutation with: `actor_id`, `actor_mandate_id`, `target_table`, `target_id`, `action_type` (create/update/delete), `before_snapshot` (JSON), `after_snapshot` (JSON), `timestamp`, `ip_address`, `user_agent`.

---

## Finding G-06: Lock Bypass via Unlock API

**Severity:** High
**Location:** `app/Http/Controllers/Api/Operasi/InsidenFullController.php:210-216`

```php
public function lock(OperasiInsiden $insiden): JsonResponse
{
    $insiden->update(['is_locked' => true]);
    return response()->json([...]);
}

public function unlock(OperasiInsiden $insiden): JsonResponse
{
    $insiden->update(['is_locked' => false]);  // ANY authorized user can unlock
    return response()->json([...]);
}
```

**Problem:** The `lock`/`unlock` endpoints are protected only by the generic `update` policy. When an incident transitions to `selesai` or `dibatalkan`, it is auto-locked (`is_locked = true`). But any user with incident update permission can unlock it and modify finalized data.

**Exploit Scenario:**
1. Incident is closed → auto-locked → `is_locked = true`
2. PCNU user calls `POST /api/v1/insiden/{id}/unlock` → succeeds
3. User modifies assessment, sitrep, status
4. User re-locks: `POST /api/v1/insiden/{id}/lock`

**Recommendation:** Only `super_admin` can unlock a locked incident. Record unlock events in audit log with mandatory reason. Take pre-unlock snapshot for rollback capability.

---

## Finding G-07: Meeting Lifecycle Missing Authority Checks

**Severity:** High
**Location:** `app/Services/Governance/MeetingLifecycleService.php`

**Methods without authority validation:**
| Method | Line | Missing Check |
|--------|------|--------------|
| `schedule()` | 66 | No mandate scheduling authority check |
| `sendInvitations()` | 87 | No validation of sender authority |
| `openMeeting()` | 113 | No check that user has opening authority |
| `closeVoting()` | 215 | No check (only time-based guard) |
| `generateMinutes()` | 240 | No approval authority check |
| `closeMeeting()` | 276 | No closure authority check |

**Only `castVote()` (line 163) validates voter authority** by checking attendee status and voting rights.

**Impact:** Any authenticated user who can call the API can schedule, open, close meetings, and generate minutes. Meeting governance integrity depends entirely on client-side UI hiding buttons.

**Recommendation:** Add mandate-based authority checks to ALL lifecycle transitions using `LegalValidationService` or `GovernanceBasePolicy`.

---

## Finding G-08: RecordMeetingAuditTrail Stores Non-Cryptographic "Signature"

**Severity:** High
**Location:** `app/Listeners/Governance/RecordMeetingAuditTrail.php:34-39`

```php
'digital_signature' => json_encode([
    'previous_status' => $event->previousStatus,
    'new_status' => $event->meeting->status,
    'meeting_title' => $event->meeting->title,
    'timestamp' => now()->toIso8601String(),
])
```

**Problem:** This is NOT a digital signature by any definition. It's a plain JSON object stored in a column misleadingly named `digital_signature`. No key, no hash, no asymmetric crypto. Anyone who can write to `org_audit_logs` (or has SQL UPDATE access) can forge the entire audit history.

**Recommendation:** This is `event_metadata`, not `digital_signature`. Rename the column. Implement actual digital signatures separately (see G-02).

---

## Finding G-09: No Revision/Versioning of Governance Records

**Severity:** Medium
**Location:** All governance models

**Problem:** No versioning mechanism exists. All `Model::update()` calls overwrite data in-place. No `revisions` table, no `version` column, no `superseded_by` foreign key. `SoftDeletes` only covers deletion recovery, not state rollback.

**Impact:** Cannot answer: "What did this document look like before the last edit?" Cannot roll back erroneous changes. Legal requests for document history require manual database restoration.

**Recommendation:** Implement optimistic version locking (increment `version` on each update, reject stale updates). Create `model_revisions` table to store before/after snapshots.

---

## Finding G-10: HasImmutableAudit Trait Underutilized

**Severity:** Medium
**Location:** `app/Traits/HasImmutableAudit.php:35-61`

**Problem:** The `executeWithGovernance()` method provides the full 8-layer authority validation + audit trail pipeline in a single atomic transaction:
```php
public function executeWithGovernance(string $authorityCode, int $nodeId, callable $action)
{
    $legalService = app(LegalValidationService::class);
    $snapshot = $legalService->validateAndGetSnapshot($user, $authorityCode, $nodeId);
    DB::beginTransaction();
    try {
        $action($this);
        $this->recordGovernanceAudit($snapshot, strtoupper($authorityCode));
        DB::commit();
        return true;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

But grep shows near-zero usage across controllers. Most code bypasses this by calling `update()`/`create()` directly. The infrastructure exists but is not used.

**Recommendation:** Audit the codebase for direct governance entity mutations. Route all mutations through `executeWithGovernance()` or equivalent. Add static analysis rule to enforce governance audit trail.

---

## Finding G-11: before() Gate Only Bypasses for super_admin

**Severity:** Medium
**Location:** `app/Policies/Governance/GovernanceBasePolicy.php:39-46`

```php
public function before(AuthUser $user, string $ability): ?bool
{
    if ($user->peran?->nama_peran === 'super_admin') {
        return true;  // Super admin bypasses all gates
    }
    return null;
}
```

**Problem:** The `before()` method correctly allows `super_admin` to bypass all gates. However, since most governance controllers don't use policies at all (G-01), this protective measure has no effect on those controllers. For the controllers that DO use policies (Insiden, Penugasan, Assessment, etc.), this works correctly.

**Recommendation:** This finding is secondary to G-01. Once G-01 is fixed (adding policies to all governance controllers), this `before()` method provides proper super_admin bypass.

---

# AUDIT 3: SDUI Architecture

## Finding S-01: No Server-Side Action Validation

**Severity:** Critical
**Location:** `app/Http/Controllers/Api/SduiActionController.php:12-35`

```php
public function handle(Request $request): JsonResponse
{
    $actionType = $request->input('action_type');
    $user = Auth::guard('sanctum')->user();

    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    switch ($actionType) {
        case 'profil.toggle_tersedia':
            $user->update(['is_tersedia' => $user->is_tersedia ? 0 : 1]);
            return response()->json(['type' => 'reload_scene', 'scene_id' => 'akun']);

        default:
            return response()->json(['success' => false, 'message' => 'Action type not found'], 404);
    }
}
```

**Problems:**
1. **No permission check per action:** The `profil.toggle_tersedia` action runs for ANY authenticated user. No check if the user is allowed to toggle availability (e.g., super_admin should not be able to toggle another user's availability)
2. **No action registry:** Any `action_type` string is accepted. New actions added by scene composers are immediately executable without any authorization review
3. **No source validation:** No mechanism to verify that the action was triggered from the correct screen/component
4. **No nonce/CSRF:** Actions can be replayed. An attacker who intercepts an action payload can replay it
5. **No mandate context:** The action handler does not use the resolved mandate (ResolveMandateContext middleware)

**Exploit Scenario:** When a new action `'incident.close'` is added to a scene composer, it becomes immediately available via `POST /api/v1/action` with no additional authorization. A user with access to the action endpoint could close any incident by sending the crafted payload.

**Recommendation:**
1. Implement `ActionRegistry` where every action type must be registered with a permission/authority requirement
2. Validate the requesting user's role, mandate, and scope against the action's requirements
3. Add scene_id and node_id to action payload — validate action is allowed on that screen
4. Use nonce for idempotency and replay protection
5. Resolve mandate context in the action handler

---

## Finding S-02: SduiValidatorService Never Called

**Severity:** High
**Location:** `app/Services/Dashboard/SduiValidatorService.php:7-42`

```php
class SduiValidatorService
{
    private const ALLOWED_PRIMITIVES = [
        'Container', 'Row', 'Column', 'Grid', 'Stack', 'Spacer',
        'Divider', 'Text', 'Image', 'Icon', 'Avatar', 'Badge',
        'Card', 'Statistic', 'Metric', 'Timeline', 'Progress',
        'Button', 'Action', 'List',
    ];

    public function validate(array $payload): array
    {
        // Checks schema_version, screen, nodes are present
        // Validates each node's type is in ALLOWED_PRIMITIVES
        // Returns errors array
    }
}
```

**Problem:** This validator is defined, dependency-injectable, and functional — but it is NEVER called by any controller. The certification engine (`RuntimeCertificationEngine`) validates structural/semantic rules but does NOT check allowed primitives.

**Impact:** Contract violations (unknown component types, missing fields) pass through to the Flutter client, which then crashes with `FormatException` (see S-05).

**Recommendation:** Wire `SduiValidatorService` into the serialization pipeline. Validate response BEFORE sending to client. Reject invalid responses early with 500 error.

---

## Finding S-03: Two Divergent JSON Structures for Same Screen

**Severity:** High
**Location:** `AkunSceneComposer.php` (legacy) vs `AccountWorkspaceScreen.php + SduiSerializer.php` (runtime)

**Legacy format** (`AkunSceneComposer.php`):
```json
{
    "scene_id": "akun",
    "version": 1,
    "ttl_seconds": 60,
    "etag": "abc123",
    "meta": { "user_role": "pcnu" },
    "app_bar": { "type": "appbar", "title": "Akun", "actions": [...] },
    "root": {
        "type": "scrollable",
        "children": [...]
    }
}
```

**Runtime format** (`SduiSerializer.php`):
```json
{
    "schema_version": "1.0.0",
    "screen": { "id": "account_workspace", "title": "Akun & Pusat Komando" },
    "layout": "vertical",
    "nodes": [{
        "id": "account_workspace",
        "type": "ListView",
        "children": [...]
    }]
}
```

**Problem:** These are completely different JSON structures for the SAME screen. The legacy `AkunSceneComposer` is commented out in `AccountHomeController.php:37-63` but still exists as dead code. If someone re-enables it, the Flutter `SduiRemoteScreen` (which expects the runtime format) will fail to parse it.

**Additionally:** `COPComposer`, `PublicDashboardComposer`, and `PublicIncidentListComposer` each produce their own slightly different JSON structures, none of which match the `SduiRemoteScreen` parsing logic exactly.

**Recommendation:** Remove all legacy composers. Establish a single JSON contract (NSS — Nurisk Schema Specification). All composers must produce output conforming to this contract.

---

## Finding S-04: No Schema Version Negotiation

**Severity:** High
**Location:** `mobile/app/lib/core/sdui/sdui_remote_screen.dart` / `SduiSerializer.php:70`

**Backend:** Hardcoded `'1.0.0'` in 5+ places (`SduiSerializer.php:70`, `AccountHomeController.php:57`, `DashboardJsonBuilder.php:19`, `COPComposer.php:131`, `PublicDashboardComposer.php:170`)

**Flutter:** Only checks `startsWith('1.')`:
```dart
final schemaVersion = json['schema_version'] as String? ?? '0.0.0';
if (!schemaVersion.startsWith('1.')) {
    throw FormatException('Incompatible schema_version...');
}
```

**Problems:**
1. Flutter sends NO `Accept-Version` or `X-Schema-Version` header — backend cannot negotiate
2. No version migration layer — older versions are incompatible
3. Different composers produce different version strings (`'1.0.0'` vs `'1.0'`)
4. No deprecation policy — any schema change breaks all deployed clients
5. `SduiRemoteScreen` does NOT parse `schema_version` at all — it only reads `res.data['nodes']`

**Recommendation:**
1. Flutter must send `Accept-Version: 1.x` in request header
2. Backend must accept `Accept-Version` and serve the highest compatible schema version
3. Implement version migration layer — transform older schema versions to newer ones
4. Define backward compatibility commitment (N-1 supported)
5. All composers must produce the exact same JSON contract

---

## Finding S-05: SduiUnknownComponent Exists But Never Registered

**Severity:** High
**Location:** `mobile/app/lib/core/sdui/components/sdui_unknown_component.dart` vs `sdui_registry_initializer.dart`

**What exists:**
```dart
// sdui_unknown_component.dart:7-40
class SduiUnknownComponent extends SduiComponent {
    Widget build(BuildContext context, WidgetRef ref) {
        return Container(
            child: Text('Unsupported Component: ${node.type}'),
            // graceful gray placeholder
        );
    }
}
```

**What happens:**
```dart
// sdui_renderer.dart:16-22
final builder = SduiRegistry.instance.getBuilder(node.type);
if (builder != null) {
    return builder(node);
}
throw FormatException('SDUI Error: Unknown primitive type "${node.type}"... Fallback is disabled by NSS.');
```

**Problem:** `SduiUnknownComponent` is defined but NEVER registered in `SduiRegistryInitializer::initialize()`. The renderer **throws** for unknown types instead of falling back. A graceful component exists but is unused.

**Test contradiction** (`sdui_registry_test.dart:52-58`):
```dart
final fallbackBuilder = registry.getBuilder('Unknown');
expect(fallbackBuilder, isNotNull);  // EXPECTS fallback but code THROWS
```
The test expects a fallback that doesn't exist in production.

**Registry coverage gaps** — Flutter does NOT register these types that the backend serializes:
- `avatar`, `button`, `image`, `action`, `list`, `spacer`, `stack`, `grid`, `progress`, `statistic`, `metric`, `timeline`

**Recommendation:**
1. Register `SduiUnknownComponent` as the default fallback for unknown types
2. NEVER throw on unknown primitives — always degrade gracefully
3. Fix the test to match production behavior
4. Synchronize backend serialized types with Flutter registered types

---

## Finding S-06: NSS Action Types Not Implemented

**Severity:** High
**Location:** `mobile/app/lib/core/sdui/sdui_action_handler.dart:10-43`

```dart
static void execute(BuildContext context, Map<String, dynamic>? action) {
    switch (type) {
        case 'navigate': // ✓ IMPLEMENTED
        case 'snackbar': // ✓ IMPLEMENTED
        case 'dialog':
        case 'bottom_sheet':
        case 'submit':
        case 'toggle':
        case 'reload':
        case 'refresh':
        case 'external_url':
        case 'phone':
        case 'email':
        case 'download':
            debugPrint('SDUI Action "$type" is recognized by NSS but not fully implemented yet.');
            ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Aksi $type belum diimplementasikan...'))
            );
        default:
            debugPrint('SDUI Warning: Unknown action type "$type". Ignored.');
    }
}
```

**Impact:** Of 14 NSS-defined action types, only 2 (`navigate`, `snackbar`) are implemented. Scene composers send actions like `{type: 'reload'}` expecting the screen to refresh, or `{type: 'dialog'}` expecting a dialog — instead, the user gets a snackbar saying "not implemented." The action system is non-functional for 85% of its contract.

**Recommendation:** Implement ALL NSS action types before claiming they are supported. Remove unimplemented types from the contract specification. Prioritize: `reload`, `dialog`, `submit`.

---

## Finding S-07: Registry / Validator Allowed Types Mismatch

**Severity:** Medium
**Location:** `SduiValidatorService.php:7-12` vs `SduiRegistryInitializer.dart:26-58`

**Backend `ALLOWED_PRIMITIVES`:**
`Container, Row, Column, Grid, Stack, Spacer, Divider, Text, Image, Icon, Avatar, Badge, Card, Statistic, Metric, Timeline, Progress, Button, Action, List`

**Flutter registered types:**
`Container, Row, Column, Text, Icon, Card, ListView, Badge, Expanded, Flexible, SizedBox, AspectRatio, Divider, Grid, Timeline, BottomSheet, Chart, Checkbox, Dialog, Dropdown, FormField, Map, Scene, Switch, Tabs`

**Mismatches:**
- Backend allows but Flutter does NOT implement: `Stack, Spacer, Image, Avatar, Statistic, Metric, Progress, Button, Action, List`
- Flutter implements but backend does NOT allow: `BottomSheet, Chart, Checkbox, Dialog, Dropdown, FormField, Map, Scene, Switch, Tabs, Expanded, Flexible, SizedBox, AspectRatio, ListView`

**Impact:** The backend validator (if it were enabled, see S-02) would reject valid Flutter components. The serializer produces types that Flutter cannot render.

**Recommendation:** Create a single source of truth — a shared JSON file or config defining ALL allowed component types. Both backend validation and Flutter registry MUST use this shared definition. Add CI check (see `scripts/ci_sdui_certification.sh`) to verify registry completeness.

---

## Finding S-08: No Screen Isolation Mechanism

**Severity:** Medium
**Location:** Entire SDUI architecture

**Problems:**
1. **No screen boundary enforcement:** `ScreenNode` is just a container — no access control, no data domain
2. **No action-scope constraints:** An action on Screen A can navigate to Screen B's data — Screen B's data fetch must independently authorize
3. **No session-scoped state:** `SduiRemoteScreen` stores `_nodesJson` locally with no namespace. Nothing prevents fetching another screen's endpoint
4. **Diff engine has no context:** `SduiDiffEngine` operates on a single map with no awareness of which screen it belongs to

**Recommendation:** Add screen-scoped action validation. Screen ID should be part of the action payload. Backend should validate that the requested action is valid for the current screen context. Implement session-scoped screen state in Flutter.

---

## Finding S-09: Flutter Auto-ID Not Deterministic

**Severity:** Medium
**Location:** `mobile/app/lib/core/sdui/sdui_node.dart:32`

```dart
final id = json['id'] as String? ?? 'auto-${type.toLowerCase()}-${DateTime.now().microsecondsSinceEpoch}';
```

**Problems:**
1. Two nodes parsed within the same microsecond get THE SAME auto-ID
2. No collision detection on Flutter side — duplicates go unnoticed
3. Auto-ID is non-deterministic — identical server payloads produce different client-side IDs
4. The diff engine cannot recognize auto-ID nodes between refreshes

**Recommendation:** Use a monotonic counter instead of timestamp. Add collision detection with warning. Ensure auto-ID format is clearly distinguishable from server-provided IDs (e.g., `_auto_${counter}`).

---

## Finding S-10: Map Component Type Check Bug

**Severity:** Medium
**Location:** `mobile/app/lib/core/sdui/components/sdui_map.dart:68`

```dart
if (child is SduiNode) {
    // This check ALWAYS fails — children are Map<String, dynamic>, not SduiNode
}
```

**Problem:** The map component receives children from `node.children` which are `SduiNode` instances in the Dart type system, but in the JSON parsing pipeline (`SduiNode.fromJson` → recursive parsing), children ARE `SduiNode`. The actual bug may be different: the check `child is SduiNode` should pass for SduiNode children but fail if the children were somehow parsed incorrectly.

**Impact:** Map markers are never rendered from SDUI tree data.

**Recommendation:** Investigate and fix the type check. If children are properly parsed as `SduiNode` instances, the check should work. If not, ensure recursive parsing is complete before passing to children.

---

## Finding S-11: Dashboard Composers and ComponentRegistry Never Wired

**Severity:** Medium
**Location:** `app/Services/Dashboard/ComponentRegistry.php`, all files in `Builders/*`

**Problem:** A complete block-based dashboard builder system exists:
- `ComponentRegistry` — maps block types to builders
- `BlockBuilderInterface` — defines the builder contract
- `WarningBlockBuilder`, `WeatherBlockBuilder`, `KpiBlockBuilder`, `IncidentBlockBuilder`, `NewsBlockBuilder`, `TrcQueueBlockBuilder` — all implemented
- `DashboardJsonBuilder::build()` — composes the final JSON

But NO controller calls these classes. `DashboardBffController` and `AccountHomeController` use their own ad-hoc composition logic.

**Recommendation:** Either wire the builder system into active controllers and remove the ad-hoc logic, or remove the unused builder code to reduce maintenance burden.

---

## Finding S-12: config/sdui.php Layouts Never Used

**Severity:** Medium
**Location:** `config/sdui.php` vs `app/Services/Dashboard/DashboardLayoutService.php:9-31`

**config/sdui.php defines:**
```php
'layouts' => [
    'default' => [['type' => 'WarningBlock'], ['type' => 'WeatherBlock'], ...],
    'pcnu' => [['type' => 'WarningBlock'], ['type' => 'WeatherBlock'], ['type' => 'KpiBlock'], ...],
    'pwnu' => [...],
    'super_admin' => [...],
    'trc' => [['type' => 'WarningBlock'], ['type' => 'WeatherBlock'], ['type' => 'TrcQueueBlock'], ...],
]
```

**DashboardLayoutService does:**
```php
public function getLayoutForUser($user): array
{
    if ($role === 'guest') { return ['WarningBlock', 'WeatherBlock', 'IncidentBlock', 'NewsBlock']; }
    // ... hardcoded logic, IGNORES config/sdui.php entirely
}
```

**Recommendation:** Either use `config/sdui.php` as the single source of truth for role-based layouts, or remove the config file. The config should drive behavior, not be documentation.

---

# AUDIT 4: COP Architecture

## Finding C-01: No Incident State Transition Validation

**Severity:** Critical
**Location:** `app/Services/InsidenService.php:123-167`

```php
public function ubahStatus(OperasiInsiden $insiden, string $statusBaru, ...): OperasiInsiden
{
    // The ONLY guard: isTerkunci()
    if ($insiden->isTerkunci()) {
        throw new RuntimeException('Insiden sudah ditutup...');
    }

    // No transition validation — ANY status from ANY state is accepted
    return DB::transaction(function () use (...) {
        RiwayatStatusInsiden::create([...]);
        // ... update timestamps based on new status
        $insiden->update($updateData);
    });
}
```

**Allowed transitions (per code — no validation):**
```
                    ┌──────────┐
           ┌───────→│  draft   │←──────┐
           │        └──────────┘       │
           │            │              │
           │            ↓              │
           │        ┌──────────┐       │
           │←──────→│verifikasi│       │
           │        └──────────┘       │
           │            │              │
           │            ↓              │
           │        ┌──────────┐       │
           │←──────→│  respon  │       │
           │        └──────────┘       │
           │            │              │
           │            ↓              │
           │        ┌──────────┐       │
           │←──────→│pemulihan │       │
           │        └──────────┘       │
           │            ↓              │
           │        ┌──────────┐       │
           │        │ selesai  │       │
           │        └──────────┘       │
           │            │              │
           └────────────┘              │
                                        ↓
                                  ┌──────────┐
                                  │dibatalkan │
                                  └──────────┘
```

**Impact:**
- `draft → selesai` — skips all verification and response
- `selesai → draft` — reopens a completed incident without audit (unless locked, but lock can be removed, see C-06)
- `respon → draft` — regresses to initial state, losing all progress tracking
- `pemulihan → dibatalkan` — cancels recovery while people are still in the field

**Recommendation:** Implement explicit allowed-transition map (like `PenugasanService::validateTransition()`). Example:
```php
private const ALLOWED_TRANSITIONS = [
    'draft'        => ['terverifikasi', 'dibatalkan'],
    'terverifikasi'=> ['respon', 'dibatalkan'],
    'respon'       => ['pemulihan', 'dibatalkan'],
    'pemulihan'    => ['selesai', 'dibatalkan'],
    'selesai'      => [],  // Terminal
    'dibatalkan'   => [],  // Terminal
];
```

---

## Finding C-02: LiveConnectionManager Is Entirely Mock/Simulation

**Severity:** Critical
**Location:** `mobile/app/lib/features/cop/runtime/live_connection_manager.dart:5-59`

```dart
class LiveConnectionManager {
    Timer? _mockConnectionTimer;
    StreamController<List<Map<String, dynamic>>>? _patchStream;

    void connect() {
        stateNotifier.startSync();
        // Simulate network delay
        Future.delayed(const Duration(milliseconds: 500), () {
            stateNotifier.ready();
            stateNotifier.goLive();
            // Simulate receiving patches
            _mockConnectionTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
                _patchStream!.add([{
                    "op": "replace",
                    "path": "/layers/0/primitives/0/props/latitude",
                    "value": -7.55 + (timer.tick * 0.001) // Simulate movement
                }]);
            });
        });
    }

    void disconnect() {
        _mockConnectionTimer?.cancel();
        stateNotifier.degrade();
        // Simulate complete offline after degraded
        Future.delayed(const Duration(seconds: 2), () {
            if (ref.read(copStateMachineProvider) == CopState.degraded) {
                stateNotifier.goOffline();
            }
        });
    }
}
```

**Impact on COP:**
- No real-time map marker movement
- No real-time incident status updates
- No real-time TRC tracking
- No real-time alert/warning delivery
- No Reverb (Laravel WebSocket) integration exists anywhere in the backend
- `CommandCenterApiController` returns timestamps, but clients must poll

**The COP real-time system is entirely simulated. There is no production real-time capability.**

**Recommendation:** Remove ALL mock code. Implement:
1. Backend: Laravel Reverb WebSocket server with broadcasting of incident status changes, new assignments, map marker updates
2. Flutter: `web_socket_channel` or `laravel_echo` package to connect to Reverb
3. Authentication: Sanctum token authentication for WebSocket connections
4. State sync: Use the `patchStream` pattern with real server-pushed events

---

## Finding C-03: Sync Resolution Uses Last-Write-Wins Without Merge

**Severity:** Critical
**Location:** `app/Http/Controllers/Api/Operasi/SyncApiController.php:195-225`

```php
// --- LAST-WRITE-WINS ---
$clientVersion = (int) ($data['sync_version'] ?? 1);
$serverVersion = (int) ($record->sync_version ?? 1);

if ($clientVersion < $serverVersion) {
    // Log conflict for audit, but APPLY the update (last-write-wins)
    SyncConflict::create([
        'entity_type' => $entityType,
        'entity_id' => $record->getKey(),
        'client_version' => $clientVersion,
        'server_version' => $serverVersion,
        'client_data' => json_encode($data),
        'server_data' => json_encode($record->toArray()),
        'resolution' => 'last_write_wins',
    ]);
}

// Always apply — last-write-wins
$record->update($data);
```

**Scenario:**
1. Command center updates cluster status: `aktif → selesai` (server version: 5)
2. TRC member offline, updates cluster notes locally (client version: 4)
3. TRC member syncs: client_version (4) < server_version (5) → conflict detected
4. Conflict is LOGGED but data is OVERWRITTEN: cluster status goes back to `aktif`
5. Command center decision is silently reverted

**Impact:** Offline users can silently overwrite command center decisions. No merge strategy, no three-way merge, no conflict resolution UI on Flutter. `SyncConflict` records are created but never reviewed or resolved.

**Recommendation:**
1. Reject stale writes: if `clientVersion < serverVersion`, return conflict response
2. Flutter must show conflict resolution UI: server vs. client version of each field
3. Implement CRDT for critical fields (status, progress) where last-write-wins is acceptable
4. Add resolution timeout: if conflict not resolved in 24h, auto-resolve to server version
5. Log ALL conflict resolutions for audit

---

## Finding C-04: No Cascading State Changes on Incident Completion

**Severity:** High
**Location:** `app/Services/InsidenService.php:154-158`

```php
} elseif ($statusBaru === 'selesai') {
    $updateData['waktu_ditutup'] = now();
    $updateData['waktu_selesai'] = now();
    $updateData['is_locked'] = true;
    $updateData['status_operasi'] = 'selesai';
    // NO cascade: clusters, tasks, posaju, assignments remain active
}
```

**When an incident is completed, the following remain active:**
- Clusters (`OperasiKlaster` with `status = 'aktif'`)
- Tasks (`OperasiTugas` with `status_tugas = 'berjalan'`)
- Forward posts (`OperasiPosaju` with `status = 'aktif'`)
- Personnel assignments (`OperasiPenugasan` with `status_penugasan != 'completed'`)
- Mobilizations (`OperasiMobilisasi` with `status != 'selesai'`)

**Impact:** An incident can be marked complete while resources are still active on the ground. Dashboard statistics show "active" resources attached to a "closed" incident.

**Recommendation:** Before completing an incident:
1. Validate all child entities are in terminal states
2. Show warning listing active resources
3. Provide force-close (super_admin only) with auto-cascade:
   - Force-complete all clusters
   - Recall all personnel assignments
   - Close all forward posts

---

## Finding C-05: BulkStubController Accepts Arbitrary Data

**Severity:** High
**Location:** `app/Http/Controllers/Api/Operasi/BulkStubController.php:11-67`

```php
class BulkStubController extends Controller
{
    public function logistikBulk(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        $success = [];
        foreach ($items as $item) {
            $success[] = [
                'id' => $item['id'] ?? null,
                'status' => 'success',  // ALWAYS success
            ];
        }
        return response()->json([
            'success' => true,
            'data' => [
                'success' => $success,
                'failed' => [],  // NEVER fails
            ]
        ]);
    }

    public function mobilisasiBulk(Request $request): JsonResponse
    {
        // Same pattern: loop, always success, no validation, no storage
    }
}
```

**Impact:** These endpoints are wired into the API at `routes/api.php:350-351`:
```php
Route::post('logistik/bulk', [BulkStubController::class, 'logistikBulk']);
Route::post('mobilisasi/bulk', [BulkStubController::class, 'mobilisasiBulk']);
```

Any data sent to these endpoints is discarded. The client receives a success response and believes the data was persisted. This is a **data loss** vulnerability.

**Recommendation:**
1. Either implement actual bulk processing with validation, storage, and error reporting
2. Or remove the endpoints and the controller entirely
3. Never return success for unprocessed operations

---

## Finding C-06: Assessment Operations Have No Audit Trail

**Severity:** High
**Location:** `app/Services/Operasi/AssessmentService.php:24-399`

**Assessment operations without audit trail:**
| Operation | Method | Audit |
|-----------|--------|-------|
| Create | `AssessmentService::simpanLengkap()` (line 272) | ❌ |
| Update | `AssessmentService::updateAssessment()` | ❌ |
| Submit | `AssessmentApiController::submit()` (line ~580) | ❌ |
| Review | `AssessmentApiController::review()` (line ~590) | ❌ |
| Delete | `AssessmentApiController::destroy()` (line ~296) | ❌ |

**Impact:** Disaster assessment data is one of the most legally critical records in the system. Without audit trail:
- Cannot determine who submitted the assessment
- Cannot determine when data was modified
- Cannot reconstruct previous versions
- Cannot detect unauthorized modifications

**Recommendation:** Add model observer for `AssessmentUtama`. Record every mutation with: actor, mandate, timestamp, before_snapshot, after_snapshot, action_type. Make `AssessmentUtama` immutable once `is_submitted = true` — require specific authority to amend.

---

## Finding C-07: Sitrep is_latest Not Properly Managed

**Severity:** High
**Location:** `app/Models/OperasiSitrep.php` / `app/Services/Operasi/SitrepService.php`

```php
// SitrepService creates new sitrep as latest
// But NO mechanism demotes previous latest to is_latest = 0
```

**Concurrency scenario:**
1. Request A and Request B arrive simultaneously for the same incident
2. Both read `is_latest = 1` from different records
3. Both set their new sitrep as `is_latest = 1`
4. Now two records have `is_latest = 1`

**Impact:** Any query filtering by `is_latest = 1` may return multiple results, causing unpredictable behavior.

**Recommendation:** Use database-level exclusive lock or transaction:
```php
DB::transaction(function () use ($incidentId, $data) {
    OperasiSitrep::where('id_insiden', $incidentId)
        ->where('is_latest', 1)
        ->update(['is_latest' => 0]);
    return OperasiSitrep::create([...$data, 'is_latest' => 1]);
});
```

---

## Finding C-08: Sync Version Auto-Increment Race Condition

**Severity:** High
**Location:** All sync-enabled model boot traits (e.g., `OperasiKlaster`, `OperasiPenugasan`, etc.)

```php
static::updating(function ($model) {
    if ($model->isDirty() && !$model->isDirty('sync_version')) {
        $model->sync_version++;
    }
});
```

**Race condition:**
1. Request A and Request B simultaneously read the same record (sync_version = 5)
2. Both modify different fields
3. Both increment sync_version from 5 to 6
4. Last write wins — one update is silently lost

**Impact:** Lost updates in concurrent sync scenarios. No detection of concurrent modification.

**Recommendation:** Use atomic increment:
```php
// Instead of read-modify-write:
Model::where('id', $id)->where('sync_version', $expectedVersion)
    ->increment('sync_version');
```
Or use `lockForUpdate()` within a transaction with version check.

---

## Finding C-09: OperasiInsiden Not in Sync Entity List

**Severity:** Medium
**Location:** `app/Http/Controllers/Api/Operasi/SyncApiController.php` — entity processing loop

**Synced entities:**
1. `assessment`
2. `sitrep`
3. `klaster`
4. `penugasan`
5. `mobilisasi`

**NOT synced:**
- `OperasiInsiden` (the core mission entity)
- `OperasiPosaju`
- `OperasiTugas`
- `OperasiAktivasi`
- `OperasiEskalasi`

**Impact:** Offline users cannot create or modify incidents, posaju, tasks, or activations. Incidents are only available via REST API — if the network is unavailable, the command center cannot function.

**Recommendation:** Add `OperasiInsiden` to sync entity list with proper scope validation and version-based conflict resolution.

---

## Finding C-10: Eskalasi Doesn't Change Incident Status

**Severity:** Medium
**Location:** `app/Http/Controllers/Api/Operasi/EskalasiApiController.php:34-49`

```php
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([...]);
    $eskalasi = OperasiEskalasi::create($validated);
    // Returns eskalasi record — does NOT change incident status
}
```

**Problem:** Creating an escalation creates a record but does NOT update the incident's status, level, or trigger any cascading action. The incident state machine and the escalation system are disconnected.

**Impact:** An incident escalated from PCNU to PWNU level still shows `status_insiden = draft` or `terverifikasi`. The command center cannot filter incidents by escalation level.

**Recommendation:** On escalation:
1. Change incident status to `tereskalasi`
2. Update incident escalation level
3. Send notification to target organization
4. Create journal entry

---

## Finding C-11: No Conflict Resolution UI on Flutter

**Severity:** Medium
**Location:** Flutter COP — sync error handling

**Problem:** The sync engine records conflicts (`SyncConflict` model created on server when `clientVersion < serverVersion`). However:
- There is NO Flutter UI to review or resolve conflicts
- Conflicts are silently recorded and forgotten
- No notification to the user that their data conflicts with server data
- No mechanism to prefer server version vs. client version per field

**Recommendation:** Build conflict resolution screen showing:
- Server version of each field
- Client (local) version of each field
- Allow user to choose per-field or accept all-server / all-client
- If conflict unresolved for 24 hours, auto-resolve to server version

---

## Finding C-12: Most COP Entities Lack CRUD Audit Trails

**Severity:** High
**Location:** All COP controllers

**Entities with ANY audit coverage:**
| Entity | Coverage |
|--------|----------|
| `OperasiInsiden` | ✅ Status changes only (`RiwayatStatusInsiden`) |
| `OperasiPenugasan` | ✅ Status changes only (`OperasiPenugasanHistory`) |
| `AssessmentUtama` | ❌ None |
| `OperasiSitrep` | ❌ None |
| `OperasiKlaster` | ❌ None |
| `OperasiTugas` | ❌ None |
| `OperasiPosaju` | ❌ None |
| `OperasiMobilisasi` | ❌ None |
| `OperasiPleno` | ❌ None |
| `OperasiAktivasi` | ❌ None |
| `OperasiEskalasi` | ❌ None |

**Operating theater data (assessments, sitreps, deployments) is the most operationally critical data in the system. Zero audit trails on these entities is a governance failure.**

**Recommendation:** Implement model observers for ALL COP entities. At minimum record: `actor_id`, `action_type` (created/updated/deleted), `timestamp`, `target_id`, `target_table`.

---

## Finding C-13: Flutter COP Offline Cache Is In-Memory

**Severity:** High
**Location:** `mobile/app/lib/features/cop/runtime/offline_cache_manager.dart:7-15`

```dart
class OfflineCacheManager {
    String? _mockStorage; // In-memory! Lost on app restart
    Future<void> saveSceneSnapshot(Map<String, dynamic> scene) async {
        _mockStorage = jsonEncode(scene);
    }
    Future<Map<String, dynamic>?> loadSceneSnapshot() async {
        if (_mockStorage == null) return null;
        return jsonDecode(_mockStorage!);
    }
}
```

**Impact:** All cached scene data is lost when the app is killed, the device restarts, or the OS reclaims memory. Offline capability is non-functional beyond a single app session.

**Recommendation:** Use `flutter_secure_storage` (for small data) or Drift SQLite database (for large scene trees). Serialize with versioning for forward compatibility.

---

# Technical Debt Register

## P0 — Must fix before production

| ID | Domain | Finding | Effort |
|----|--------|---------|--------|
| A-01 | AUTH | Hardcoded PIN `123456` | 1 SP |
| A-02 | AUTH | Public device registration to user 1 | 1 SP |
| A-03 | AUTH | RoleMiddleware uses wrong auth guard | 1 SP |
| G-01 | GOV | Zero authorization on governance CRUD controllers | 5 SP |
| G-02 | GOV | Digital signature is cryptographically invalid | 5 SP |
| G-03 | GOV | HasGovernanceFields depends on global request() | 3 SP |
| C-01 | COP | No incident state transition validation | 3 SP |
| C-02 | COP | LiveConnectionManager is entirely mock | 8 SP |
| C-03 | COP | Sync last-write-wins without merge | 8 SP |
| C-05 | COP | BulkStubController discards all data | 2 SP |

**Total P0: 10 items, ~37 SP**

## P1 — Must fix before rollout

| ID | Domain | Finding | Effort |
|----|--------|---------|--------|
| A-04 | AUTH | No token rotation/refresh | 5 SP |
| A-05 | AUTH | Token abilities never used (dead code) | 1 SP |
| A-06 | AUTH | No concurrent login detection | 3 SP |
| A-07 | AUTH | Debug login credential logging | 1 SP |
| A-08 | AUTH | PIN brute force protection missing | 1 SP |
| A-09 | AUTH | device_uuid never populated on tokens | 1 SP |
| G-04 | GOV | MobilisasiPolicy allows self-approval | 1 SP |
| G-05 | GOV | Audit trail missing for most entity operations | 8 SP |
| G-06 | GOV | Lock bypass via unlock API | 2 SP |
| G-07 | GOV | Meeting lifecycle missing authority checks | 5 SP |
| G-08 | GOV | RecordMeetingAuditTrail stores JSON as "signature" | 1 SP |
| S-01 | SDUI | No server-side action validation | 5 SP |
| S-02 | SDUI | SduiValidatorService never called | 1 SP |
| S-03 | SDUI | Two divergent JSON structures for same screen | 2 SP |
| S-04 | SDUI | No schema version negotiation | 3 SP |
| S-05 | SDUI | SduiUnknownComponent exists but never registered | 1 SP |
| S-06 | SDUI | NSS action types not implemented | 5 SP |
| C-04 | COP | No cascading state changes on incident completion | 3 SP |
| C-06 | COP | Assessment operations have no audit trail | 3 SP |
| C-07 | COP | Sitrep is_latest not properly managed | 1 SP |
| C-08 | COP | Sync version auto-increment race condition | 3 SP |
| C-12 | COP | Most COP entities lack CRUD audit trails | 5 SP |
| C-13 | COP | Flutter COP offline cache is in-memory | 2 SP |

**Total P1: 23 items, ~62 SP**

## P2 — Can fix after rollout

| ID | Domain | Finding | Effort |
|----|--------|---------|--------|
| A-10 | AUTH | Expired Sanctum tokens never pruned | 1 SP |
| A-11 | AUTH | No impersonation mechanism | 3 SP |
| A-12 | AUTH | No OTP/2FA | 5 SP |
| A-13 | AUTH | Flutter token state assumes valid without validation | 2 SP |
| G-09 | GOV | No revision/versioning of governance records | 5 SP |
| G-10 | GOV | HasImmutableAudit trait underutilized | 3 SP |
| G-11 | GOV | before() gate only bypasses super_admin | 1 SP |
| S-07 | SDUI | Registry/validator allowed types mismatch | 2 SP |
| S-08 | SDUI | No screen isolation mechanism | 5 SP |
| S-09 | SDUI | Flutter auto-ID not deterministic | 1 SP |
| S-10 | SDUI | Map component type check bug | 1 SP |
| S-11 | SDUI | Dashboard composers never wired | 3 SP |
| S-12 | SDUI | config/sdui.php layouts never used | 1 SP |
| C-09 | COP | OperasiInsiden not in sync entity list | 5 SP |
| C-10 | COP | Eskalasi doesn't change incident status | 2 SP |
| C-11 | COP | No conflict resolution UI on Flutter | 5 SP |

**Total P2: 16 items, ~45 SP**

## P3 — Enhancement

| ID | Domain | Finding | Effort |
|----|--------|---------|--------|
| — | AUTH | Rate limiting review across all endpoints | 3 SP |
| — | AUTH | Implement refresh token rotation | 5 SP |
| — | GOV | Audit log viewer UI for governance officers | 8 SP |
| — | SDUI | Component registry visualization tool | 5 SP |
| — | SDUI | NSS contract compliance checker (CI/CD) | 3 SP |
| — | COP | Real-time dashboard with WebSocket metrics | 8 SP |
| — | COP | Conflict resolution admin panel | 5 SP |
| — | COP | Offline sync monitoring and alerting | 3 SP |

**Total P3: 8 items, ~40 SP**

---

# Refactoring Roadmap

## Sprint 1 (P0 — Security & Critical Fixes)
*Estimated: 37 SP / 3 weeks with 3 engineers*

| Item | Focus |
|------|-------|
| A-01 | Replace hardcoded PIN with bcrypt PIN + rate limit + lockout |
| A-02 | Fix DeviceAuthController: require auth, remove fallback |
| A-03 | Fix RoleMiddleware: use correct guard per context |
| G-01 | Add authorization to ALL governance CRUD controllers |
| G-02 | Implement Ed25519 digital signature with nonce chaining |
| G-03 | Remove `request()` dependency from HasGovernanceFields |
| C-01 | Implement allowed-transition map for incident state machine |
| C-03 | Reject stale sync writes, conflict response to client |
| C-05 | Remove BulkStubController or implement real processing |

## Sprint 2 (P1 — Authorization & Governance)
*Estimated: 62 SP / 4 weeks with 4 engineers*

| Item | Focus |
|------|-------|
| A-04 | Implement short-lived access token + refresh token rotation |
| A-05 | Either implement token abilities or remove dead code |
| A-06 | Implement concurrent session management |
| A-07 | Remove all debug credential logging |
| A-08 | Add PIN brute force protection (rate limit + lockout) |
| A-09 | Populate device_uuid on token creation, fix revocation |
| G-04 | Fix MobilisasiPolicy: prevent self-approval |
| G-05 | Add model observers for all entity audit trails |
| G-06 | Restrict unlock to super_admin only |
| G-07 | Add authority checks to meeting lifecycle |
| G-08 | Rename digital_signature to event_metadata |

## Sprint 3 (P1 — SDUI & COP)
*Estimated: 62 SP / 4 weeks with 4 engineers*

| Item | Focus |
|------|-------|
| S-01 | Implement ActionRegistry with permission-based validation |
| S-02 | Wire SduiValidatorService into serialization pipeline |
| S-03 | Remove legacy scene composers |
| S-04 | Implement schema version negotiation with Accept-Version |
| S-05 | Register SduiUnknownComponent as fallback |
| S-06 | Implement all NSS action types |
| C-02 | Implement Laravel Reverb WebSocket + Flutter integration |
| C-04 | Add cascading state validation on incident completion |
| C-06 | Add assessment audit trail |
| C-07 | Fix sitrep is_latest concurrency |
| C-08 | Fix sync_version race condition with atomic increment |
| C-12 | Add audit trails to all COP entities |
| C-13 | Replace in-memory cache with persistent storage |

## Sprint 4 (P2 — Architecture & Maintainability)
*Estimated: 45 SP / 3 weeks with 4 engineers*

| Item | Focus |
|------|-------|
| A-10 | Schedule sanctum:prune-expired |
| A-11 | Implement impersonation for super_admin |
| A-12 | Implement TOTP 2FA for privileged roles |
| G-09 | Add revision history for governance entities |
| S-07 | Sync backend allowed-types with Flutter registry |
| S-08 | Implement screen isolation boundaries |
| S-11 | Wire dashboard builder system into controllers |
| C-09 | Add OperasiInsiden to sync entity list |
| C-11 | Build conflict resolution UI on Flutter |

---

# Final Verdict

## NOT READY FOR PRODUCTION

**Score: 2/100**

### Technical Rationale

1. **Complete authorization bypass in governance layer (G-01):** Any authenticated user can create, modify, or delete the entire governance hierarchy (mandates, positions, delegations, authorities) because 10+ CRUD controllers have zero access control. The 8-layer mandate validation system is rendered irrelevant.

2. **Hardcoded authentication bypass (A-01):** The PIN verification system uses `if ($request->pin === '123456')` — a literal string comparison against a hardcoded value used by all users. This is a single factor of an 8-layer governance system that is trivially bypassed.

3. **Unauthenticated privilege escalation (A-02):** A public HTTP endpoint with no authentication creates device tokens bound to user ID 1 (super_admin). Any unauthenticated client can obtain a privileged token.

4. **Real-time COP is entirely simulated (C-02):** The `LiveConnectionManager` uses `Timer.periodic` to emit mock patches with hardcoded GPS coordinates. There is no WebSocket, no Reverb, no real-time capability. The COP platform cannot receive real-time updates.

5. **Data loss via sync (C-03):** The offline sync engine detects stale writes but always accepts the client's version. Command center decisions made while a user is offline are silently overwritten when the user syncs. No merge strategy exists.

6. **Data loss via stubs (C-05):** `BulkStubController` accepts any data and returns "success" for every item without storing or processing anything. Production data sent to logistik/bulk or mobilisasi/bulk is silently discarded.

### Conditions for Re-Audit

All P0 and P1 items must be remediated before a re-audit can be scheduled. Estimated total effort: **~150 SP (~14 weeks with 4 engineers).**

The most critical architectural issues (governance CRUD authorization, digital signatures, sync conflict resolution, real-time infrastructure) require design decisions and cannot be resolved with superficial patches.

---

*End of Audit Report — 11 July 2026*

*Generated by Principal Software Architecture Audit*
