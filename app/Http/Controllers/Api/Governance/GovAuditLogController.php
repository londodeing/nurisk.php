<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Models\OrgAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovAuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = OrgAuditLog::with('actor.profil')
            ->when($request->action_type, fn($q, $v) => $q->where('action_type', $v))
            ->when($request->target_table, fn($q, $v) => $q->where('target_table', $v))
            ->orderBy('timestamp', 'desc')
            ->paginate($request->get('per_page', 20));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }
}
