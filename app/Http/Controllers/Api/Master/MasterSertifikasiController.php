<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterSertifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterSertifikasiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = MasterSertifikasi::paginate($request->get('per_page', 50));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function show(MasterSertifikasi $sertifikasi): JsonResponse
    {
        return response()->json(['data' => $sertifikasi]);
    }
}
