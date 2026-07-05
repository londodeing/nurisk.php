<?php

namespace App\Http\Controllers\Api\Relawan;

use App\Http\Controllers\Controller;
use App\Models\RelawanSertifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelawanSertifikasiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', RelawanSertifikasi::class);
        $items = RelawanSertifikasi::with(['pengguna.profil', 'sertifikasi'])
            ->when($request->id_pengguna, fn($q, $v) => $q->where('id_pengguna', $v))
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', RelawanSertifikasi::class);
        $validated = $request->validate([
            'id_pengguna' => 'required|exists:auth_users,id_pengguna',
            'id_sertifikasi' => 'required|exists:master_sertifikasi,id_sertifikasi',
            'tanggal_terbit' => 'required|date',
            'tanggal_kedaluwarsa' => 'nullable|date|after:tanggal_terbit',
        ]);
        RelawanSertifikasi::create($validated);
        return response()->json(['message' => 'Sertifikasi tercatat.'], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'id_pengguna' => 'required|exists:auth_users,id_pengguna',
            'id_sertifikasi' => 'required|exists:master_sertifikasi,id_sertifikasi',
        ]);
        RelawanSertifikasi::where('id_pengguna', $request->id_pengguna)
            ->where('id_sertifikasi', $request->id_sertifikasi)
            ->delete();
        return response()->json(['message' => 'Sertifikasi dihapus.']);
    }
}
