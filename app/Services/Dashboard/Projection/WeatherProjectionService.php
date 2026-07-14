<?php

namespace App\Services\Dashboard\Projection;

use Illuminate\Support\Facades\Cache;

class WeatherProjectionService
{
    public function getWeatherFeeds(): array
    {
        return Cache::remember('projection_weather_feeds', 60, function () {
            return [
                'location' => 'Solo Raya',
                'condition' => 'Rainy',
                'temperature' => 27.5,
                'wind_speed' => 12.0,
                'warnings' => [
                    [
                        'id' => 'w_bmkg_01',
                        'title' => 'Peringatan Banjir',
                        'severity' => 'warning',
                        'description' => 'Siaga banjir pesisir utara dan Solo Raya.',
                    ]
                ],
            ];
        });
    }
}
