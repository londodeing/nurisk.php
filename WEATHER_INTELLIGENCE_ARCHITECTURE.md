# WEATHER INTELLIGENCE ARCHITECTURE

## Architectural Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                     WEATHER INTELLIGENCE LAYER                       │
│                                                                     │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────────────────┐   │
│  │ Provider     │  │ Normalizer   │  │ Risk Analyzer            │   │
│  │ Adapter      │  │              │  │                          │   │
│  │              │  │ OpenWeather  │  │ Heavy Rain Risk          │   │
│  │ OpenWeather  │  │  → Internal  │  │ Flood Risk               │   │
│  │ BMKG         │  │  Schema      │  │ Strong Wind Risk         │   │
│  │ (future)     │  │              │  │ Thunderstorm Risk        │   │
│  └──────┬───────┘  └──────┬───────┘  └────────────┬─────────────┘   │
│         │                 │                       │                 │
│         └────────┬────────┘───────────────────────┘                 │
│                  │                                                  │
│          ┌───────▼────────┐                                         │
│          │ Snapshot       │                                         │
│          │ Repository     │                                         │
│          │                │                                         │
│          │ weather_       │                                         │
│          │ snapshots      │                                         │
│          │ (database)     │                                         │
│          └───────┬────────┘                                         │
│                  │                                                  │
└──────────────────┼──────────────────────────────────────────────────┘
                   │
      ┌────────────┼────────────┬──────────────────┐
      │            │            │                  │
      ▼            ▼            ▼                  ▼
┌─────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐
│ Public  │ │ Dashboard│ │ API      │ │ Early Warning    │
│ KPI     │ │ Widgets  │ │ Endpoints│ │ Service          │
└─────────┘ └──────────┘ └──────────┘ └──────────────────┘
```

## Layer Responsibilities

### 1. Provider Adapter
- Abstracts external weather providers
- Currently: OpenWeatherMap (5-day/3-hour forecast)
- Future: BMKG, custom API
- Single responsibility: fetch raw data from provider

### 2. Normalizer
- Transforms provider-specific format → internal standard schema
- Handles unit conversion (Kelvin → Celsius, etc.)
- Handles locale (BMKG Indonesian labels → internal codes)
- Validates required fields exist

### 3. Risk Analyzer
- Pure computation, no I/O
- Input: normalized weather data
- Output: risk levels per territory (LOW/MEDIUM/HIGH/CRITICAL)
- Runs during snapshot creation, not at query time

### 4. Snapshot Repository
- Database-persisted weather snapshots
- Keyed by territory (`pwnu`, `pcnu_{id}`, `mwc_{id}`)
- Contains: current + hourly + daily + risk analysis
- TTL tracked via `expires_at` column
- Scheduler-driven refresh

### 5. Territory Resolver
- Maps user scope → territory code
- `super_admin` → all PCNU territories
- `pwnu` → all PCNU territories
- `pcnu` → single PCNU territory + its MWCs
- `relawan` → assigned posko territory

## Data Flow

### Write Path (Scheduler)
```
Schedule Trigger
  ↓
Provider Adapter → fetch from OpenWeatherMap
  ↓
Normalizer → transform to canonical format
  ↓
Risk Analyzer → compute disaster indicators
  ↓
Snapshot Repository → upsert weather_snapshots
```

### Read Path (Dashboard)
```
User Request
  ↓
Dashboard Controller
  ↓
Snapshot Repository → query by territory_code
  ↓
Return JSON/View
  ↓
Render (zero external I/O)
```

## Failure Mode
- If scheduler fails → last snapshot remains in DB
- If snapshot expired → serve stale data + `last_update` timestamp
- If no snapshot exists → return null + graceful UI
- Dashboard NEVER calls provider directly

## Scheduled Tasks

| Task | Cadence | Description |
|------|---------|-------------|
| `weather:fetch --scope=pwnu` | Every 30 min | Fetch & cache for all PCNU territories |
| `weather:fetch --scope=pcnu:{id}` | Every 30 min | Fetch & cache for specific PCNU |
| `weather:prune` | Daily at 03:00 | Delete snapshots older than 7 days |
