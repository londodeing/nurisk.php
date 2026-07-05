<?php

namespace App\Http\Controllers\Api\Organisasi;

use App\Http\Controllers\Controller;
use App\Models\OrganisasiJabatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisasiJabatanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganisasiJabatan::class);
        $items = OrganisasiJabatan::with(['jabatanMaster'])
            ->when($request->organisasi_id, fn($q, $v) => $q->where('organisasi_id', $v))
            ->paginate($request->get('per_page', 50));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', OrganisasiJabatan::class);
        $validated = $request->validate([
            'organisasi_id' => 'nullable|integer',
            'jabatan_master_id' => 'required|exists:master_jabatan,id_jabatan_posisi',
        ]);
        $jabatan = OrganisasiJabatan::create($validated);
        return response()->json(['message' => 'Jabatan tersimpan.', 'data' => ['id' => $jabatan->id]], 201);
    }

    public function show(OrganisasiJabatan $jabatan): JsonResponse
    {
        $this->authorize('view', $jabatan);
        $jabatan->load(['jabatanMaster']);
        return response()->json(['data' => $jabatan]);
    }

    public function update(Request $request, OrganisasiJabatan $jabatan): JsonResponse
    {
        $this->authorize('update', $jabatan);
        $validated = $request->validate([
            'jabatan_master_id' => 'sometimes|exists:master_jabatan,id_jabatan_posisi',
        ]);
        $jabatan->update($validated);
        return response()->json(['message' => 'Jabatan diperbarui.']);
    }

    public function destroy(OrganisasiJabatan $jabatan): JsonResponse
    {
        $this->authorize('delete', $jabatan);
        $jabatan->delete();
        return response()->json(['message' => 'Jabatan dihapus.']);
    }
}
