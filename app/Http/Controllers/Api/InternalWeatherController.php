<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeatherSnapshot;
use App\Services\Weather\TerritoryResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalWeatherController extends Controller
{
    public function __construct(
        private TerritoryResolver $territoryResolver,
    ) {}

    public function current(Request $request): JsonResponse
    {
        $territory = $this->resolveTerritory($request);
        if (!$territory) {
            return response()->json(['error' => 'Wilayah tidak ditemukan'], 404);
        }

        $snapshot = WeatherSnapshot::byTerritory($territory)->latest('id')->first();

        if (!$snapshot) {
            return response()->json([
                'data' => null,
                'message' => 'Data cuaca belum tersedia',
                'last_update' => null,
            ]);
        }

        return response()->json([
            'data' => $snapshot->current_weather,
            'is_fresh' => $snapshot->isFresh(),
            'last_update' => $snapshot->cached_at?->toIso8601String(),
        ]);
    }

    public function hourly(Request $request): JsonResponse
    {
        $territory = $this->resolveTerritory($request);
        if (!$territory) {
            return response()->json(['error' => 'Wilayah tidak ditemukan'], 404);
        }

        $snapshot = WeatherSnapshot::byTerritory($territory)->latest('id')->first();

        if (!$snapshot) {
            return response()->json(['data' => null, 'message' => 'Prakiraan cuaca belum tersedia']);
        }

        return response()->json([
            'data' => $snapshot->hourly_forecast,
            'last_update' => $snapshot->cached_at?->toIso8601String(),
        ]);
    }

    public function daily(Request $request): JsonResponse
    {
        $territory = $this->resolveTerritory($request);
        if (!$territory) {
            return response()->json(['error' => 'Wilayah tidak ditemukan'], 404);
        }

        $snapshot = WeatherSnapshot::byTerritory($territory)->latest('id')->first();

        if (!$snapshot) {
            return response()->json(['data' => null, 'message' => 'Prakiraan cuaca belum tersedia']);
        }

        return response()->json([
            'data' => $snapshot->daily_forecast,
            'last_update' => $snapshot->cached_at?->toIso8601String(),
        ]);
    }

    public function risk(Request $request): JsonResponse
    {
        $territory = $this->resolveTerritory($request);
        if (!$territory) {
            return response()->json(['error' => 'Wilayah tidak ditemukan'], 404);
        }

        $snapshot = WeatherSnapshot::byTerritory($territory)->latest('id')->first();

        if (!$snapshot) {
            return response()->json(['data' => null, 'message' => 'Analisis risiko belum tersedia']);
        }

        return response()->json([
            'data' => $snapshot->risk_analysis,
            'last_update' => $snapshot->cached_at?->toIso8601String(),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $territory = $this->resolveTerritory($request);
        if (!$territory) {
            return response()->json(['error' => 'Wilayah tidak ditemukan'], 404);
        }

        $snapshot = WeatherSnapshot::byTerritory($territory)->latest('id')->first();

        if (!$snapshot) {
            return response()->json(['data' => null, 'message' => 'Data cuaca belum tersedia']);
        }

        $highestRisk = 'LOW';
        $risks = $snapshot->risk_analysis ?? [];
        foreach ($risks as $key => $risk) {
            $levelOrder = ['LOW' => 0, 'MEDIUM' => 1, 'HIGH' => 2, 'CRITICAL' => 3];
            $currentOrder = $levelOrder[$risk['level'] ?? 'LOW'] ?? 0;
            $highestOrder = $levelOrder[$highestRisk] ?? 0;
            if ($currentOrder > $highestOrder) {
                $highestRisk = $risk['level'];
            }
        }

        return response()->json([
            'data' => [
                'current' => $snapshot->current_weather,
                'risk' => $snapshot->risk_analysis,
                'highest_risk_level' => $highestRisk,
                'daily_summary' => [
                    'rain_probability_today' => $snapshot->daily_forecast['days'][0]['rain_probability'] ?? 0,
                    'temp_min_today' => $snapshot->daily_forecast['days'][0]['temp_min'] ?? null,
                    'temp_max_today' => $snapshot->daily_forecast['days'][0]['temp_max'] ?? null,
                ],
            ],
            'is_fresh' => $snapshot->isFresh(),
            'last_update' => $snapshot->cached_at?->toIso8601String(),
        ]);
    }

    private function resolveTerritory(Request $request): ?string
    {
        if ($request->filled('territory_code')) {
            return $request->territory_code;
        }

        $user = $request->user();
        if ($user) {
            $territories = $this->territoryResolver->resolveFromUser($user);
            return $territories[0]['code'] ?? null;
        }

        return 'pwnu:0';
    }
}
