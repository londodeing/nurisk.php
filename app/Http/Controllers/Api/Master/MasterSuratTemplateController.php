<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterSuratTemplate;
use App\Http\Resources\Master\MasterSuratTemplateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterSuratTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = MasterSuratTemplate::paginate($request->get('per_page', 50));
        return response()->json([
            'data' => MasterSuratTemplateResource::collection($items),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function show(MasterSuratTemplate $item): JsonResponse
    {
        return response()->json(new MasterSuratTemplateResource($item));
    }
}
