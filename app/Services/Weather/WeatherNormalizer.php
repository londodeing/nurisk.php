<?php

namespace App\Services\Weather;

class WeatherNormalizer
{
    public function normalizeCurrent(array $raw): array
    {
        $main = $raw['main'] ?? [];
        $wind = $raw['wind'] ?? [];
        $weather = $raw['weather'][0] ?? [];
        $sys = $raw['sys'] ?? [];

        return [
            'temperature' => round((float) ($main['temp'] ?? 0), 1),
            'feels_like' => round((float) ($main['feels_like'] ?? 0), 1),
            'humidity' => (int) ($main['humidity'] ?? 0),
            'wind_speed' => round((float) ($wind['speed'] ?? 0), 1),
            'wind_direction' => (int) ($wind['deg'] ?? 0),
            'visibility' => (int) ($raw['visibility'] ?? 0),
            'pressure' => (int) ($main['pressure'] ?? 0),
            'condition' => $weather['description'] ?? '',
            'condition_code' => $weather['icon'] ?? '01d',
            'condition_id' => $weather['id'] ?? 800,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function normalizeHourly(array $rawList): array
    {
        $hours = [];
        $now = now()->timestamp;

        foreach ($rawList as $item) {
            if (!isset($item['dt'], $item['main']['temp'])) continue;
            if ($item['dt'] < $now) continue;

            $weather = $item['weather'][0] ?? [];

            $hours[] = [
                'time' => date('Y-m-d\TH:i:s', $item['dt']) . '+07:00',
                'temperature' => round((float) ($item['main']['temp'] ?? 0), 1),
                'rain_probability' => (int) (($item['pop'] ?? 0) * 100),
                'rain_volume_mm' => round((float) (($item['rain']['3h'] ?? 0)), 1),
                'wind_speed' => round((float) ($item['wind']['speed'] ?? 0), 1),
                'condition' => $weather['description'] ?? '',
                'condition_code' => $weather['icon'] ?? '01d',
                'condition_id' => $weather['id'] ?? 800,
            ];
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'hours' => array_slice($hours, 0, 24),
        ];
    }

    public function normalizeDaily(array $rawList): array
    {
        $daily = [];
        $now = now()->timestamp;
        $grouped = [];

        foreach ($rawList as $item) {
            if (!isset($item['dt'], $item['main']['temp'])) continue;
            if ($item['dt'] < $now) continue;

            $day = date('Y-m-d', $item['dt']);
            $grouped[$day][] = $item;
        }

        foreach ($grouped as $date => $slots) {
            $temps = array_map(fn($s) => $s['main']['temp'], $slots);
            $pops = array_map(fn($s) => $s['pop'] ?? 0, $slots);
            $rains = array_map(fn($s) => $s['rain']['3h'] ?? 0, $slots);
            $winds = array_map(fn($s) => $s['wind']['speed'] ?? 0, $slots);

            $weather = $slots[0]['weather'][0] ?? [];

            $daily[] = [
                'date' => $date,
                'temp_min' => round(min($temps), 1),
                'temp_max' => round(max($temps), 1),
                'rain_probability' => (int) (max($pops) * 100),
                'expected_rainfall_mm' => round(array_sum($rains), 1),
                'wind_speed_max' => round(max($winds), 1),
                'condition' => $weather['description'] ?? '',
                'condition_code' => $weather['icon'] ?? '01d',
                'condition_id' => $weather['id'] ?? 800,
            ];

            if (count($daily) >= 7) break;
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'days' => $daily,
        ];
    }

    public function normalizeCurrentFromMock(array $mock): array
    {
        return [
            'temperature' => $mock['temp'] ?? 28,
            'feels_like' => $mock['temp'] ?? 28,
            'humidity' => $mock['humidity'] ?? 75,
            'wind_speed' => $mock['wind_speed'] ?? 5,
            'wind_direction' => 180,
            'visibility' => 8000,
            'pressure' => 1013,
            'condition' => $mock['condition'] ?? 'Cerah',
            'condition_code' => $mock['icon'] ?? '01d',
            'condition_id' => 800,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
