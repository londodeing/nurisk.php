# Device & Token Lifecycle Audit

**Date:** 2026-06-20  
**Phase:** 14.2  
**Status:** Implementation complete

---

## 1. Current Architecture

### Sanctum Token System (API Auth)

| Property | Value |
|---|---|
| Provider | `Laravel\Sanctum\SanctumServiceProvider` |
| Model | `Laravel\Sanctum\PersonalAccessToken` (table: `personal_access_tokens`) |
| Expiration | 30 days (config: `SANCTUM_TOKEN_EXPIRATION_DAYS`) |
| Prefix | `nurisk_` (config: `SANCTUM_TOKEN_PREFIX`) |
| Last Used | Tracked per-request (1 UPDATE per request) |
| Tokenable | `App\Models\AuthUser` via `HasApiTokens` trait |
| **Device UUID** | **NEW** — `device_uuid` column added (`varchar(64)`, nullable, indexed) |

### Mobile Device System

| Property | Value |
|---|---|
| Model | `App\Models\MobileDevice` (table: `mobile_devices`) |
| PK | `id_device` (auto-increment) |
| Device UUID | `uuid_device` (unique string, auto-generated) |
| User FK | `id_pengguna` → `auth_users.id_pengguna` (cascade) |
| Status | `active`, `revoked`, `inactive` |
| Token | `device_token` (SHA-256 of 60-char random string) |
| Token Expiry | `token_expires_at` (timestamp) |
| Trust Score | Integer (default 100) |

### Relationship

- `AuthUser` → `mobileDevices()`: `HasMany` (NEW — added in Phase 14.2)
- `MobileDevice` → `pengguna()`: `BelongsTo` (pre-existing)
- `PersonalAccessToken` → `device_uuid`: string attribute (NEW — added in Phase 14.2)

---

## 2. New Endpoints

| Method | Route | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/devices` | Sanctum + Role | List all devices for authenticated user, ordered by last activity |
| `DELETE` | `/api/v1/devices/{uuid}` | Sanctum + Role | Revoke device + delete all associated Sanctum tokens |
| `POST` | `/api/v1/devices/logout-all` | Sanctum + Role | Revoke ALL devices + delete ALL Sanctum tokens for this user |

All routes require `auth:sanctum` and `role:super_admin,pwnu,pcnu,relawan`.

### Controller: `App\Http\Controllers\Api\Device\DeviceApiController`

**`index()`** — Returns device list sorted by `last_sync_at` (desc), falling back to `dibuat_pada`. Each entry includes:
- `uuid_device`, `platform`, `app_version`, `status`
- `last_sync_at`, `trust_score`, `token_expires_at`, `dibuat_pada`

**`destroy($uuid)`** — Validates device belongs to current user, deletes Sanctum tokens with matching `device_uuid`, sets device `status = 'revoked'`. Returns 404 if device not found.

**`logoutAll()`** — Deletes ALL Sanctum tokens for user, sets ALL devices to `status = 'revoked'`. Returns counts in `meta`.

### Migration: `add_device_uuid_to_personal_access_tokens`

```sql
ALTER TABLE personal_access_tokens
    ADD device_uuid VARCHAR(64) NULL AFTER tokenable_id;
CREATE INDEX personal_access_tokens_device_uuid_index
    ON personal_access_tokens (device_uuid);
```

---

## 3. Token-to-Device Association

### Current Flow

1. Sanctum token is created via `AuthUser::createToken()` (typically from web SPA login)
2. `device_uuid` is NOT set at creation time (no login API endpoint exists)
3. When mobile client calls `POST /api/v1/sync`, the `device_uuid` is provided in the request payload
4. The sync controller finds the device by `uuid_device` but does NOT update the Sanctum token's `device_uuid`

### Future Enhancement

To fully populate `device_uuid` on Sanctum tokens:

1. **Option A: Update in Sync Controller** — After validating the device, update all tokens for the user that have `device_uuid = NULL` to set `device_uuid = $device->uuid_device`. This batch-update would add 1 UPDATE query per sync request but provides backward association.

2. **Option B: Login Endpoint** — Create a `POST /api/v1/auth/login` endpoint that accepts `device_uuid` and sets it on the token at creation time:
   ```php
   $token = $user->createToken('mobile-token', ['*']);
   $token->accessToken->device_uuid = $request->device_uuid;
   $token->accessToken->save();
   ```

3. **Option C: Token Refresh** — Enhance the existing `POST /api/v1/device/refresh-token` endpoint to also issue a Sanctum token with `device_uuid` set.

**Recommendation:** Option A is simplest and backward-compatible. Option B is cleaner but requires a login API endpoint which currently does not exist.

---

## 4. Security Considerations

| Concern | Mitigation |
|---|---|
| Token revocation on device delete | `destroy()` deletes all Sanctum tokens with matching `device_uuid` |
| Token revocation on user deactivation | `AuthUserObserver` already deletes tokens via `$user->tokens()->delete()` |
| Bulk logout (lost/stolen phone) | `logoutAll()` revokes ALL tokens + ALL devices in 2 queries |
| Device UUID spoofing | Each `destroy()` call validates `uuid_device` belongs to the authenticated user |
| Race condition on `logoutAll` | Token is validated before controller runs; response is sent after deletion |
| No login API endpoint | Tokens created via web SPA; device-to-token association requires Option A/B/C above |

---

## 5. Token Lifecycle Summary

```
[Device Register]
    ↓
POST /api/v1/device/refresh-token    → Creates MobileDevice record
    ↓                               → Issues device_token (60-char random)
[User logs in via web SPA]
    ↓
AuthUser::createToken()              → Creates Sanctum token (nurisk_ prefix)
    ↓                                → Stores in personal_access_tokens
[Device syncs]
    ↓
POST /api/v1/sync                    → Associates device_uuid with sync
    ↓                                → (Future: also associate with Sanctum token)
[Admin revokes device]
    ↓
DELETE /api/v1/devices/{uuid}        → Deletes Sanctum tokens for device
    ↓                                → Sets device status = 'revoked'
[User logs out all]
    ↓
POST /api/v1/devices/logout-all      → Deletes ALL Sanctum tokens
                                     → Sets ALL devices = 'revoked'
```

---

## 6. Tests

**File:** `tests/Feature/DeviceApiTest.php` (4 tests, 9 assertions)

| Test | What it verifies |
|---|---|
| `it_lists_devices_for_authenticated_user` | Returns only current user's devices |
| `it_rejects_unauthenticated_access` | 401 without valid token |
| `it_revokes_a_specific_device` | Status changes to 'revoked' on delete |
| `it_logs_out_all_devices` | All devices revoked after logout-all |

```bash
php artisan test tests/Feature/DeviceApiTest.php
```
