<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Models\OrgAuthority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgAuthorityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => OrgAuthority::all()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:org_authorities,code',
            'domain' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);
        return response()->json(['message' => 'Authority dibuat.', 'data' => OrgAuthority::create($validated)], 201);
    }

    public function show(OrgAuthority $orgAuthority): JsonResponse
    {
        return response()->json(['data' => $orgAuthority]);
    }

    public function update(Request $request, OrgAuthority $orgAuthority): JsonResponse
    {
        $orgAuthority->update($request->validate([
            'code' => 'sometimes|string|max:50|unique:org_authorities,code,' . $orgAuthority->id,
            'domain' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
        ]));
        return response()->json(['message' => 'Authority diperbarui.']);
    }

    public function destroy(OrgAuthority $orgAuthority): JsonResponse
    {
        $orgAuthority->functions()->detach();
        $orgAuthority->delete();
        return response()->json(['message' => 'Authority dihapus.']);
    }
}
