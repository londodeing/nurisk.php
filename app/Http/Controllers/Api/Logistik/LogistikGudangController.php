<?php

namespace App\Http\Controllers\Api\Logistik;

use App\Http\Controllers\Controller;
use App\Models\LogistikGudang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogistikGudangController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LogistikGudang::class);
        $items = LogistikGudang::with(['pcnu:id_pcnu,nama_pcnu'])
            ->when($request->id_pcnu, fn($q, $v) => $q->where('id_pcnu', $v))
            ->paginate($request->get('per_page', 15));
        return response()->json(['data' => $items, 'meta' => ['total' => $items->total()]]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', LogistikGudang::class);
        $validated = $request->validate([
            'nama_gudang' => 'required|string|max:100',
            'alamat_fisik' => 'nullable|string|max:255',
            'id_pcnu' => 'nullable|exists:organisasi_pcnu,id_pcnu',
            'pj_gudang' => 'nullable|exists:auth_users,id_pengguna',
        ]);
        $gudang = LogistikGudang::create($validated);
        return response()->json(['message' => 'Gudang dibuat.', 'data' => ['id' => $gudang->id_gudang]], 201);
    }

    public function show(LogistikGudang $gudang): JsonResponse
    {
        $this->authorize('view', $gudang);
        $gudang->load(['pcnu', 'stok.barangKatalog']);
        return response()->json(['data' => $gudang]);
    }

    public function update(Request $request, LogistikGudang $gudang): JsonResponse
    {
        $this->authorize('update', LogistikGudang::class);
        $validated = $request->validate([
            'nama_gudang' => 'sometimes|string|max:100',
            'alamat_fisik' => 'nullable|string|max:255',
            'pj_gudang' => 'nullable|exists:auth_users,id_pengguna',
        ]);
        $gudang->update($validated);
        return response()->json(['message' => 'Gudang diperbarui.']);
    }

    public function destroy(LogistikGudang $gudang): JsonResponse
    {
        $this->authorize('delete', LogistikGudang::class);
        $gudang->delete();
        return response()->json(['message' => 'Gudang dihapus.']);
    }
}
