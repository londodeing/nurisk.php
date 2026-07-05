<?php

namespace App\Http\Controllers\Api\Relawan;

use App\Http\Controllers\Controller;
use App\Models\RelawanKebutuhan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelawanKebutuhanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RelawanKebutuhan::class);
        $items = RelawanKebutuhan::with(['posaju'])
            ->when($request->id_posaju, fn($q, $v) => $q->where('id_posaju', $v))
            ->when($request->status_rekrutmen, fn($q, $v) => $q->where('status_rekrutmen', $v))
            ->orderBy('id_relawan_kebutuhan', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', RelawanKebutuhan::class);
        $validated = $request->validate([
            'id_posaju' => 'required|exists:operasi_posaju,id_posaju',
            'judul_posisi' => 'required|string|max:100',
            'jumlah_dibutuhkan' => 'required|integer|min:1',
            'deskripsi_tugas' => 'nullable|string|max:500',
            'persyaratan' => 'nullable|string',
            'tgl_mulai_tugas' => 'nullable|date',
            'tgl_selesai_tugas' => 'nullable|date|after:tgl_mulai_tugas',
        ]);
        $kebutuhan = RelawanKebutuhan::create($validated + ['status_rekrutmen' => 'terbuka']);
        return response()->json(['message' => 'Kebutuhan relawan tercatat.', 'data' => ['id' => $kebutuhan->id_relawan_kebutuhan]], 201);
    }

    public function show(RelawanKebutuhan $kebutuhan): JsonResponse
    {
        $this->authorize('view', $kebutuhan);
        $kebutuhan->load(['posaju']);
        return response()->json(['data' => $kebutuhan]);
    }

    public function update(Request $request, RelawanKebutuhan $kebutuhan): JsonResponse
    {
        $this->authorize('update', $kebutuhan);
        $validated = $request->validate([
            'jumlah_dibutuhkan' => 'sometimes|integer|min:1',
            'deskripsi_tugas' => 'nullable|string|max:500',
            'status_rekrutmen' => 'sometimes|in:terbuka,terisi,ditutup',
            'judul_posisi' => 'sometimes|string|max:100',
            'persyaratan' => 'nullable|string',
        ]);
        $kebutuhan->update($validated);
        return response()->json(['message' => 'Kebutuhan diperbarui.']);
    }

    public function destroy(RelawanKebutuhan $kebutuhan): JsonResponse
    {
        $this->authorize('delete', $kebutuhan);
        $kebutuhan->delete();
        return response()->json(['message' => 'Kebutuhan dihapus.']);
    }
}
