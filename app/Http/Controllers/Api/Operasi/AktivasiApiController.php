<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiAktivasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AktivasiApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OperasiAktivasi::class);

        $items = OperasiAktivasi::with(['insiden:id_insiden,kode_kejadian', 'komandan:id_pengguna'])
            ->when($request->id_insiden, fn($q, $v) => $q->where('id_insiden', $v))
            ->when($request->status_darurat, fn($q, $v) => $q->where('status_darurat', $v))
            ->orderBy('id_aktivasi', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $items->map(fn($a) => [
                'id' => $a->id_aktivasi,
                'id_insiden' => $a->id_insiden,
                'kode_insiden' => $a->insiden?->kode_kejadian,
                'komandan' => $a->komandan?->nama,
                'status_darurat' => $a->status_darurat,
                'waktu_mulai' => $a->waktu_mulai?->toIso8601String(),
                'waktu_selesai' => $a->waktu_selesai?->toIso8601String(),
            ]),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', OperasiAktivasi::class);

        $validated = $request->validate([
            'id_insiden' => 'required|exists:operasi_insiden,id_insiden',
            'id_komandan' => 'required|exists:auth_users,id_pengguna',
            'status_darurat' => 'required|in:siaga,waspada,aktif',
            'waktu_mulai' => 'required|date',
        ]);

        $aktivasi = OperasiAktivasi::create($validated);

        return response()->json(['message' => 'Aktivasi tercatat.', 'data' => ['id' => $aktivasi->id_aktivasi]], 201);
    }

    public function show(OperasiAktivasi $aktivasi): JsonResponse
    {
        $this->authorize('view', $aktivasi);
        $aktivasi->load(['insiden', 'komandan']);
        return response()->json(['data' => $aktivasi]);
    }

    public function update(Request $request, OperasiAktivasi $aktivasi): JsonResponse
    {
        $this->authorize('update', $aktivasi);

        $validated = $request->validate([
            'id_komandan' => 'sometimes|exists:auth_users,id_pengguna',
            'status_darurat' => 'sometimes|in:siaga,waspada,aktif',
            'waktu_mulai' => 'sometimes|date',
        ]);

        $aktivasi->update($validated);
        return response()->json(['message' => 'Aktivasi diperbarui.']);
    }

    public function selesai(OperasiAktivasi $aktivasi): JsonResponse
    {
        $this->authorize('update', $aktivasi);
        $aktivasi->update(['waktu_selesai' => now(), 'status_darurat' => 'aktif']);
        return response()->json(['message' => 'Aktivasi selesai.']);
    }

    public function destroy(OperasiAktivasi $aktivasi): JsonResponse
    {
        $this->authorize('delete', $aktivasi);
        $aktivasi->delete();
        return response()->json(['message' => 'Aktivasi dihapus.']);
    }
}
