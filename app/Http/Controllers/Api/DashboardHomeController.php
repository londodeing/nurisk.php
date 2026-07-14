<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardHomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardHomeController extends Controller
{
    public function __construct(
        private DashboardHomeService $dashboardService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        $widgets = $this->dashboardService->getWidgets($user);

        return response()->json([
            'screen' => 'DashboardHome',
            'layout' => 'list',
            'nodes' => [
                [
                    'type' => 'ListView',
                    'props' => ['padding' => 16],
                    'children' => $widgets
                ]
            ]
        ]);
    }
}
