<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Models\OrgInstitution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgInstitutionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => OrgInstitution::all()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'domain' => 'nullable|string|max:50',
        ]);
        return response()->json(['message' => 'Institusi dibuat.', 'data' => OrgInstitution::create($validated)], 201);
    }

    public function update(Request $request, OrgInstitution $orgInstitution): JsonResponse
    {
        $orgInstitution->update($request->validate(['name' => 'sometimes|string|max:100', 'domain' => 'nullable|string|max:50']));
        return response()->json(['message' => 'Institusi diperbarui.']);
    }

    public function destroy(OrgInstitution $orgInstitution): JsonResponse
    {
        $orgInstitution->delete();
        return response()->json(['message' => 'Institusi dihapus.']);
    }
}
