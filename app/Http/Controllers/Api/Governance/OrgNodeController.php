<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Governance\GovernanceAuthorization;
use App\Models\OrgNode;
use App\Models\OrgNodePosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgNodeController extends Controller
{
    use GovernanceAuthorization;

    public function index(Request $request): JsonResponse
    {
        $items = OrgNode::with(['institution', 'structureLevel', 'positions'])
            ->when($request->institution_id, fn($q, $v) => $q->where('institution_id', $v))
            ->orderBy('name')
            ->paginate($request->get('per_page', 50));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeGovernance($request);
        $validated = $request->validate([
            'institution_id' => 'required|exists:org_institutions,id',
            'structure_level_id' => 'required|exists:org_structure_levels,id',
            'territory_code' => 'nullable|string|max:20',
            'name' => 'required|string|max:200',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);
        return response()->json(['message' => 'Node dibuat.', 'data' => OrgNode::create($validated)], 201);
    }

    public function show(OrgNode $orgNode): JsonResponse
    {
        $orgNode->load(['institution', 'structureLevel', 'positions', 'assets']);
        return response()->json(['data' => $orgNode]);
    }

    public function update(Request $request, OrgNode $orgNode): JsonResponse
    {
        $this->authorizeGovernance($request);
        $orgNode->update($request->validate(['name' => 'sometimes|string|max:200', 'status' => 'sometimes|in:aktif,nonaktif', 'territory_code' => 'nullable|string|max:20']));
        return response()->json(['message' => 'Node diperbarui.']);
    }

    public function destroy(OrgNode $orgNode): JsonResponse
    {
        $this->authorizeGovernance(request());
        $orgNode->delete();
        return response()->json(['message' => 'Node dihapus.']);
    }

    public function assignPosition(Request $request): JsonResponse
    {
        $this->authorizeGovernance($request);
        $validated = $request->validate([
            'node_id' => 'required|exists:org_nodes,id',
            'position_id' => 'required|exists:org_positions,id',
        ]);
        $np = OrgNodePosition::create($validated);
        return response()->json(['message' => 'Posisi ditambahkan ke node.', 'data' => $np], 201);
    }

    public function removePosition(OrgNodePosition $nodePosition): JsonResponse
    {
        $this->authorizeGovernance(request());
        $nodePosition->delete();
        return response()->json(['message' => 'Posisi dihapus dari node.']);
    }
}
