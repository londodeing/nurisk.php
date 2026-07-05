# WEATHER CACHE POLICY

## Refresh Cadence

| Data Type | Refresh Interval | Rationale |
|-----------|-----------------|-----------|
| Current Weather | 15 minutes | Actual conditions change faster |
| Hourly Forecast (72h) | 30 minutes | Forecast updates every 1-3 hours |
| Daily Forecast (7d) | 60 minutes | Daily outlook updates slowly |
| Risk Analysis | 30 minutes | Re-computed from forecast data |

## Cache Storage

### Database (Primary)
Table `weather_snapshots` — persistent, survives cache clear, serves all dashboards

### Laravel Cache (Secondary)
- Key: `weather_current_{territory}`, TTL: 15 min
- Key: `weather_risk_{territory}`, TTL: 30 min
- Volatile, faster for repeated reads within TTL window

## Cache Warm Strategy

### Scheduler-Driven
```
php artisan weather:fetch --scope=all   # Every 30 min via Cron
```

### Stale-While-Revalidate
1. Dashboard reads from DB
2. If `expires_at < now`:
   - Return stale data with `last_update` timestamp
   - Dispatch background job to refresh
3. Next request serves fresh data

### Cold Start
- First deployment: run `php artisan weather:fetch --scope=all`
- Scheduler picks up thereafter
- Dashboard shows "Data cuaca belum tersedia" until first fetch

## Cache Key Design

### Database Key
```
territory_code = '{scope_type}:{scope_id}'
Examples:
  pwnu:0                    → PWNU level (all Jateng)
  pcnu:12                   → PCNU Kudus
  mwc:45                    → MWC specific
```

### Laravel Cache Key
```
weather_snapshot_{territory_code}
weather_risk_{territory_code}
```

## TTL Values

| Environment | Current | Hourly | Daily | Risk |
|-------------|---------|--------|-------|------|
| Production | 15 min | 30 min | 60 min | 30 min |
| Staging | 5 min | 15 min | 30 min | 15 min |
| Development | 1 min | 5 min | 10 min | 5 min |

## Eviction Policy
- Snapshots older than 7 days deleted by daily `weather:prune` command
- Manual eviction: `php artisan weather:prune --force`
- No LRU needed (territory count is bounded: ~35 PCNU + ~700 MWC)
