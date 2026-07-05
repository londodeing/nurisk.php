<?php

namespace App\Services\Weather;

use App\Models\WeatherSnapshot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherForecastService
{
    public function __construct(
        private WeatherNormalizer $normalizer,
        private RiskAnalyzer $riskAnalyzer,
        private TerritoryResolver $territoryResolver,
    ) {}

    public function refreshTerritory(string $territoryCode): ?WeatherSnapshot
    {
        $coord = $this->territoryResolver->resolveCoordinate($territoryCode);
        $raw = $this->fetchFromProvider($coord['lat'], $coord['lon']);

        if (!$raw) {
            return $this->staleFallback($territoryCode);
        }

        $current = $this->normalizer->normalizeCurrent($raw['raw_current'] ?? $raw['list'][0] ?? []);
        $hourly = $this->normalizer->normalizeHourly($raw['list'] ?? []);
        $daily = $this->normalizer->normalizeDaily($raw['list'] ?? []);

        $risk = $this->riskAnalyzer->analyze($hourly, $daily);

        $parts = explode(':', $territoryCode);

        return WeatherSnapshot::updateOrCreate(
            [
                'territory_code' => $territoryCode,
                'provider' => 'openweathermap',
            ],
            [
                'territory_type' => $parts[0] ?? 'pwnu',
                'territory_id' => (int) ($parts[1] ?? 0),
                'current_weather' => $current,
                'hourly_forecast' => $hourly,
                'daily_forecast' => $daily,
                'risk_analysis' => $risk,
                'cached_at' => now(),
                'expires_at' => $this->computeExpiry($territoryCode),
            ]
        );
    }

    public function refreshAllPcnu(): array
    {
        $territories = $this->territoryResolver->allPcnuTerritories();
        $results = [];

        foreach ($territories as $t) {
            try {
                $snapshot = $this->refreshTerritory($t['code']);
                $results[$t['code']] = $snapshot ? 'ok' : 'failed';
            } catch (\Throwable $e) {
                Log::warning("Weather refresh failed for {$t['code']}: {$e->getMessage()}");
                $results[$t['code']] = 'error';
            }
        }

        return $results;
    }

    public function fetchFromProvider(float $lat, float $lon): ?array
    {
        $apiKey = config('services.openweather.key');

        if (!$apiKey || $apiKey === 'YOUR_API_KEY_HERE') {
            return $this->generateMockData($lat, $lon);
        }

        try {
            $response = Http::timeout(10)->get(
                'https://api.openweathermap.org/data/2.5/forecast',
                [
                    'lat' => round($lat, 4),
                    'lon' => round($lon, 4),
                    'appid' => $apiKey,
                    'units' => 'metric',
                    'lang' => 'id',
                ]
            );

            if ($response->failed()) {
                Log::warning("OpenWeatherMap API failed for {$lat},{$lon}: {$response->status()}");
                return null;
            }

            $raw = $response->json();
            if (!$raw || !isset($raw['list'])) {
                Log::warning("OpenWeatherMap: invalid response for {$lat},{$lon}");
                return null;
            }

            return $raw;
        } catch (\Throwable $e) {
            Log::error("OpenWeatherMap exception for {$lat},{$lon}: {$e->getMessage()}");
            return null;
        }
    }

    private function generateMockData(float $lat, float $lon): array
    {
        $conditions = [
            ['condition' => 'Cerah Berawan', 'icon' => '02d', 'id' => 802],
            ['condition' => 'Hujan Ringan', 'icon' => '10d', 'id' => 500],
            ['condition' => 'Berawan', 'icon' => '04d', 'id' => 804],
            ['condition' => 'Cerah', 'icon' => '01d', 'id' => 800],
            ['condition' => 'Hujan Petir', 'icon' => '11d', 'id' => 200],
        ];

        $list = [];
        $start = now()->startOfHour();
        for ($i = 0; $i < 24; $i++) {
            $dt = $start->copy()->addHours($i * 3);
            $cond = $conditions[$i % count($conditions)];
            $tempBase = 27 + sin($i * 0.5) * 4;

            $list[] = [
                'dt' => $dt->timestamp,
                'main' => [
                    'temp' => round($tempBase, 1),
                    'feels_like' => round($tempBase - 1, 1),
                    'temp_min' => round($tempBase - 3, 1),
                    'temp_max' => round($tempBase + 3, 1),
                    'pressure' => 1013,
                    'humidity' => rand(60, 90),
                ],
                'weather' => [
                    [
                        'id' => $cond['id'],
                        'description' => $cond['condition'],
                        'icon' => $cond['icon'],
                    ],
                ],
                'wind' => ['speed' => rand(5, 30) / 10, 'deg' => rand(0, 360)],
                'pop' => rand(0, 80) / 100,
                'rain' => ['3h' => rand(0, 50) / 10],
                'visibility' => rand(3000, 10000),
            ];
        }

        return [
            'city' => ['name' => 'Jawa Tengah', 'country' => 'ID'],
            'list' => $list,
        ];
    }

    private function staleFallback(string $territoryCode): ?WeatherSnapshot
    {
        return WeatherSnapshot::byTerritory($territoryCode)->latest('id')->first();
    }

    private function computeExpiry(string $territoryCode): \Carbon\Carbon
    {
        if (str_starts_with($territoryCode, 'pwnu')) {
            return now()->addHour();
        }
        return now()->addMinutes(30);
    }
}
