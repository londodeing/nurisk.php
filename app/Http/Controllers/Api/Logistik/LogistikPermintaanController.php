<?php

namespace App\Http\Controllers\Api\Logistik;

use App\Http\Controllers\Controller;
use App\Models\LogistikPermintaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogistikPermintaanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LogistikPermintaan::class);
        $items = LogistikPermintaan::with(['posaju', 'detail.barangKatalog'])
            ->when($request->status, fn($q, $v) => $q->where('status_permintaan', $v))
            ->when($request->id_posaju, fn($q, $v) => $q->where('id_posaju_tujuan', $v))
            ->orderBy('dibuat_pada', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', LogistikPermintaan::class);

        $validated = $request->validate([
            'id_posaju_tujuan' => 'required|exists:operasi_posaju,id_posaju',
            'barang' => 'required|array|min:1',
            'barang.*.id_katalog' => 'required|exists:logistik_barang_katalog,id_katalog',
            'barang.*.jumlah_diminta' => 'required|integer|min:1',
        ]);

        $permintaan = LogistikPermintaan::create([
            'id_posaju_tujuan' => $validated['id_posaju_tujuan'],
            'status_permintaan' => 'pending',
        ]);

        foreach ($validated['barang'] as $b) {
            $permintaan->detail()->create([
                'id_katalog' => $b['id_katalog'],
                'jumlah_diminta' => $b['jumlah_diminta'],
            ]);
        }

        return response()->json(['message' => 'Permintaan diajukan.', 'data' => ['id' => $permintaan->id_permintaan]], 201);
    }

    public function show(LogistikPermintaan $permintaan): JsonResponse
    {
        $this->authorize('view', $permintaan);
        $permintaan->load(['posaju', 'detail.barangKatalog']);
        return response()->json(['data' => $permintaan]);
    }

    public function proses(Request $request, LogistikPermintaan $permintaan): JsonResponse
    {
        $this->authorize('update', LogistikPermintaan::class);
        $validated = $request->validate([
            'status' => 'required|in:diproses,dikirim,selesai,ditolak',
        ]);
        $permintaan->update([
            'status_permintaan' => $validated['status'],
        ]);
        return response()->json(['message' => 'Status permintaan diperbarui.']);
    }
}
