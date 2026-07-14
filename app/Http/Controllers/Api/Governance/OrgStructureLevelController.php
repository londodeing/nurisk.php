<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Governance\GovernanceAuthorization;
use App\Models\OrgStructureLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgStructureLevelController extends Controller
{
    use GovernanceAuthorization;

    public function index(): JsonResponse
    {
        return response()->json(['data' => OrgStructureLevel::orderBy('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeGovernance($request);
        $validated = $request->validate(['name' => 'required|string|max:100', 'weight' => 'required|integer|min:1']);
        return response()->json(['message' => 'Level struktur dibuat.', 'data' => OrgStructureLevel::create($validated)], 201);
    }

    public function update(Request $request, OrgStructureLevel $orgStructureLevel): JsonResponse
    {
        $this->authorizeGovernance($request);
        $orgStructureLevel->update($request->validate(['name' => 'sometimes|string|max:100', 'weight' => 'sometimes|integer|min:1']));
        return response()->json(['message' => 'Level diperbarui.']);
    }

    public function destroy(OrgStructureLevel $orgStructureLevel): JsonResponse
    {
        $this->authorizeGovernance(request());
        $orgStructureLevel->delete();
        return response()->json(['message' => 'Level dihapus.']);
    }
}
