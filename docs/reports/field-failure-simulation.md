# Phase 15.3 — Field Failure Simulation Report

## 1. Internet putus 24 jam

| Field | Content |
|---|---|
| Scenario | Device disconnected for 24 hours |
| Description | A field device loses internet connectivity for a full 24-hour period. During this time the operator continues collecting assessments offline (stored in SQLite). After 24 hours the device reconnects and the sync process resumes. The Sanctum token is still valid (30-day expiry). |
| Expected Behaviour | All 24 hours of offline changes sync successfully. Conflict detection runs on any overlapping records. Both server and client converge to a consistent state via LWW. No data loss. |
| Actual Behaviour Analysis | **Cursor divergence:** Each entity type tracked via `SyncCursor` has `cursor_value` set to the server's `updated_at` timestamp at last sync. After 24h offline the client's cursors lag behind. On reconnect the `POST /api/sync` handler in `app/Http/Controllers/Api/SyncController.php` receives the stale cursors and runs `SyncService::pullChanges()` which queries `WHERE updated_at > clientCursor`. The server returns up to 24 changes per entity (bounded by `SyncService::PER_PAGE = 24`). If more than 24 records changed for any entity, the client receives cursor `serverCursor` pointing to the 24th record; a second sync round is needed. **Tombstones:** The `SyncTombstone` table (`sync_tombstones`) is consulted. Any records deleted server-side in the last 24h are returned. The client iterates through tombstones in `SyncService::applyTombstones()` and removes them from the local SQLite store. **Conflict detection:** For each incoming server change, the client compares `sync_version`. If the local version is higher, the server record is discarded (LWW favours the higher version). If equal, the later `updated_at` wins. The conflict table `sync_conflicts` is never written to because LWW resolves deterministically. **Batch size:** The `in:24` validation on the `per_page` field ensures exactly 24 queries are processed per request. If offline changes exceed 24 for a single entity, additional sync rounds are triggered automatically by the client pagination logic in `SyncService::syncBatch()`. |
| Risk | P2 — Transient delay on reconnection if many changes accumulated. No data loss. |
| Mitigation | None required for 24h. Ensure `SyncService::PER_PAGE` is adequate (24 is reasonable). Monitor sync duration alerting at >30s per sync round. |
| Detection | CloudWatch metric `SyncDuration` spikes for reconnect devices. Grafana dashboard shows `DeviceOfflineHours` widget. |

## 2. Internet putus 7 hari

| Field | Content |
|---|---|
| Scenario | Device disconnected for 7 days |
| Description | A field device remains offline for a full week. Large backlog of local changes accumulated. Significant server-side changes and tombstones exist. Token still valid (30-day expiry). |
| Expected Behaviour | All changes sync after reconnection. Multiple sync rounds occur due to the 24-query batch limit. No data loss. LWW resolves all conflicts. |
| Actual Behaviour Analysis | **Backlog volume:** With 7 days of offline data, a single device could accumulate hundreds of assessments. Each `POST /api/sync` request is limited to 24 changes. `SyncService::syncBatch()` loops until `clientCursor === serverCursor`. This means 10–20+ sequential HTTP requests per device. **Cursor fetch cost:** Each sync round re-queries `SyncCursor` from `sync_cursors` table. The `WHERE entity_type = ? AND device_uuid = ?` query has a composite index `idx_sync_cursors_entity_device`. No degradation. **Tombstone volume:** `sync_tombstones` may have thousands of rows. `SyncService::pullTombstones()` queries `WHERE entity_type = ? AND deleted_at > ?` which uses `idx_sync_tombstones_entity_deleted`. At scale this is fine. **Memory pressure:** `SyncService::applyChanges()` hydrates Eloquent models for each of the 24 changes. With 10 rounds this is 240 models — no memory issue. **Conflict risk:** If two devices both modified the same record offline, and both reconnected within the same window, the higher `sync_version` wins. The version is derived from `microtime(true) * 1000` in `app/Models/Traits/HasSyncVersion.php`. Since both devices generate versions based on their own clocks, drift is possible (see scenarios 3 & 4). |
| Risk | P1 — High number of sequential sync rounds increases total sync time. If many devices reconnect simultaneously, server load spikes. |
| Mitigation | Implement staggered reconnect (jitter) on the client: `delay = random(1, 60) seconds` before first sync after connectivity restore. Consider increasing `per_page` limit dynamically or on server side for large backlogs. |
| Detection | CloudWatch `SyncRoundsPerDevice` metric. Alarm when >50 rounds per device. Server-side `SyncController` logs `sync_rounds` to `sync_log` table. |

## 3. Device clock maju 3 hari

| Field | Content |
|---|---|
| Scenario | Device system clock is 3 days ahead |
| Description | The device's system clock has been manually set or drifted 3 days into the future. This affects all client-generated timestamps including `sync_version`, `waktu_mulai`, `waktu_assesment`, and the client's `cursor_value` sent to the server. Sanctum validation uses server time, so the token remains valid. |
| Expected Behaviour | The server should reject or correct future-dated timestamps where possible. LWW comparison should not break. Cursor negotiation should handle the time skew. |
| Actual Behaviour Analysis | **Token validation:** `EnsureFrontendRequestsAreStateful` and `Sanctum\Middleware\CheckAbilities` check `$token->expires_at` against `now()` (server time). Since the token was issued 28 days ago (server time) and expires in 2 days, it is still valid. The client clock does not factor into token validation. **`sync_version` collision:** `HasSyncVersion::bootHasSyncVersion()` sets `$model->sync_version = (int) (microtime(true) * 1000)` on create. A clock-ahead device generates sync versions that are 259,200,000 ms ahead of server versions. When LWW compares two versions of the same record, the device's version always wins — even if the server has a legitimate newer change. This effectively gives the clock-ahead device priority over the server. **Cursor negotiation:** In `SyncService::pullChanges()`, the comparison is `WHERE updated_at > ?` (cursor parameter). The client sends `cursor_value` which was stored as `updated_at` of the last synced record. If the client stored a future `updated_at` as its cursor, the server sees `WHERE updated_at > 2026-06-23 12:00:00` (future date). This would return zero rows because no server records have a future `updated_at`. The client would believe it has no new server changes. **`waktu_mulai`/`waktu_assesment`:** These are stored as-is from the client. A `waktu_assesment` of +3 days could cause reporting queries to produce records in the future. The `created_at` timestamps on `assessments` table are set by the server when `POST /api/sync` is processed — those are correct. But application-level date fields are not validated for future dates in `app/Http/Requests/SyncRequest.php`. |
| Risk | P1 — Data integrity issue. Future-dated records cause reporting anomalies. The clock-ahead device silently dominates LWW conflicts. |
| Mitigation | Add server-side validation to reject future-dated `waktu_mulai` / `waktu_assesment` (tolerance of 5 minutes). Normalize `sync_version` on the server during write-back: overwrite client `sync_version` with `(int) (microtime(true) * 1000)` on the server after accepting a change. Add `NTP_REQUIRED` flag in `config/sync.php` that the client checks at startup. |
| Detection | Grafana alert on `max(waktu_assesment) > now()` in `assessments` table. Sync log analysis for `version_gap > 86400000` (1 day in ms). |

## 4. Device clock mundur 3 hari

| Field | Content |
|---|---|
| Scenario | Device system clock is 3 days behind |
| Description | The device clock is set 3 days in the past. Client-generated timestamps are old. Token validation passes (server time). Cursor negotiation and conflict detection are affected. |
| Expected Behaviour | The server should handle old timestamps gracefully. Conflicts should still be detectable. Cursors should not cause excessive data transfer. |
| Actual Behaviour Analysis | **Cursor negotiation:** The client sends `cursor_value` = `2026-06-17 12:00:00` (3 days ago). Server query: `WHERE updated_at > 2026-06-17 12:00:00`. This matches all records changed in the last 3 days — which is correct. However, if the client originally synced at a real offset of 3 days, the cursor stored before going offline was correct. After the clock change, the client _thinks_ its cursor is `2026-06-17`, but the actual last-sync point was `2026-06-20`. The client will re-request records from `2026-06-17` onward — i.e., the last 3 days of data. This is wasteful but not destructive. **Conflict detection:** The client's `sync_version` is 259,200,000 ms _behind_. LWW on the client side will always prefer server versions (which are higher). The client never wins a conflict. If the client modified a record offline, the version stored locally is the old version. When the server version arrives with a higher version, the client overwrites its own local change. **Data loss:** The operator's offline work is silently overwritten on sync. The local changes are discarded because LWW favours the higher version. This is the inverse problem of scenario 3 and arguably worse because legitimate field data is lost. **`created_at` / `updated_at`:** These server-side timestamps are never affected. The server sets them during `SyncService::applyChanges()`. |
| Risk | P1 — Silent data loss. Operator's offline changes are discarded by LWW because the device's sync_version is too low. |
| Mitigation | Same as scenario 3: server-side normalization of `sync_version`. Add a `clock_drift` check on the client at sync time: if `abs(device_time - server_time) > 300` seconds, warn the operator and refuse to sync. Use `X-Timestamp` header (server time) returned in sync response to let clients calibrate. |
| Detection | Sync log analysis for `version_gap < -86400000`. Compare `sync_version` of incoming changes to server-generated versions on the same records. Dashboard metric `VersionSkew`. |

## 5. Reinstall aplikasi

| Field | Content |
|---|---|
| Scenario | Fresh app reinstall on the same device |
| Description | The operator reinstalls the app. SQLite database is wiped. All cached cursors are lost. The client sends `cursors=0` for all entities. The bootstrap endpoint returns the full snapshot. The previous MobileDevice UUID is reused (device ID stored in Android KeyStore / iOS Keychain which survives reinstall). |
| Expected Behaviour | Bootstrap returns full dataset. Client rebuilds local store from scratch. Conflicts are detected if server-side records match the device's prior uploads (same UUID). No duplicate records. |
| Actual Behaviour Analysis | **Bootstrap:** `GET /api/bootstrap` hits `BootstrapController::index()`. With client sending `last_sync_at = null` or `cursors = 0`, the controller calls `BootstrapService::fullSync()`. This iterates over all entities: `Assessments`, `EvacuationRoutes`, `Shelters`, `SyncTombstone`. Each entity returns all records (not filtered by cursor). With `per_page = 15` (set in `BootstrapService::PER_PAGE`), multiple rounds are required. **Duplicate avoidance:** Each record has a `uuid` column. `HasUuid` trait generates `$model->uuid` on `creating`. The client upserts by UUID in `SyncService::applyChanges()`. If the device UUID is the same, and the server has records from a previous sync with matching UUIDs, the upsert matches on `uuid` and updates, not inserts. No duplicates. **Conflicts:** Since the previous records have the same UUIDs, the upsert updates existing rows. `sync_version` comparison in the upsert logic in `app/Models/Traits/HasSyncVersion.php` checks if incoming version > existing version before applying. Since both sides have the same version (the version was originally generated by this device), no conflict — it's a no-op update. **Token:** The Sanctum token is stored server-side and the client re-authenticates (token stored in secure storage may survive reinstall if KeyStore/Keychain backup is enabled; otherwise a fresh login is needed). **Tombstones:** Bootstrap includes all tombstones from `sync_tombstones`. The client removes any records in the tombstone list. This is correct. |
| Risk | P2 — Bootstrap is bandwidth-heavy. Token re-authentication may be needed if secure storage is wiped. No data integrity risk. |
| Mitigation | Ensure bootstrap endpoint is cached on a CDN or Redis for read-only entities. Use conditional bootstrapping: if the client has any cursors > 0, skip full bootstrap. |
| Detection | CloudWatch `BootstrapSize` metric. Log line `reinstall_detected` in `bootstrap_log` when full sync is served. |

## 6. Ganti device

| Field | Content |
|---|---|
| Scenario | Operator switches to a new device |
| Description | The operator logs into the app on a new phone. The new device has a different UUID. `POST /api/login` succeeds with valid credentials. `MobileDevice::firstOrCreate(['uuid' => $deviceUuid])` creates a fresh record with `trust_score = 0`. Bootstrap sends all data. |
| Expected Behaviour | New device receives all data. The old device remains active unless explicitly revoked. No conflicts (new UUID). The operator may have twin devices. |
| Actual Behaviour Analysis | **MobileDevice creation:** `app/Http/Controllers/Api/AuthController.php::login()` calls `MobileDevice::firstOrCreate(...)`. The `trust_score` defaults to `0` in the migration `create_mobile_devices_table`. The new device starts untrusted. Feature flags or access controls gating on `trust_score > MINIMUM_TRUST_SCORE` in `config/mobile.php` may restrict certain operations (e.g., PDF generation, evacuation route editing). **Bootstrap:** Full bootstrap as per scenario 5. All assessments, routes, shelters are sent. The new device now has a complete copy. **Conflicts:** None. The new UUID has no prior records. `sync_version` on the device is compared against server records — all server records have higher versions, so the client accepts all. **Old device:** The old device remains in `mobile_devices` with `active = true`. If its token has not expired, it can still sync. The app does not automatically revoke old devices on new device registration. `AuthController::login()` does not call `MobileDevice::where('user_id', $user->id)->update(['active' => false])`. **Trust score:** The new device starts at `trust_score = 0`. Old device retains its accumulated `trust_score`. If access controls rely on trust score, the new device may have degraded functionality until trust is earned. |
| Risk | P2 — Old device remains active (security concern). New device starts untrusted (operational friction). |
| Mitigation | Add optional `revoke_other_devices` flag to login flow. Implement a trust score seeding mechanism: when a user registers a new device, seed `trust_score` from the user's aggregate trust level. |
| Detection | `mobile_devices` table audit: `SELECT user_id, COUNT(*) FROM mobile_devices WHERE active = 1 GROUP BY user_id`. Security alert on >2 active devices per user. |

## 7. Token expired

| Field | Content |
|---|---|
| Scenario | Sanctum API token has expired |
| Description | The device has been offline long enough that the Sanctum token passed its `expires_at` (30 days from issuance). The next sync request hits the API. The `EnsureFrontendRequestsAreStateful` middleware or `CheckAbilities` middleware checks `$token->expires_at < now()` and returns 401. |
| Expected Behaviour | The client catches the 401, redirects to login, refreshes the token via re-authentication, then retries the sync. Queued sync requests remain in the local queue until retry. |
| Actual Behaviour Analysis | **Middleware chain:** `app/Http/Kernel.php` registers `EnsureFrontendRequestsAreStateful` in the `api` middleware group. This calls `Sanctum::authenticateAccessTokens()` which checks `$token->expires_at && $token->expires_at->isPast()`. On expiry, it throws `AuthenticationException` which Laravel converts to a `401` JSON response. **Client handling:** The sync worker in `app/Services/Sync/SyncWorker.php` catches `Illuminate\Auth\AuthenticationException` or checks `$response->status() === 401` in the response handler. The retry logic: `$this->retryAfter = 300` (5 minutes). The sync job is pushed back to the local SQLite queue (`jobs` table) with `available_at = now() + 300`. **Queue persistence:** The local SQLite queue is not cleared. The `sync` job remains in `jobs` with `attempts` incremented. The `SyncWorker` uses `$this->release(300)` to re-queue. **Data safety:** All locally collected data remains in the SQLite `assessments` table. No data is lost. Only the sync cursor is stale. **Re-authentication:** The user sees a login prompt (if the app UI handles 401) or the client silently re-authenticates using stored credentials (if `remember_me` token or refresh token is stored). The app does _not_ have a refresh token mechanism by design — Sanctum does not support refresh tokens out of the box. The operator must re-enter credentials. |
| Risk | P1 — Operator may not notice the 401 and data stays unsynced. If re-authentication requires connectivity but the token expired _because_ the device was offline, this is a Catch-22: the device is offline, the token expired, and the user cannot re-authenticate until online, but once online the token is expired and sync fails immediately. |
| Mitigation | Implement a grace period: before the token expires, the device should pre-emptively re-authenticate while still online. Store hashed password in Android KeyStore / iOS Keychain for silent re-auth (with biometric gate). Set token expiry to a value greater than any expected offline window (already 30 days, but consider 60 or 90). Add `X-Token-Expires-In` header to sync responses so the client can warn the operator when the token is nearing expiry. |
| Detection | CloudWatch `Sync401Error` count. Log line `token_expired` in `sync_log`. Client-side `TokenExpiryWarning` shown in UI when token is within 48h of expiry. |

## 8. Queue backlog 10.000 job

| Field | Content |
|---|---|
| Scenario | 10,000 PDF generation jobs accumulated in the queue |
| Description | A burst of PDF generation requests (e.g., bulk report generation after a disaster event) has filled the `jobs` table with 10,000 `pdf_generation` jobs. The queue worker processes sequentially (or with `--queue=pdf-generation --sleep=3`). |
| Expected Behaviour | PDF generation is delayed but not lost. Sync operations continue unaffected because they are synchronous (not queued). The queue backlog is processed eventually. |
| Actual Behaviour Analysis | **Queue architecture:** The `jobs` table is SQLite. `config/queue.php` sets `'default' => env('QUEUE_CONNECTION', 'sync')` but for production `QUEUE_CONNECTION=database` with SQLite. The `php artisan queue:work --queue=pdf-generation` processes these sequentially. Each job in `app/Jobs/GeneratePdf.php` calls `PdfService::generate()` which loads assessments, renders a Blade view, and streams to `dompdf`. **Processing time:** At ~3s per PDF (dompdf + I/O), 10,000 jobs = ~8.3 hours. **Sync is NOT queued:** `POST /api/sync` is handled synchronously in the HTTP request lifecycle. `SyncController::sync()` directly calls `SyncService::pullChanges()` and `SyncService::applyChanges()`. It does not dispatch a job. Therefore sync requests are unaffected by the PDF queue backlog. **Database contention:** Both sync and queue workers use the same SQLite database (if SQLite is the primary DB). SQLite's write lock (`WAL` mode helps but `BEGIN IMMEDIATE` still serializes writes). A sync request and a queue worker may contend for the write lock. `SELECT` queries (sync pulls) are not blocked by WAL writers but `INSERT`/`UPDATE` may be. **Memory:** Each `GeneratePdf` job loads models. With 10,000 jobs, the queue worker memory may grow if jobs leak. Configure `--memory=128` in the supervisor. **Priority:** PDF jobs are on the `pdf-generation` queue. Sync is not a queue at all. The queue worker only pops jobs from its specified queue. No starvation. |
| Risk | P2 — PDF delivery delayed (~8h). Possible database write contention during sync if SQLite is the primary DB. |
| Mitigation | Use separate SQLite databases for queue (`database/queue.sqlite`) and main app. Or switch to Redis for queue. Increase `--sleep=1` and add more workers. Add a priority queue: `pdf-generation` vs `critical`. Consider streaming PDF generation to S3 with signed URLs instead of queuing. |
| Detection | CloudWatch `QueueSize` metric. Alarm at >1000 jobs. Grafana `PdfGenerationLatency` — time between job dispatch and completion. Track in `failed_jobs` table for failures. |

---

## Risk Matrix

| Risk | Scenario | Impact | Likelihood | Estimated Effort to Fix | Priority |
|---|---|---|---|---|---|
| P1 | Device clock maju 3 hari | Data integrity: future-dated records, client dominates LWW | Medium | 2 days — Add server-side `sync_version` normalization + clock drift check | **High** |
| P1 | Device clock mundur 3 hari | Silent data loss: operator's offline changes discarded | Medium | 2 days — Same fix as clock maju, plus `X-Timestamp` header calibration | **High** |
| P1 | Token expired while offline | Catch-22: cannot re-auth without connectivity, cannot sync without token | Medium | 3 days — Implement grace period + silent re-auth with KeyStore credentials | **High** |
| P1 | Internet putus 7 hari | High sync rounds, server load spike on reconnect | High | 1 day — Staggered reconnect jitter + dynamic per_page | **High** |
| P2 | Internet putus 24 jam | Transient delay on reconnect | High | 0 days — Acceptable within design | **Low** |
| P2 | Reinstall aplikasi | Bandwidth-heavy bootstrap | Low | 1 day — CDN caching for bootstrap, conditional full sync | **Medium** |
| P2 | Ganti device | Old device remains active, new device untrusted | Low | 1 day — Optional `revoke_other_devices` + trust score seeding | **Medium** |
| P2 | Queue backlog 10.000 | PDF delivery delayed ~8h, DB write contention | Low | 2 days — Separate queue DB or Redis, add workers | **Medium** |
