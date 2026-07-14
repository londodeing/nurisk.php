<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Governance\GovernanceAuthorization;
use App\Models\OrgSk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgSkController extends Controller
{
    use GovernanceAuthorization;

    public function index(Request $request): JsonResponse
    {
        $items = OrgSk::with(['mandates.user.profil'])
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeGovernance($request);
        $validated = $request->validate([
            'nomor_sk' => 'required|string|max:100|unique:org_sks,nomor_sk',
            'tanggal_mulai' => 'required|date',
        ]);
        return response()->json(['message' => 'SK Organisasi dibuat.', 'data' => OrgSk::create($validated)], 201);
    }

    public function show(OrgSk $orgSk): JsonResponse
    {
        $orgSk->load(['mandates.user.profil']);
        return response()->json(['data' => $orgSk]);
    }

    public function destroy(OrgSk $orgSk): JsonResponse
    {
        $this->authorizeGovernance(request());
        $orgSk->delete();
        return response()->json(['message' => 'SK dihapus.']);
    }
}
