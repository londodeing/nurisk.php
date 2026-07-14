<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Governance\GovernanceAuthorization;
use App\Models\OrgPosition;
use App\Models\OrgPositionFunction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgPositionController extends Controller
{
    use GovernanceAuthorization;

    public function index(): JsonResponse
    {
        return response()->json(['data' => OrgPosition::orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeGovernance($request);
        $validated = $request->validate(['name' => 'required|string|max:100', 'level' => 'nullable|integer']);
        return response()->json(['message' => 'Posisi dibuat.', 'data' => OrgPosition::create($validated)], 201);
    }

    public function update(Request $request, OrgPosition $orgPosition): JsonResponse
    {
        $this->authorizeGovernance($request);
        $orgPosition->update($request->validate(['name' => 'sometimes|string|max:100', 'level' => 'nullable|integer']));
        return response()->json(['message' => 'Posisi diperbarui.']);
    }

    public function destroy(OrgPosition $orgPosition): JsonResponse
    {
        $this->authorizeGovernance(request());
        $orgPosition->delete();
        return response()->json(['message' => 'Posisi dihapus.']);
    }

    public function assignFunction(Request $request): JsonResponse
    {
        $this->authorizeGovernance($request);
        $validated = $request->validate([
            'node_position_id' => 'required|exists:org_node_positions,id',
            'function_id' => 'required|exists:org_governance_functions,id',
        ]);
        $pf = OrgPositionFunction::create($validated);
        return response()->json(['message' => 'Fungsi ditambahkan ke posisi.', 'data' => $pf], 201);
    }

    public function removeFunction(OrgPositionFunction $positionFunction): JsonResponse
    {
        $this->authorizeGovernance(request());
        $positionFunction->delete();
        return response()->json(['message' => 'Fungsi dihapus dari posisi.']);
    }
}
