<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\JabatanPosisi;
use App\Http\Resources\Master\MasterJabatanResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterJabatanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = JabatanPosisi::paginate($request->get('per_page', 50));
        return response()->json([
            'data' => MasterJabatanResource::collection($items),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function show(JabatanPosisi $item): JsonResponse
    {
        return response()->json(new MasterJabatanResource($item));
    }
}
