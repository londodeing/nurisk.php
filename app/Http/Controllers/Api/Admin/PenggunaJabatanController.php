<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenggunaJabatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PenggunaJabatanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PenggunaJabatan::class);
        $items = PenggunaJabatan::with(['pengguna.profil', 'jabatan'])
            ->when($request->id_pengguna, fn($q, $v) => $q->where('id_pengguna', $v))
            ->when($request->id_jabatan_posisi, fn($q, $v) => $q->where('id_jabatan_posisi', $v))
            ->when($request->status_aktif, fn($q, $v) => $q->where('status_aktif', $v))
            ->orderBy('ditugaskan_pada', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PenggunaJabatan::class);
        $validated = $request->validate([
            'id_pengguna' => 'required|exists:auth_users,id_pengguna',
            'id_jabatan_posisi' => 'required|exists:master_jabatan,id_jabatan_posisi',
            'tipe_lingkup' => 'required|in:pwnu,pcnu,mwc,ranting,lembaga,banom',
            'id_lingkup' => 'required|integer',
            'berakhir_pada' => 'nullable|date',
        ]);
        $validated['status_aktif'] = true;
        $item = PenggunaJabatan::create($validated);
        return response()->json(['message' => 'Jabatan ditugaskan.', 'data' => ['id' => $item->id_pengguna_jabatan]], 201);
    }

    public function show(PenggunaJabatan $pengguna_jabatan): JsonResponse
    {
        $this->authorize('view', $pengguna_jabatan);
        $pengguna_jabatan->load(['pengguna.profil', 'jabatan']);
        return response()->json(['data' => $pengguna_jabatan]);
    }

    public function update(Request $request, PenggunaJabatan $pengguna_jabatan): JsonResponse
    {
        $this->authorize('update', $pengguna_jabatan);
        $pengguna_jabatan->update($request->validate([
            'tipe_lingkup' => 'sometimes|in:pwnu,pcnu,mwc,ranting,lembaga,banom',
            'id_lingkup' => 'nullable|integer',
            'berakhir_pada' => 'nullable|date',
        ]));
        return response()->json(['message' => 'Jabatan diperbarui.']);
    }

    public function destroy(PenggunaJabatan $pengguna_jabatan): JsonResponse
    {
        $this->authorize('delete', $pengguna_jabatan);
        $pengguna_jabatan->delete();
        return response()->json(['message' => 'Penugasan jabatan dihapus.']);
    }

    public function activate(PenggunaJabatan $pengguna_jabatan): JsonResponse
    {
        $this->authorize('update', $pengguna_jabatan);
        $pengguna_jabatan->update(['status_aktif' => true]);
        return response()->json(['message' => 'Jabatan diaktifkan.']);
    }

    public function deactivate(PenggunaJabatan $pengguna_jabatan): JsonResponse
    {
        $this->authorize('update', $pengguna_jabatan);
        $pengguna_jabatan->update(['status_aktif' => false]);
        return response()->json(['message' => 'Jabatan dinonaktifkan.']);
    }
}
