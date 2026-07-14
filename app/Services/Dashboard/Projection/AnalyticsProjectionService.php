<?php

namespace App\Services\Dashboard\Projection;

use Illuminate\Support\Facades\Cache;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;

class AnalyticsProjectionService
{
    public function getAnalyticsAggregations(): array
    {
        return Cache::remember('projection_analytics_aggregations', 60, function () {
            $totalActive = OperasiInsiden::whereIn('status_insiden', ['respon', 'pemulihan'])->count();
            $totalResolved = OperasiInsiden::where('status_insiden', 'selesai')->count();
            $personnelMobilized = OperasiPenugasan::aktif()->count();

            return [
                'total_incidents_active' => $totalActive,
                'incidents_resolved_today' => $totalResolved,
                'personnel_mobilized' => $personnelMobilized,
            ];
        });
    }
}
