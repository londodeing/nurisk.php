<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ClusterCoordinatorDashboardService;
use Illuminate\Http\JsonResponse;

class ClusterCoordinatorDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(ClusterCoordinatorDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $initialData = $this->dashboardService->getPollingData();
        return view('dashboard.cluster', compact('initialData'));
    }

    public function polling(): JsonResponse
    {
        $start = microtime(true);
        $data = $this->dashboardService->getPollingData();
        $duration = (microtime(true) - $start) * 1000;
        
        $data['debug'] = ['response_time_ms' => round($duration)];
        return response()->json($data);
    }
}
