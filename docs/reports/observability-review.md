# Observability Review (Task 13.6)

## Current State

### Logging Configuration (`config/logging.php`)

| Aspect | Detail |
|---|---|
| Default channel | `stack` (resolves to `json` via `LOG_STACK` env) |
| JSON channel | `storage/logs/nurisk.json`, level `debug`, `JsonFormatter` |
| Single channel | `storage/logs/laravel.log`, level `debug` |
| Daily channel | 14-day rotation, level `debug` |
| Additional drivers | Slack (critical+), Papertrail (SyslogUdp), stderr, syslog, errorlog, null |
| Deprecations | Sent to `null` channel by default |

The app uses **structured JSON logging** via the `json` channel with `Monolog\Formatter\JsonFormatter`. The `stack` channel defaults to `json`, so most log output is already structured. A 14-day rotation is available via the `daily` channel but not used by default.

### Correlation ID Implementation (`CorrelationIdMiddleware`)

- Accepts `X-Correlation-ID` or `X-Request-ID` from request headers, falls back to `Str::uuid()`.
- Stores value in `$request->merge(['_correlation_id' => ...])`.
- Returns `X-Correlation-ID` header on the response.

**Gaps:**
1. **Not propagated to log context** — the correlation ID is stored in the request but never injected via `Log::withContext()`. Log entries lack a correlation ID field.
2. **Bug in `X-Request-ID` response** — line 24 checks `$request->has('_request_id')` but the code only sets `_correlation_id`, so the `X-Request-ID` response header branch is dead code.
3. **No outbound propagation** — if the app makes HTTP calls to downstream services, the correlation ID is not forwarded.
4. **Not available in queue jobs** — queued jobs have no access to the original request's correlation ID unless manually passed as job constructor data.

### Queue Monitoring (`config/queue.php`)

- **Driver**: `database` (default), with fallover to `deferred`.
- **No dedicated monitoring**: No Horizon, no Laravel Pulse, no custom queue metrics exporter.
- The health check endpoint (`HealthCheckController::checkQueue`) counts pending and failed jobs from the `jobs` and `failed_jobs` tables, with thresholds: warn at >100 pending, degraded at >10 failed.
- No per-job duration tracking. `SyncAuditLog` exists for sync jobs but is specific to the sync subsystem.

### Health Endpoint (`/health`)

Exposed via `routes/web.php` (web route, no auth). Checks:

| Check | What it tests | Format |
|---|---|---|
| `database` | PDO connection + `SELECT 1` | `ok` / `fail` |
| `cache` | Write + read health check key | `ok` / `fail` |
| `storage` | Write + delete temp file on default disk | `ok` / `fail` |
| `queue` | Count pending/failed jobs | `{status, pending, failed}` |
| `disk` | Storage disk usage percentage | `{status, used_pct}` |
| `migration` | Migration table existence and completeness | `current` / `pending` / `unavailable` / `fail` |
| `sync` | Sync audit log metrics | `{status, last_sync_at, duration_ms, entities_synced, conflicts, total_requests}` |
| `version` | `config('app.version')` | string |
| `git_sha` | `.git/HEAD` ref resolution | string |
| `time` | Current ISO 8601 timestamp | string |

**Gaps:**
- No HTTP-level health (response time, error rate).
- No external dependency health beyond DB/cache/storage.
- Not authenticated — could be used for information gathering.

## Recommendations

### 1. Request Duration Logging Middleware

Create a middleware that logs request method, path, status code, and duration:

```
app/Http/Middleware/RequestDurationMiddleware.php
```

Sample implementation: record `LARAVEL_START` or `microtime(true)` on `request` event, compute elapsed time on `terminate`, log via `Log::info()` with duration in milliseconds and the correlation ID.

Register in `bootstrap/app.php` as a global middleware for both `web` and `api` stacks.

### 2. Slow Query Logging (>100ms)

Enable Laravel's built-in slow query logging in `AppServiceProvider::boot()`:

```php
DB::whenQueryingForLongerThan(100, function (Connection $connection, QueryExecuted $event) {
    Log::warning('Slow query detected', [
        'sql' => $event->sql,
        'bindings' => $event->bindings,
        'time' => $event->time,
        'connection' => $connection->getName(),
    ]);
});
```

Alternatively, set `DB_LOG_QUERIES_WITH_STACK_TRACE=true` in environments where needed.

### 3. Queue Processing Time Logging

Before/after job processing in the job's `handle()` method:

```php
$start = microtime(true);
// ... job logic ...
$duration = (microtime(true) - $start) * 1000;
Log::info('Job processed', [
    'job' => static::class,
    'duration_ms' => round($duration, 2),
]);
```

For a cross-cutting solution, register a custom job middleware or use the `before`/`after` hooks on the `Queue` facade.

### 4. Structured Logging Improvements

- **Add correlation ID to log context** in `CorrelationIdMiddleware`:
  ```php
  Log::withContext(['correlation_id' => $correlationId]);
  ```
- **Add environment and service metadata** to every log entry:
  ```php
  Log::withContext([
      'env' => app()->environment(),
      'service' => config('app.name'),
  ]);
  ```
- Configure the JSON channel to include a `timestamp` in ISO 8601 format and a `level_name` field for readability in log aggregators.
- Consider adding a `LogMiddleware` (processor) via Monolog to attach these fields automatically.

## Summary

The app has a solid foundation with structured JSON logging and a comprehensive health endpoint. The critical gaps are: (1) correlation ID not propagated to logs, (2) no request duration measurement, (3) no slow query detection, and (4) no queue job duration tracking. These are all quick wins (< 1 day each) that would significantly improve debuggability in production.
