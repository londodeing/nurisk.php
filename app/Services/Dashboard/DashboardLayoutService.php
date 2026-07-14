<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Config;

class DashboardLayoutService
{
    public function getLayoutForUser($user): array
    {
        // Instead of role, we check authorization / policies.
        // For now, we simulate policy checks:
        $layout = [
            ['type' => 'WarningBlock'],
            ['type' => 'WeatherBlock']
        ];

        // Simulated permission check: "can_view_kpi"
        if ($user && $user->tokenCan('view_kpi') || true) { // simplified for now
            $layout[] = ['type' => 'KpiBlock'];
        }

        // Simulated permission check: "can_view_trc_queue"
        if ($user && $user->tokenCan('view_trc_queue') || false) {
            $layout[] = ['type' => 'TrcQueueBlock'];
        }

        $layout[] = ['type' => 'IncidentBlock'];
        $layout[] = ['type' => 'NewsBlock'];
        
        return $layout;
    }
}
