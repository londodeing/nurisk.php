<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Governance\GovernanceAuthorization;
use App\Models\OrgMandate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgMandateController extends Controller
{
    use GovernanceAuthorization;

    public function index(Request $request): JsonResponse
    {
        $items = OrgMandate::with(['user.profil', 'sk'])
            ->when($request->user_id, fn($q, $v) => $q->where('user_id', $v))
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeGovernance($request);
        $validated = $request->validate([
            'sk_id' => 'required|exists:org_sks,id',
            'user_id' => 'required|exists:auth_users,id_pengguna',
            'node_position_id' => 'required|exists:org_node_positions,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'nullable|date|after:tanggal_mulai',
        ]);
        return response()->json(['message' => 'Mandat dibuat.', 'data' => OrgMandate::create($validated)], 201);
    }

    public function show(OrgMandate $orgMandate): JsonResponse
    {
        $orgMandate->load(['user.profil', 'sk', 'nodePosition.position', 'nodePosition.node']);
        return response()->json(['data' => $orgMandate]);
    }

    public function destroy(OrgMandate $orgMandate): JsonResponse
    {
        $this->authorizeGovernance(request());
        $orgMandate->delete();
        return response()->json(['message' => 'Mandat dihapus.']);
    }
}
