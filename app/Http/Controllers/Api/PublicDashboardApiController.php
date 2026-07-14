<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Dashboard\DashboardProjectionService;
use App\Services\Dashboard\DashboardLayoutService;
use App\Services\Dashboard\DashboardJsonBuilder;

use App\Services\Dashboard\Composer\PublicDashboardComposer;
use App\Services\Sdui\Runtime\Screens\DashboardHomeService;

class PublicDashboardApiController extends Controller
{
    public function config(Request $request, PublicDashboardComposer $composer, DashboardHomeService $dashboardService): JsonResponse
    {
        // Runtime mode: return NSS 1.0 envelope
        if ($request->query('runtime') === '1') {
            return response()->json($dashboardService->compose());
        }

        // Legacy mode: return old SDUI format
        $node = $composer->compose();

        return response()->json([
            'screen' => 'PublicDashboard',
            'layout' => 'vertical',
            'nodes' => [$node]
        ]);
    }
}
