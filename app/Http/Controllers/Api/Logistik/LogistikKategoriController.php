<?php

namespace App\Http\Controllers\Api\Logistik;

use App\Http\Controllers\Controller;
use App\Models\LogistikKategori;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LogistikKategoriController extends Controller
{
    public function index(): JsonResponse
    {
        $kategori = LogistikKategori::orderBy('nama_kategori', 'asc')->get();
        return response()->json(['success' => true, 'data' => $kategori]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manageMasterData', LogistikKategori::class);
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:logistik_kategori,nama_kategori'
        ]);

        $kategori = LogistikKategori::create($validated);
        return response()->json(['success' => true, 'data' => $kategori], 201);
    }

    public function show($id): JsonResponse
    {
        $kategori = LogistikKategori::findOrFail($id);
        return response()->json(['success' => true, 'data' => $kategori]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $this->authorize('manageMasterData', LogistikKategori::class);
        $kategori = LogistikKategori::findOrFail($id);

        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:logistik_kategori,nama_kategori,' . $id . ',id_kategori'
        ]);

        $kategori->update($validated);
        return response()->json(['success' => true, 'data' => $kategori]);
    }

    public function destroy($id): JsonResponse
    {
        $this->authorize('manageMasterData', LogistikKategori::class);
        $kategori = LogistikKategori::findOrFail($id);
        
        if (\App\Models\LogistikBarangKatalog::where('id_kategori', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Kategori sedang digunakan di katalog'], 422);
        }

        $kategori->delete();
        return response()->json(['success' => true, 'message' => 'Kategori dihapus']);
    }
}
