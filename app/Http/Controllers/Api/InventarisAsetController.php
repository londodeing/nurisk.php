<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventarisAsetController extends Controller
{
    public function index()
    {
        $asets = \App\Models\Inventaris\InventarisAset::with(['jenis', 'unitPemilik'])->get();
        return response()->json([
            'success' => true,
            'data' => $asets
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_jenis' => 'required|integer',
            'id_unit_pemilik' => 'required|integer',
            'nama_aset' => 'required|string|max:255',
            'kode_inventaris' => 'required|string|max:100|unique:inventaris_aset',
            'tahun_perolehan' => 'nullable|integer',
            'kondisi_terkini' => 'required|string',
            'status_operasional' => 'required|string',
        ]);

        $aset = \App\Models\Inventaris\InventarisAset::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Aset berhasil ditambahkan',
            'data' => $aset
        ]);
    }
}
