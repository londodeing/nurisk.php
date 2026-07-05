<?php

namespace App\Http\Controllers\Api\Logistik;

use App\Http\Controllers\Controller;
use App\Models\LogistikBarangKatalog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LogistikKatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $katalog = LogistikBarangKatalog::with('kategori')->orderBy('nama_barang_standar', 'asc')->get();
        return response()->json(['success' => true, 'data' => $katalog]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manageMasterData', LogistikBarangKatalog::class);
        $validated = $request->validate([
            'id_kategori' => 'required|exists:logistik_kategori,id_kategori',
            'id_satuan' => 'required|integer',
            'nama_barang_standar' => 'required|string|max:200|unique:logistik_barang_katalog,nama_barang_standar'
        ]);

        $katalog = LogistikBarangKatalog::create($validated);
        return response()->json(['success' => true, 'data' => $katalog], 201);
    }

    public function show($id): JsonResponse
    {
        $katalog = LogistikBarangKatalog::with('kategori')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $katalog]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $this->authorize('manageMasterData', LogistikBarangKatalog::class);
        $katalog = LogistikBarangKatalog::findOrFail($id);

        $validated = $request->validate([
            'id_kategori' => 'required|exists:logistik_kategori,id_kategori',
            'id_satuan' => 'required|integer',
            'nama_barang_standar' => 'required|string|max:200|unique:logistik_barang_katalog,nama_barang_standar,' . $id . ',id_katalog'
        ]);

        $katalog->update($validated);
        return response()->json(['success' => true, 'data' => $katalog]);
    }

    public function destroy($id): JsonResponse
    {
        $this->authorize('manageMasterData', LogistikBarangKatalog::class);
        $katalog = LogistikBarangKatalog::findOrFail($id);
        
        if (\App\Models\LogistikStok::where('id_katalog', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Barang sedang digunakan di stok'], 422);
        }

        $katalog->delete();
        return response()->json(['success' => true, 'message' => 'Barang dihapus']);
    }
}
