<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperasiMetricsDaily;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = OperasiMetricsDaily::orderBy('tanggal', 'desc')
            ->limit($request->get('limit', 30))
            ->get();
        return response()->json(['data' => $items]);
    }
}
