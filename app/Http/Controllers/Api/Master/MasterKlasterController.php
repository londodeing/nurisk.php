<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterKlaster;
use App\Http\Resources\Master\MasterKlasterResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterKlasterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = MasterKlaster::paginate($request->get('per_page', 50));
        return response()->json([
            'data' => MasterKlasterResource::collection($items),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function show(MasterKlaster $item): JsonResponse
    {
        return response()->json(new MasterKlasterResource($item));
    }
}
