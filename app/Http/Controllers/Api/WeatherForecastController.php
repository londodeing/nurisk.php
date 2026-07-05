<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherForecastController extends Controller
{
    public function forecast(Request $request): JsonResponse
    {
        $lat = $request->input('lat');
        $lon = $request->input('lon');

        if (!$lat || !$lon) {
            return response()->json(['error' => 'lat and lon required'], 422);
        }

        $roundedLat = round((float) $lat, 4);
        $roundedLon = round((float) $lon, 4);
        $cacheKey = "weather_forecast_{$roundedLat}_{$roundedLon}";

        $data = Cache::remember($cacheKey, 600, function () use ($roundedLat, $roundedLon, $lat, $lon) {
            $apiKey = config('services.openweather.key');

            if (!$apiKey || $apiKey === 'YOUR_API_KEY_HERE') {
                return $this->mockForecast();
            }

            $response = Http::timeout(10)->get(
                'https://api.openweathermap.org/data/2.5/forecast',
                [
                    'lat'   => $roundedLat,
                    'lon'   => $roundedLon,
                    'appid' => $apiKey,
                    'units' => 'metric',
                    'lang'  => 'id',
                ]
            );

            if ($response->failed()) {
                return $this->mockForecast();
            }

            $raw = $response->json();
            if (!$raw || !isset($raw['list'])) {
                return $this->mockForecast();
            }

            $now = now();
            $daily = [];
            foreach ($raw['list'] as $item) {
                if (!isset($item['dt'], $item['main']['temp'])) continue;

                $dt = $item['dt'];
                $hour = (int) date('H', $dt);

                if ($dt < $now->timestamp) continue;

                $label = match (true) {
                    $hour >= 0 && $hour < 6  => 'Malam',
                    $hour >= 6 && $hour < 12 => 'Pagi',
                    $hour >= 12 && $hour < 18 => 'Siang',
                    default                   => 'Sore/Malam',
                };

                $weather = $item['weather'][0] ?? [];

                $daily[] = [
                    'time'         => date('Y-m-d H:i:s', $dt),
                    'label'        => $label,
                    'temp'         => round((float) ($item['main']['temp'] ?? 0), 1),
                    'temp_min'     => round((float) ($item['main']['temp_min'] ?? 0), 1),
                    'temp_max'     => round((float) ($item['main']['temp_max'] ?? 0), 1),
                    'humidity'     => (int) ($item['main']['humidity'] ?? 0),
                    'condition'    => $weather['description'] ?? '',
                    'icon'         => $weather['icon'] ?? '01d',
                    'wind_speed'   => (float) ($item['wind']['speed'] ?? 0),
                    'rain'         => (float) ($item['rain']['3h'] ?? 0),
                ];
            }

            $grouped = [];
            foreach ($daily as $entry) {
                $day = substr($entry['time'], 0, 10);
                $grouped[$day][] = $entry;
            }

            return [
                'city'    => $raw['city']['name'] ?? 'Lokasi Anda',
                'country' => $raw['city']['country'] ?? '',
                'current' => $daily[0] ?? null,
                'days'    => $grouped,
                'list'    => $daily,
            ];
        });

        return response()->json($data);
    }

    private function mockForecast(): array
    {
        $now = now();
        $days = [];
        $conditions = [
            ['condition' => 'Cerah Berawan', 'icon' => '02d'],
            ['condition' => 'Hujan Ringan', 'icon' => '10d'],
            ['condition' => 'Berawan', 'icon' => '04d'],
            ['condition' => 'Cerah', 'icon' => '01d'],
            ['condition' => 'Hujan Petir', 'icon' => '11d'],
        ];

        $allList = [];
        for ($d = 0; $d < 5; $d++) {
            $date = $now->copy()->addDays($d);
            $dayKey = $date->format('Y-m-d');
            $cond = $conditions[$d % count($conditions)];
            $entries = [];

            $slots = [
                ['label' => 'Pagi',     'hour' => 6],
                ['label' => 'Siang',    'hour' => 12],
                ['label' => 'Sore',     'hour' => 15],
                ['label' => 'Malam',    'hour' => 21],
            ];

            foreach ($slots as $i => $slot) {
                $tempBase = 26 + rand(-3, 6) + ($d * 2 % 5);
                $dt = $date->copy()->setTime($slot['hour'], 0, 0)->timestamp;
                $entries[] = [
                    'time'      => date('Y-m-d H:i:s', $dt),
                    'label'     => $slot['label'],
                    'temp'      => $tempBase + $i,
                    'temp_min'  => $tempBase - 2,
                    'temp_max'  => $tempBase + 4,
                    'humidity'  => rand(55, 90),
                    'condition' => $cond['condition'],
                    'icon'      => $cond['icon'],
                    'wind_speed' => rand(5, 25) / 10,
                    'rain'      => in_array($slot['label'], ['Siang', 'Sore']) ? rand(0, 5) : 0,
                ];
            }

            $allList = array_merge($allList, $entries);
            $days[$dayKey] = $entries;
        }

        return [
            'city'    => 'Jawa Tengah',
            'country' => 'ID',
            'current' => $allList[0] ?? null,
            'days'    => $days,
            'list'    => $allList,
        ];
    }
}
