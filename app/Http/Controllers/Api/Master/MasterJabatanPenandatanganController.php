<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterJabatanPenandatangan;
use App\Http\Resources\Master\MasterJabatanPenandatanganResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterJabatanPenandatanganController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = MasterJabatanPenandatangan::paginate($request->get('per_page', 50));
        return response()->json([
            'data' => MasterJabatanPenandatanganResource::collection($items),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function show(MasterJabatanPenandatangan $item): JsonResponse
    {
        return response()->json(new MasterJabatanPenandatanganResource($item));
    }
}
