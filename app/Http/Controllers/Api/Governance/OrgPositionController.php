<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Models\OrgPosition;
use App\Models\OrgPositionFunction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgPositionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => OrgPosition::orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:100', 'level' => 'nullable|integer']);
        return response()->json(['message' => 'Posisi dibuat.', 'data' => OrgPosition::create($validated)], 201);
    }

    public function update(Request $request, OrgPosition $orgPosition): JsonResponse
    {
        $orgPosition->update($request->validate(['name' => 'sometimes|string|max:100', 'level' => 'nullable|integer']));
        return response()->json(['message' => 'Posisi diperbarui.']);
    }

    public function destroy(OrgPosition $orgPosition): JsonResponse
    {
        $orgPosition->delete();
        return response()->json(['message' => 'Posisi dihapus.']);
    }

    public function assignFunction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'node_position_id' => 'required|exists:org_node_positions,id',
            'function_id' => 'required|exists:org_governance_functions,id',
        ]);
        $pf = OrgPositionFunction::create($validated);
        return response()->json(['message' => 'Fungsi ditambahkan ke posisi.', 'data' => $pf], 201);
    }

    public function removeFunction(OrgPositionFunction $positionFunction): JsonResponse
    {
        $positionFunction->delete();
        return response()->json(['message' => 'Fungsi dihapus dari posisi.']);
    }
}
