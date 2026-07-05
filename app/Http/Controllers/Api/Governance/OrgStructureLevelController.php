<?php

namespace App\Http\Controllers\Api\Governance;

use App\Http\Controllers\Controller;
use App\Models\OrgStructureLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgStructureLevelController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => OrgStructureLevel::orderBy('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:100', 'weight' => 'required|integer|min:1']);
        return response()->json(['message' => 'Level struktur dibuat.', 'data' => OrgStructureLevel::create($validated)], 201);
    }

    public function update(Request $request, OrgStructureLevel $orgStructureLevel): JsonResponse
    {
        $orgStructureLevel->update($request->validate(['name' => 'sometimes|string|max:100', 'weight' => 'sometimes|integer|min:1']));
        return response()->json(['message' => 'Level diperbarui.']);
    }

    public function destroy(OrgStructureLevel $orgStructureLevel): JsonResponse
    {
        $orgStructureLevel->delete();
        return response()->json(['message' => 'Level dihapus.']);
    }
}
