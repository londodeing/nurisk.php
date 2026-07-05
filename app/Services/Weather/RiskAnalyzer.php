<?php

namespace App\Services\Weather;

class RiskAnalyzer
{
    const LEVELS = ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'];

    public function analyze(array $hourlyForecast, array $dailyForecast, array $context = []): array
    {
        $dailyDays = $dailyForecast['days'] ?? [];

        return [
            'heavy_rain' => $this->analyzeHeavyRain($dailyDays),
            'flood' => $this->analyzeFlood($dailyDays),
            'strong_wind' => $this->analyzeStrongWind($hourlyForecast),
            'thunderstorm' => $this->analyzeThunderstorm($hourlyForecast),
        ];
    }

    private function analyzeHeavyRain(array $dailyDays): array
    {
        $maxRainfall = 0;
        $peakDate = null;

        foreach ($dailyDays as $day) {
            $rain = $day['expected_rainfall_mm'] ?? 0;
            if ($rain > $maxRainfall) {
                $maxRainfall = $rain;
                $peakDate = $day['date'] ?? null;
            }
        }

        $level = 'LOW';
        $reason = 'Curah hujan normal';

        if ($maxRainfall > 100) {
            $level = 'CRITICAL';
            $reason = "Curah hujan ekstrem {$maxRainfall}mm/24jam";
        } elseif ($maxRainfall > 50) {
            $level = 'HIGH';
            $reason = "Curah hujan lebat {$maxRainfall}mm/24jam";
        } elseif ($maxRainfall > 20) {
            $level = 'MEDIUM';
            $reason = "Curah hujan sedang {$maxRainfall}mm/24jam";
        }

        return [
            'level' => $level,
            'reason' => $reason,
            'peak_time' => $peakDate ? $peakDate . 'T14:00:00+07:00' : null,
            'max_rainfall_mm' => round($maxRainfall, 1),
        ];
    }

    private function analyzeFlood(array $dailyDays): array
    {
        $consecutiveHeavyRain = 0;
        $maxConsecutive = 0;
        $totalRain48h = 0;

        foreach ($dailyDays as $i => $day) {
            $rain = $day['expected_rainfall_mm'] ?? 0;
            $totalRain48h += $rain;

            if ($i > 0) {
                $prevRain = $dailyDays[$i - 1]['expected_rainfall_mm'] ?? 0;
                $totalRain48h = $rain + $prevRain;
            }

            if ($rain > 50) {
                $consecutiveHeavyRain++;
                $maxConsecutive = max($maxConsecutive, $consecutiveHeavyRain);
            } else {
                $consecutiveHeavyRain = 0;
            }
        }

        $level = 'LOW';
        $reason = 'Tidak ada risiko banjir';

        if ($maxConsecutive >= 3 || $totalRain48h > 150) {
            $level = 'CRITICAL';
            $reason = "Risiko banjir tinggi: {$maxConsecutive} hari berturut-turut hujan lebat, akumulasi {$totalRain48h}mm";
        } elseif ($maxConsecutive >= 2 || $totalRain48h > 100) {
            $level = 'HIGH';
            $reason = "Waspada banjir: akumulasi {$totalRain48h}mm dalam 48 jam";
        } elseif ($totalRain48h > 50) {
            $level = 'MEDIUM';
            $reason = "Pantau: akumulasi {$totalRain48h}mm dalam 48 jam";
        }

        return [
            'level' => $level,
            'reason' => $reason,
            'consecutive_heavy_rain_days' => $maxConsecutive,
            'accumulation_48h_mm' => round($totalRain48h, 1),
        ];
    }

    private function analyzeStrongWind(array $hourlyForecast): array
    {
        $hours = $hourlyForecast['hours'] ?? [];
        $maxWind = 0;
        $peakTime = null;

        foreach ($hours as $h) {
            $wind = $h['wind_speed'] ?? 0;
            if ($wind > $maxWind) {
                $maxWind = $wind;
                $peakTime = $h['time'] ?? null;
            }
        }

        $level = 'LOW';
        $reason = 'Angin normal';

        if ($maxWind > 60) {
            $level = 'CRITICAL';
            $reason = "Angin kencang ekstrem {$maxWind} km/jam";
        } elseif ($maxWind > 40) {
            $level = 'HIGH';
            $reason = "Angin kencang {$maxWind} km/jam, waspadai pohon tumbang";
        } elseif ($maxWind > 25) {
            $level = 'MEDIUM';
            $reason = "Angin cukup kencang {$maxWind} km/jam";
        }

        return [
            'level' => $level,
            'reason' => $reason,
            'max_wind_speed_kmh' => round($maxWind * 3.6, 1),
            'peak_time' => $peakTime,
        ];
    }

    private function analyzeThunderstorm(array $hourlyForecast): array
    {
        $hours = $hourlyForecast['hours'] ?? [];
        $hasThunderstorm = false;
        $hasHeavyRain = false;
        $hasStrongWind = false;
        $thunderHours = [];

        foreach ($hours as $h) {
            $condId = $h['condition_id'] ?? 0;
            $rainProb = $h['rain_probability'] ?? 0;
            $wind = $h['wind_speed'] ?? 0;

            if ($condId >= 200 && $condId < 300) {
                $hasThunderstorm = true;
                $thunderHours[] = $h['time'];
                if ($rainProb > 70) $hasHeavyRain = true;
                if ($wind > 11) $hasStrongWind = true;
            }
        }

        $level = 'LOW';
        $reason = 'Tidak ada potensi petir';

        if ($hasThunderstorm && $hasHeavyRain && $hasStrongWind) {
            $level = 'CRITICAL';
            $reason = 'Badai petir dengan hujan lebat dan angin kencang';
        } elseif ($hasThunderstorm && $hasHeavyRain) {
            $level = 'HIGH';
            $reason = 'Potensi badai petir disertai hujan lebat';
        } elseif ($hasThunderstorm) {
            $level = 'MEDIUM';
            $reason = 'Potensi petir pada sore/malam hari';
        }

        return [
            'level' => $level,
            'reason' => $reason,
            'thunderstorm_hours' => $thunderHours,
        ];
    }
}
