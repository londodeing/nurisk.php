<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiEskalasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EskalasiApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OperasiEskalasi::class);

        $items = OperasiEskalasi::with(['insiden:id_insiden,kode_kejadian'])
            ->when($request->id_insiden, fn($q, $v) => $q->where('id_insiden', $v))
            ->orderBy('id_eskalasi', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $items->map(fn($e) => [
                'id' => $e->id_eskalasi,
                'id_insiden' => $e->id_insiden,
                'kode_insiden' => $e->insiden?->kode_kejadian,
                'level_sebelumnya' => $e->level_sebelumnya,
                'level_baru' => $e->level_baru,
                'alasan' => $e->alasan_eskalasi,
            ]),
            'meta' => ['total' => $items->total()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', OperasiEskalasi::class);

        $validated = $request->validate([
            'id_insiden' => 'required|exists:operasi_insiden,id_insiden',
            'id_pleno' => 'nullable|exists:operasi_pleno,id_pleno',
            'level_sebelumnya' => 'required|string|max:50',
            'level_baru' => 'required|string|max:50',
            'alasan_eskalasi' => 'required|string|max:500',
        ]);

        $eskalasi = OperasiEskalasi::create($validated);

        return response()->json(['message' => 'Eskalasi tercatat.', 'data' => ['id' => $eskalasi->id_eskalasi]], 201);
    }

    public function show(OperasiEskalasi $eskalasi): JsonResponse
    {
        $this->authorize('view', $eskalasi);
        $eskalasi->load(['insiden', 'pleno']);
        return response()->json(['data' => $eskalasi]);
    }

    public function destroy(OperasiEskalasi $eskalasi): JsonResponse
    {
        $this->authorize('delete', $eskalasi);
        $eskalasi->delete();
        return response()->json(['message' => 'Eskalasi dihapus.']);
    }
}
