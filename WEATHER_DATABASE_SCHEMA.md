# WEATHER DATABASE SCHEMA

## Table: `weather_snapshots`

Single table, JSON columns for flexibility. No relational complexity needed.

```sql
CREATE TABLE weather_snapshots (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    territory_code      VARCHAR(50)     NOT NULL COMMENT 'Scope identifier: pwnu:0, pcnu:12, mwc:45',
    territory_type      ENUM('pwnu','pcnu','mwc') NOT NULL,
    territory_id        INT UNSIGNED    NOT NULL,
    provider            VARCHAR(50)     NOT NULL DEFAULT 'openweathermap',

    -- Snapshot payloads (JSON)
    current_weather     JSON            NULL COMMENT 'Suhu, humidity, angin, visibility, tekanan',
    hourly_forecast     JSON            NULL COMMENT '72 jam ke depan per 3 jam',
    daily_forecast      JSON            NULL COMMENT '7 hari ke depan',
    risk_analysis       JSON            NULL COMMENT 'Indikator risiko bencana per territory',

    -- Metadata
    cached_at           TIMESTAMP       NULL COMMENT 'Kapan data diambil dari provider',
    expires_at          TIMESTAMP       NULL COMMENT 'Kapan data dianggap stale',
    created_at          TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_weather_territory (territory_code),
    INDEX idx_weather_expires (expires_at),
    INDEX idx_weather_territory_type (territory_type, territory_id),
    UNIQUE KEY uq_weather_territory (territory_code, provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## JSON Structure

### `current_weather`
```json
{
    "temperature": 28.5,
    "humidity": 75,
    "wind_speed": 12.3,
    "wind_direction": 180,
    "visibility": 8000,
    "pressure": 1013,
    "condition": "Cerah Berawan",
    "condition_code": "02d",
    "timestamp": "2026-06-26T08:00:00+07:00"
}
```

### `hourly_forecast`
```json
{
    "generated_at": "2026-06-26T08:00:00+07:00",
    "hours": [
        {
            "time": "2026-06-26T09:00:00+07:00",
            "temperature": 30.2,
            "rain_probability": 20,
            "rain_volume_mm": 0.5,
            "wind_speed": 10.1,
            "condition": "Berawan",
            "condition_code": "03d"
        }
    ]
}
```

### `daily_forecast`
```json
{
    "generated_at": "2026-06-26T08:00:00+07:00",
    "days": [
        {
            "date": "2026-06-26",
            "temp_min": 24.0,
            "temp_max": 32.0,
            "rain_probability": 40,
            "expected_rainfall_mm": 5.2,
            "condition": "Hujan Ringan",
            "condition_code": "10d"
        }
    ]
}
```

### `risk_analysis`
```json
{
    "generated_at": "2026-06-26T08:00:00+07:00",
    "risks": {
        "heavy_rain": {
            "level": "MEDIUM",
            "reason": "Curah hujan 50-100mm dalam 24 jam",
            "peak_time": "2026-06-27T14:00:00+07:00"
        },
        "flood": {
            "level": "LOW",
            "reason": "Akumulasi hujan < 50mm/24jam"
        },
        "strong_wind": {
            "level": "LOW",
            "reason": "Kecepatan angin < 25 km/jam"
        },
        "thunderstorm": {
            "level": "MEDIUM",
            "reason": "Potensi petir pada sore hari"
        }
    }
}
```

## Migration

```php
Schema::create('weather_snapshots', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('territory_code', 50);
    $table->enum('territory_type', ['pwnu', 'pcnu', 'mwc']);
    $table->unsignedInteger('territory_id');
    $table->string('provider', 50)->default('openweathermap');
    $table->json('current_weather')->nullable();
    $table->json('hourly_forecast')->nullable();
    $table->json('daily_forecast')->nullable();
    $table->json('risk_analysis')->nullable();
    $table->timestamp('cached_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();

    $table->index('territory_code');
    $table->index('expires_at');
    $table->index(['territory_type', 'territory_id']);
    $table->unique(['territory_code', 'provider']);
});
```

## Estimated Growth

| Item | Size |
|------|------|
| PCNU territories | ~35 rows |
| MWC territories | ~700 rows |
| Total rows | ~735 |
| Row size (with JSON) | ~5-10 KB |
| Total storage | ~7 MB |
| 7 days retention | ~51 MB |

Negligible storage footprint.
