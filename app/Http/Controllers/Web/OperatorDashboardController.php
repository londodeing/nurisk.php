<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\OperatorDashboardService;
use Illuminate\Http\JsonResponse;

class OperatorDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(OperatorDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $initialData = $this->dashboardService->getPollingData();
        return view('dashboard.operator', compact('initialData'));
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
