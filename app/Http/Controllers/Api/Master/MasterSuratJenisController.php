<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterSuratJenis;
use App\Http\Resources\Master\MasterSuratJenisResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterSuratJenisController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = MasterSuratJenis::paginate($request->get('per_page', 50));
        return response()->json([
            'data' => MasterSuratJenisResource::collection($items),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function show(MasterSuratJenis $item): JsonResponse
    {
        return response()->json(new MasterSuratJenisResource($item));
    }
}
