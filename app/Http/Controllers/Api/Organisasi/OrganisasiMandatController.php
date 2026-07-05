<?php

namespace App\Http\Controllers\Api\Organisasi;

use App\Http\Controllers\Controller;
use App\Models\OrganisasiMandat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisasiMandatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OrganisasiMandat::class);
        $items = OrganisasiMandat::with(['jabatan', 'pengguna.profil'])
            ->when($request->user_id, fn($q, $v) => $q->where('user_id', $v))
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', OrganisasiMandat::class);
        $validated = $request->validate([
            'user_id' => 'required|exists:auth_users,id_pengguna',
            'jabatan_id' => 'required|exists:organisasi_jabatan,id',
            'sk_id' => 'nullable|exists:organisasi_sk,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'nullable|date|after:tanggal_mulai',
            'organisasi_id' => 'nullable|integer',
        ]);
        $mandat = OrganisasiMandat::create($validated);
        return response()->json(['message' => 'Mandat dibuat.', 'data' => ['id' => $mandat->id]], 201);
    }

    public function show(OrganisasiMandat $mandat): JsonResponse
    {
        $this->authorize('view', $mandat);
        $mandat->load(['jabatan', 'pengguna.profil', 'sk']);
        return response()->json(['data' => $mandat]);
    }

    public function update(Request $request, OrganisasiMandat $mandat): JsonResponse
    {
        $this->authorize('update', $mandat);
        $validated = $request->validate([
            'tanggal_berakhir' => 'nullable|date|after:tanggal_mulai',
        ]);
        $mandat->update($validated);
        return response()->json(['message' => 'Mandat diperbarui.']);
    }

    public function destroy(OrganisasiMandat $mandat): JsonResponse
    {
        $this->authorize('delete', $mandat);
        $mandat->delete();
        return response()->json(['message' => 'Mandat dihapus.']);
    }
}
