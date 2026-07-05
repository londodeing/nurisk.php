<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Models\OrgGovernanceFunction;
use App\Models\OrgFunctionAuthority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgFunctionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => OrgGovernanceFunction::with('authorities')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);
        return response()->json(['message' => 'Fungsi dibuat.', 'data' => OrgGovernanceFunction::create($validated)], 201);
    }

    public function show(OrgGovernanceFunction $orgFunction): JsonResponse
    {
        $orgFunction->load('authorities');
        return response()->json(['data' => $orgFunction]);
    }

    public function update(Request $request, OrgGovernanceFunction $orgFunction): JsonResponse
    {
        $orgFunction->update($request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:255',
        ]));
        return response()->json(['message' => 'Fungsi diperbarui.']);
    }

    public function destroy(OrgGovernanceFunction $orgFunction): JsonResponse
    {
        $orgFunction->authorities()->detach();
        $orgFunction->delete();
        return response()->json(['message' => 'Fungsi dihapus.']);
    }

    public function assignAuthority(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'function_id' => 'required|exists:org_governance_functions,id',
            'authority_id' => 'required|exists:org_authorities,id',
        ]);
        $fa = OrgFunctionAuthority::create($validated);
        return response()->json(['message' => 'Authority ditambahkan ke fungsi.', 'data' => $fa], 201);
    }

    public function removeAuthority(OrgFunctionAuthority $functionAuthority): JsonResponse
    {
        $functionAuthority->delete();
        return response()->json(['message' => 'Authority dihapus dari fungsi.']);
    }
}
