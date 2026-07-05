<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\BencanaMasterJenis;
use App\Http\Resources\Master\BencanaMasterJenisResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BencanaMasterJenisController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = BencanaMasterJenis::paginate($request->get('per_page', 50));
        return response()->json([
            'data' => BencanaMasterJenisResource::collection($items),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function show(BencanaMasterJenis $item): JsonResponse
    {
        return response()->json(new BencanaMasterJenisResource($item));
    }
}
