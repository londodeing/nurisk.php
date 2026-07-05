<?php

namespace App\Http\Controllers\Api\Organisasi;

use App\Http\Controllers\Controller;
use App\Models\OrganisasiAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisasiAuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = OrganisasiAuditLog::with('aktor.profil')
            ->when($request->aksi, fn($q, $v) => $q->where('aksi', $v))
            ->when($request->target_table, fn($q, $v) => $q->where('target_table', $v))
            ->orderBy('timestamp', 'desc')
            ->paginate($request->get('per_page', 20));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }
}
