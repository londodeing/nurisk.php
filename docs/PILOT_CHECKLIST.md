# NURISK Pilot Readiness Checklist

**Project:** NURISK — Sistem Informasi Penanggulangan Bencana NU
**Target Environment:** Limited Pilot (verified PWNU/PCNU users, 1–2 regions)
**Last Updated:** 2026-06-20

---

## Instructions

Tick each item only after completing the described action **and** verifying via the specified method. All items must be ✅ before declaring pilot-ready.

---

## 1. Security

- [ ] **1.1 Revoke all tokens for a deactivated user** — Deactivate an `AuthUser` (set `status_akun` to non-aktif) and confirm all Sanctum tokens are revoked.  
  *Verify:* `php artisan tinker` → `$user->tokens()->count()` returns `0`.

- [ ] **1.2 Verify role middleware enforcement** — Hit a protected API endpoint with a token whose role lacks permission (e.g., `relawan` hitting `POST /api/operasi/posaju`).  
  *Verify:* Response is `403 Forbidden` with role-denied message.

- [ ] **1.3 Verify scope (wilayah) isolation** — Authenticate as a PCNU user with `default_scope_id = X` and attempt to read data belonging to `default_scope_id = Y`.  
  *Verify:* Response returns empty set or `403 Forbidden`; no data from scope Y leaks.

- [ ] **1.4 XSS injection test** — Submit payload `<script>alert('xss')</script>` in a text field (e.g., assessment `deskripsi`, insiden `nama`). Re-render in API response.  
  *Verify:* Payload is HTML-escaped (e.g., `&lt;script&gt;`) in JSON output; raw script tag is absent.

- [ ] **1.5 SQL injection test** — Submit `' OR 1=1 --` in a numeric parameter like `id_insiden`.  
  *Verify:* No data leak or SQL error in response; query bound safely via Eloquent/query builder.

- [ ] **1.6 Mass-assignment protection** — Attempt to set `id_peran`, `status_akun`, or `default_scope_id` via a `POST`/`PUT` JSON body on a user-accessible endpoint.  
  *Verify:* The field is silently ignored or rejected (`400`/`422`); the record is created with default values.

- [ ] **1.7 Rate-limit enforcement** — Send 11 rapid-fire requests to `POST /login` (limit: 10/min).  
  *Verify:* 11th request returns `429 Too Many Requests` with `Retry-After` header.

- [ ] **1.8 CORS origin validation** — Send a preflight `OPTIONS` request from an unapproved origin header.  
  *Verify:* Response has no `Access-Control-Allow-Origin` matching the unapproved origin; for cookie routes, origin is validated strictly.

- [ ] **1.9 Token prefix verification** — Inspect a freshly issued token string.  
  *Verify:* Token string begins with `nurisk_` prefix as configured in `config/sanctum.php`.

- [ ] **1.10 Check token expiry is honoured** — Wait for or manually set a token past its 30-day expiry and use it to call an authenticated endpoint.  
  *Verify:* Endpoint returns `401 Unauthorized`; expired token is rejected.

---

## 2. Backup

- [ ] **2.1 Database backup script exists and runs** — Execute the backup script (e.g., `php artisan backup:run` or custom shell script) for the SQLite database.  
  *Verify:* Backup file is created in the designated location (e.g., `storage/backups/`) with a timestamp suffix.

- [ ] **2.2 `.env` file backed up** — Copy `.env` to a secure, access-controlled location outside the project root.  
  *Verify:* Backup copy exists with identical SHA-256 hash to the original.

- [ ] **2.3 Storage directory backed up** — Run backup of `storage/app/` (includes PDFs, user uploads).  
  *Verify:* Backup archive contains expected directory structure and file count matches `find storage/app/ -type f | wc -l`.

- [ ] **2.4 Backup schedule configured** — Cron entry or scheduler task is registered for automated daily backup.  
  *Verify:* `php artisan schedule:list` shows the backup task with correct frequency.

- [ ] **2.5 Backup integrity verified** — Run a checksum comparison between original and backup files (DB + storage).  
  *Verify:* `sha256sum` output matches for all backed-up assets; no corruption detected.

- [ ] **2.6 Off-site backup configured** — Encrypted backup is pushed to a secondary location (S3-compatible, rsync remote, etc.).  
  *Verify:* Remote target contains backup files with recent timestamps.

---

## 3. Restore

- [ ] **3.1 Database restoration from backup** — Copy a backup `.sqlite` file to the database path and confirm the application boots.  
  *Verify:* `php artisan migrate:status` shows the correct migration list and `php artisan tinker` can query a known record.

- [ ] **3.2 `.env` restoration from backup** — Replace `.env` with the backed-up version.  
  *Verify:* `php artisan config:cache` succeeds and `config('app.env')` returns the expected value.

- [ ] **3.3 Storage restoration from backup** — Extract backup archive into `storage/app/`.  
  *Verify:* Previously generated PDFs and uploads are accessible via their URLs/storage paths.

- [ ] **3.4 Restore drill timed** — Perform a full restore cycle (DB + .env + storage) against a staging environment.  
  *Verify:* RTO (recovery time objective) is documented and meets SLA target (target: < 5 minutes for SQLite).

- [ ] **3.5 Post-restore functionality check** — After restore, call a sync bootstrap and confirm all expected records are present.  
  *Verify:* `GET /api/v1/sync/bootstrap` returns expected entity count and no orphaned references.

---

## 4. Queue

- [ ] **4.1 Queue worker is running** — Confirm the queue worker process for the `default` connection (database/SQLite) is active.  
  *Verify:* `php artisan queue:monitor --queue=default` shows status `running` or `ps aux | grep 'queue:work'` shows the worker PID.

- [ ] **4.2 Failed jobs table exists** — Confirm the `failed_jobs` table is present.  
  *Verify:* `php artisan queue:failed-table` has been run and `php artisan queue:failed` returns no errors.

- [ ] **4.3 Send a PDF job and verify processing** — Trigger a PDF generation (e.g., sitrep export) that queues a job.  
  *Verify:* `php artisan queue:listen --queue=pdf --once` processes the job; PDF file appears in `storage/app/pdfs/`.

- [ ] **4.4 Retry mechanism works** — Force a job to fail (e.g., by removing the PDF template temporarily), then run the retry command.  
  *Verify:* `php artisan queue:retry --queue=pdf` re-queues the job and it completes successfully after the template is restored.

- [ ] **4.5 Queue backlog monitoring** — Check the number of pending jobs in the `jobs` table.  
  *Verify:* `php artisan queue:size` is within acceptable threshold (< 100 pending under normal load).

- [ ] **4.6 Job timeout configured** — Confirm `--timeout` on queue worker exceeds expected max job duration (e.g., 120s for PDF generation).  
  *Verify:* `php artisan queue:work --timeout=120` is set; no jobs are killed prematurely.

- [ ] **4.7 Max attempts configured** — Verify `tries` or `maxAttempts` is set on PDF jobs so a failing job does not run indefinitely.  
  *Verify:* After exceeding max attempts, the job moves to `failed_jobs` table.

- [ ] **4.8 Queue monitoring alert** — Confirm a monitoring script or Sentry alert fires when failed-job count exceeds threshold.  
  *Verify:* Trigger a test failure; alert is received within 5 minutes.

---

## 5. Monitoring

- [ ] **5.1 Sentry SDK configured and reporting** — Send a test event via `\Sentry\captureMessage('test')`.  
  *Verify:* Event appears in Sentry dashboard with correct environment, release, and tags.

- [ ] **5.2 Health endpoint responds** — Hit `GET /api/health` (or equivalent).  
  *Verify:* Response is `200 OK` with JSON body containing `{"status":"ok","db":"connected","queue":"running"}` (or similar).

- [ ] **5.3 Log rotation is active** — Check that `config/logging.php` channels use daily or size-based rotation for `stack`/`daily`.  
  *Verify:* A rotated log file exists in `storage/logs/` with a date suffix (e.g., `laravel-2026-06-20.log`).

- [ ] **5.4 Disk-space alerting configured** — Verify a monitoring check (Sentry, custom script, or server tool) warns when disk usage exceeds 85%.  
  *Verify:* Fill a test file to trigger threshold; alert is dispatched to the on-call channel.

- [ ] **5.5 Application error log check** — Review `storage/logs/laravel.log` for unexpected errors, warnings, or stack traces.  
  *Verify:* No `ERROR` or `CRITICAL` level entries unrelated to known test operations.

- [ ] **5.6 Query-log verification (if enabled)** — Toggle `DB::enableQueryLog()`, run a sync request, and inspect logged queries.  
  *Verify:* Query log confirms the sync endpoint fires ~24 queries per request (baseline); no N+1 patterns or outlier queries detected.

- [ ] **5.7 Uptime monitoring configured** — External uptime check (e.g., UptimeRobot, Nagios) pings the health endpoint every 5 minutes.  
  *Verify:* Uptime dashboard shows the app as `UP` with < 500 ms response time.

---

## 6. Token Lifecycle

- [ ] **6.1 Token creation returns expected payload** — Call `POST /login` with valid credentials.  
  *Verify:* Response includes `token` (plaintext), `expires_at` (now + 30 days), and `device` object.

- [ ] **6.2 Token expiry is 30 days** — Inspect the `expires_at` field on the newly created Sanctum token.  
  *Verify:* `diffInDays(now(), expires_at)` equals `30`; token row in `personal_access_tokens` has `expires_at` set.

- [ ] **6.3 Token revocation via device** — Hit `DELETE /api/v1/devices/{device}` to revoke all tokens linked to a mobile device.  
  *Verify:* `personal_access_tokens` for that device are deleted; the device cannot sync until re-registration.

- [ ] **6.4 Token-device linking** — Confirm a token created via device registration is tied to a `mobile_devices` record via `device_uuid`.  
  *Verify:* `token->device_uuid` matches `mobile_devices.uuid` in the database.

- [ ] **6.5 Logout-all revokes every token for user** — Call `POST /logout-all` (or equivalent) for a user with multiple active tokens.  
  *Verify:* `AuthUser::find(...)->tokens()->count()` returns `0`; all sessions logged out.

- [ ] **6.6 Trust-score update on token use** — Make a sync request with a valid token, then inspect the associated device's `trust_score`.  
  *Verify:* `mobile_devices.trust_score` increments (or updates based on request frequency/consistency).

- [ ] **6.7 Device registration endpoint works** — Register a new device via `POST /api/v1/devices` with device UUID and name.  
  *Verify:* New `mobile_devices` row inserted; token issued with `device_uuid` linked to that device.

---

## 7. Sync

- [ ] **7.1 Bootstrap endpoint returns initial state** — Call `POST /api/v1/sync/bootstrap` with a valid scope (e.g., `{"scope_type":"pcnu","scope_id":3321}`).  
  *Verify:* Response contains all relevant entities for that scope with correct cursors and version tags.

- [ ] **7.2 Upsert creates a new record** — POST an entity (e.g., assessment) via `POST /api/v1/sync` with action `upsert`.  
  *Verify:* Record is created in the database and a corresponding tombstone entry is NOT generated (it is a new record).

- [ ] **7.3 Upsert updates an existing record** — Send an upsert payload with an existing `uuid` and newer `sync_version`.  
  *Verify:* Existing row is updated; the `sync_version` increments in database.

- [ ] **7.4 Tombstone generated on delete** — Delete an entity (e.g., via API or observer), then query the `sync_tombstones` table.  
  *Verify:* Tombstone row exists with the deleted entity's UUID, type, and `deleted_at` timestamp.

- [ ] **7.5 Conflict detection works** — Send two upserts with the same UUID but different `sync_version` values (one stale).  
  *Verify:* Stale update is rejected (`409 Conflict`) or queued for manual resolution; latest version wins.

- [ ] **7.6 Scope filter enforced in sync** — Authenticate as PCNU scope X, attempt to pull data belonging to scope Y.  
  *Verify:* Response contains only scope-X data; scope-Y records are absent.

- [ ] **7.7 Cursor-based pagination** — Call `POST /api/v1/sync` with a `cursor` parameter (pull direction).  
  *Verify:* Response includes `next_cursor` and limited results; subsequent calls with that cursor return the next batch.

- [ ] **7.8 Sync endpoint query count within baseline** — Enable query logging and execute a full sync cycle (bootstrap + upsert + pull).  
  *Verify:* Total queries per sync request ≤ 24; no unexpected duplicate queries.

---

## 8. Storage

- [ ] **8.1 PDF storage location is correct** — Generate a PDF via queue and confirm it lands in the expected directory.  
  *Verify:* File exists at `storage/app/pdfs/{uuid}.pdf` (or configured path); readable and non-empty.

- [ ] **8.2 Log file growth is under control** — Check `storage/logs/` directory size after a load test.  
  *Verify:* Total log directory size < 500 MB; log rotation is trimming old files as expected.

- [ ] **8.3 SQLite WAL file size monitored** — Check the WAL (`-wal`) and SHM (`-shm`) files for the SQLite database.  
  *Verify:* `ls -lh database/*.sqlite-wal` shows WAL file size < 10 MB under normal operation; checkpoint runs periodically (`PRAGMA wal_checkpoint(TRUNCATE)`).

- [ ] **8.4 Cleanup procedure documented** — Verify a documented procedure exists for purging stale PDFs, old logs, and orphaned uploads.  
  *Verify:* Script or command exists (e.g., `php artisan cleanup:stale-pdfs --days=30`); dry-run mode confirms which files would be deleted.

- [ ] **8.5 Temp directory is clean** — Check `storage/tmp/` or `sys_get_temp_dir()` for orphaned files from PDF generation.  
  *Verify:* No leftover temporary files older than 1 hour.

---

## 9. Incident Response

- [ ] **9.1 Runbook is accessible** — Confirm the incident response runbook is available offline (printed or local PDF copy).  
  *Verify:* Runbook contains contact numbers, escalation steps, service-restart commands, and rollback procedures.

- [ ] **9.2 On-call rotation defined** — At least one primary and one secondary on-call engineer are assigned per shift.  
  *Verify:* Rotation schedule is published in team calendar; current on-call engineer can receive alerts.

- [ ] **9.3 Escalation path documented** — Document the chain: Engineer → Lead → CTO/Project Owner, with expected response SLAs.  
  *Verify:* Each tier has a contact method (phone, WhatsApp, Slack) with < 15 min acknowledgement target.

- [ ] **9.4 Communication plan exists** — Define the channels (e.g., `#nurisk-incidents` Slack channel, SMS blast) for notifying stakeholders during an incident.  
  *Verify:* A test notification was sent to the channel and acknowledged by a team member within 5 minutes.

- [ ] **9.5 Rollback drill performed** — Execute the rollback plan: disable `PILOT_MODE`, revert to the previous stable branch, and verify the app boots.  
  *Verify:* After rollback, health endpoint returns `200`, sync bootstrap returns expected data, and no data loss is detected.

- [ ] **9.6 Post-incident review template exists** — A template for post-mortem documentation is ready (what happened, impact, root cause, action items).  
  *Verify:* Blank template is stored in `docs/incidents/` with sections for timeline, impact, RCA, and follow-up tasks.

---

## Sign-off

| Section | Verified By | Date | Signature |
|---------|-------------|------|-----------|
| 1. Security | | | |
| 2. Backup | | | |
| 3. Restore | | | |
| 4. Queue | | | |
| 5. Monitoring | | | |
| 6. Token Lifecycle | | | |
| 7. Sync | | | |
| 8. Storage | | | |
| 9. Incident Response | | | |

**Final Pilot Decision:** ☐ GO  ☐ NO-GO
**Date:** _____________
**Approved By:** __________________________
