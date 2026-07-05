<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Models\OrgDelegation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgDelegationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = OrgDelegation::orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mandat_asal_id' => 'required|exists:org_mandates,id',
            'mandat_pengganti_id' => 'required|exists:org_mandates,id',
            'mulai' => 'required|date',
            'selesai' => 'nullable|date|after:mulai',
            'jenis' => 'nullable|string|max:50',
        ]);
        return response()->json(['message' => 'Delegasi dibuat.', 'data' => OrgDelegation::create($validated)], 201);
    }

    public function show(OrgDelegation $orgDelegation): JsonResponse
    {
        return response()->json(['data' => $orgDelegation]);
    }

    public function destroy(OrgDelegation $orgDelegation): JsonResponse
    {
        $orgDelegation->delete();
        return response()->json(['message' => 'Delegasi dihapus.']);
    }
}
