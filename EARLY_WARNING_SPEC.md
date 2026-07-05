# EARLY WARNING SPECIFICATION

## Warning Levels

| Level | Color | Action |
|-------|-------|--------|
| LOW | Green | Monitor |
| MEDIUM | Yellow | Prepare |
| HIGH | Orange | Alert |
| CRITICAL | Red | Evacuation |

## Warning Types

### 1. Heavy Rain Warning

| Level | Threshold (24h accumulation) | Description |
|-------|------------------------------|-------------|
| LOW | 0-20 mm | Light rain |
| MEDIUM | 20-50 mm | Moderate rain |
| HIGH | 50-100 mm | Heavy rain |
| CRITICAL | >100 mm | Extreme rain |

**Source:** Forecast `rain_volume_mm` aggregated over 24h windows.

### 2. Flood Watch / Flood Warning

| Level | Criteria |
|-------|----------|
| LOW | Rain < 50mm/24h, no prior saturation |
| MEDIUM | Rain 50-100mm/24h OR heavy rain 2+ consecutive days |
| HIGH | Rain 100-150mm/24h OR rain >50mm with prior 24h >50mm |
| CRITICAL | Rain >150mm/24h OR active flood incident in same territory |

**Source:** Rain accumulation + consecutive wet days + active insiden flood events.

### 3. Strong Wind Warning

| Level | Criteria (max gust) |
|-------|---------------------|
| LOW | < 25 km/h |
| MEDIUM | 25-40 km/h |
| HIGH | 40-60 km/h |
| CRITICAL | > 60 km/h |

**Source:** Forecast `wind_speed` max values per day.

### 4. Thunderstorm Warning

| Level | Criteria |
|-------|----------|
| LOW | No thunderstorm in forecast |
| MEDIUM | Thunderstorm condition code in any 3-hour slot |
| HIGH | Thunderstorm + heavy rain (rain_probability > 70%) |
| CRITICAL | Thunderstorm + heavy rain + strong wind simultaneously |

**Source:** Condition codes (`2xx` = thunderstorm in OpenWeatherMap).

## Alert Generation Rules

### Rule Engine (Pure PHP, no external dependency)

```php
class RiskAnalyzer
{
    public function analyze(array $hourlyForecast, array $dailyForecast, ?array $activeIncidents): array
    {
        return [
            'heavy_rain'     => $this->analyzeHeavyRain($dailyForecast),
            'flood'          => $this->analyzeFlood($dailyForecast, $activeIncidents),
            'strong_wind'    => $this->analyzeStrongWind($hourlyForecast),
            'thunderstorm'   => $this->analyzeThunderstorm($hourlyForecast),
        ];
    }

    private function analyzeHeavyRain(array $daily): array { /* threshold logic */ }
    private function analyzeFlood(array $daily, ?array $incidents): array { /* accumulation + context */ }
    private function analyzeStrongWind(array $hourly): array { /* max gust */ }
    private function analyzeThunderstorm(array $hourly): array { /* condition code check */ }
}
```

### Consecutive Day Detection
Flood risk escalates when heavy rain occurs on consecutive days:
- Track rolling 72h accumulation
- Compare against historical average (future)

## Integration with Insiden System

When warning level reaches HIGH or CRITICAL:

1. **Log to jurnal operasi**
   ```php
   OperasiJurnal::create([
       'kategori_event' => 'early_warning',
       'judul_event' => 'Flood Warning: Kabupaten Kudus',
       'deskripsi_event' => 'Level: HIGH | 24h rain: 120mm | Periode: 26-27 Jun 2026',
   ]);
   ```

2. **Notification queue** (future)
   - Create notification record
   - Dashboard badge displays active warnings

3. **Dashboard indicator**
   - Red/orange banner in public dashboard
   - Warning icon in sidebar navigation

## Early Warning Dashboard Widget

```blade
<div class="bg-white rounded-xl border p-4">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">
        Early Warning
        @if($highestRisk === 'CRITICAL')
            <span class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full">AKTIF</span>
        @endif
    </h3>
    @foreach($risks as $type => $risk)
    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
        <span class="text-sm text-gray-600">{{ $risk['label'] }}</span>
        <x-badge-status :status="$risk['level']" map="warning" />
    </div>
    @endforeach
</div>
```

## Alert Badge Status Maps

```php
'warning' => [
    'LOW'      => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Aman'],
    'MEDIUM'   => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Siaga'],
    'HIGH'     => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'label' => 'Waspada'],
    'CRITICAL' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Bahaya'],
];
```

## Testing

| Test Case | Input | Expected Output |
|-----------|-------|----------------|
| Light rain 5mm | `expected_rainfall: 5` | `heavy_rain: LOW` |
| Heavy rain 80mm | `expected_rainfall: 80` | `heavy_rain: HIGH` |
| 2 consecutive heavy rain | Day1: 60mm, Day2: 70mm | `flood: HIGH` |
| Thunderstorm code | `condition_code: 2xx` | `thunderstorm: MEDIUM` |
| No data | `null` | All `LOW` + fallback label |
