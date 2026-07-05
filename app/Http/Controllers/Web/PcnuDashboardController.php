<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PcnuDashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PcnuDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(PcnuDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $initialData = $this->dashboardService->getPollingData();
        return view('dashboard.pcnu', compact('initialData'));
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
