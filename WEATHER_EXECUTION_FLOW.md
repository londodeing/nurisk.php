# WEATHER EXECUTION FLOW

## Current Flow Analysis

### Request Chain

```
User Browser
    ↓ (navigator.geolocation.getCurrentPosition)
User GPS Coordinates (lat, lon)
    ↓ (fetch GET /api/weather/forecast?lat=X&lon=Y)
Laravel Route → WeatherForecastController@forecast
    ↓ (Cache::remember with key "weather_forecast_{roundedLat}_{roundedLon}", TTL 600s)
┌─────────────────────────────────────────────────────────┐
│ CACHE HIT                                               │
│   → Return cached JSON immediately                     │
└─────────────────────────────────────────────────────────┘
    ↓ (CACHE MISS)
Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/forecast', params)
    ↓
OpenWeatherMap API (External)
    ↓
Parse & Transform Response
    ↓ (group by day, extract 5-day forecast)
Store in Cache (600s TTL)
    ↓
Return JSON to Browser
    ↓
JavaScript Renders Weather Strip + Scrollable Forecast
```

### Components Involved

| Component | Location | Responsibility |
|-----------|----------|----------------|
| WeatherForecastController | `app/Http/Controllers/Api/WeatherForecastController.php` | API endpoint, cache logic, OpenWeatherMap integration, mock fallback |
| Route | `routes/api.php` line 28 | `GET /api/weather/forecast` |
| Dashboard View | `resources/views/public/dashboard.blade.php` lines 670-736 | GPS detection, fetch weather, render UI |
| Config | `config/services.php` | OpenWeather API key |
| Cache | Laravel Cache (default driver) | In-memory/file/redis cache per coordinate |

### Dependency Chain

```
Dashboard Blade
    → WeatherForecastController
        → Cache (Laravel)
        → Http Client (Guzzle)
        → OpenWeatherMap API
        → config/services.php (api key)
```

### Current Request Frequency

| Scenario | External API Calls |
|----------|-------------------|
| Single user, single page load | 1 (if cache miss) / 0 (cache hit) |
| Single user, 5 page loads in 10 min | 1 (first load caches, rest hit) |
| 100 users, different locations, 10 min | Up to 100 (each unique lat/lon) |
| 1000 users, 35 kabupaten coverage, daily | ~35 × 144 = 5,040 calls/day (theoretical max) |

### Cache Key Strategy

```
weather_forecast_{roundedLat}_{roundedLon}
```
- Rounded to 4 decimal places (~11m precision)
- Each unique coordinate pair = separate cache entry
- No territory/PCNU awareness

### Mock Fallback Trigger

Mock data returned when:
1. `OPENWEATHER_API_KEY` not set or equals `'YOUR_API_KEY_HERE'`
2. OpenWeatherMap API returns non-2xx response
3. Response missing `list` field
4. Network timeout (10s) or exception

---

## Target Flow (Post-Implementation)

### Request Chain
```
User Browser
    ↓
Dashboard Controller
    ↓ (no GPS required)
WeatherSnapshot::where('territory_code', resolveTerritory())
    ↓
weather_snapshots TABLE (Database)
    ↓
Return JSON to Browser (zero external I/O)
    ↓
JavaScript Renders Weather Strip + Forecast + Early Warning
```

### Scheduled Write Path
```
CRON: * * * * * (every 30 min)
    ↓
php artisan weather:fetch --scope=all
    ↓
WeatherForecastService → Provider Adapter
    ↓
OpenWeatherMap API (External)   ← Only scheduler calls this
    ↓
Normalizer → Transform
    ↓
Risk Analyzer → Compute indicators
    ↓
WeatherSnapshot::upsert() → Database
```

### Key Changes from Current Flow
| Aspect | Current | Target |
|--------|---------|--------|
| API calls | On every page load (cached 10min) | Only scheduler (every 30 min) |
| GPS required | Yes (`navigator.geolocation`) | No (territory-based) |
| Storage | Volatile cache | Database |
| Territory | Lat/lon coordinates | PCNU/MWC hierarchy |
| Risk analysis | None | Built-in |
| Early warning | None | Hazard levels |
| Failure mode | Mock data | Last snapshot |
| Dashboard rendering | Synchronous fetch | Async from DB |

---

## Identified Problems

1. **Client-side GPS dependency**: Dashboard blocks on `navigator.geolocation` before weather fetch
2. **No pre-warming**: Cache populated on-demand by user requests
3. **Per-coordinate caching**: 35 kabupaten × multiple coordinates = fragmented cache
4. **No scheduler**: Cache expires, next user waits for external API
5. **10-minute TTL too short** for forecast data (forecast changes hourly)
6. **No territory mapping**: Cannot serve PCNU/PWNU level weather
7. **No disaster indicators**: Raw forecast only, no risk analysis
8. **No persistence**: Cache clear = all weather data lost
9. **Synchronous render**: Dashboard waits for weather (though non-blocking JS, still user-perceived delay)
10. **No early warning**: No flood/storm/heavy rain alerts