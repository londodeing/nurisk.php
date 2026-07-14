<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Governance\GovernanceAuthorization;
use App\Models\OrgInstitution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgInstitutionController extends Controller
{
    use GovernanceAuthorization;

    public function index(): JsonResponse
    {
        return response()->json(['data' => OrgInstitution::all()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeGovernance($request);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'domain' => 'nullable|string|max:50',
        ]);
        return response()->json(['message' => 'Institusi dibuat.', 'data' => OrgInstitution::create($validated)], 201);
    }

    public function update(Request $request, OrgInstitution $orgInstitution): JsonResponse
    {
        $this->authorizeGovernance($request);
        $orgInstitution->update($request->validate(['name' => 'sometimes|string|max:100', 'domain' => 'nullable|string|max:50']));
        return response()->json(['message' => 'Institusi diperbarui.']);
    }

    public function destroy(OrgInstitution $orgInstitution): JsonResponse
    {
        $this->authorizeGovernance(request());
        $orgInstitution->delete();
        return response()->json(['message' => 'Institusi dihapus.']);
    }
}
