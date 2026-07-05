<?php

namespace App\Http\Controllers\Api\Logistik;

use App\Http\Controllers\Controller;
use App\Models\LogistikStok;
use App\Models\LogistikMutasi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LogistikStokController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Parameter opsional untuk filter
        $idPcnu = $request->query('id_pcnu');

        $query = LogistikStok::with(['katalog.kategori', 'gudang']);
        
        if ($idPcnu) {
            $query->whereHas('gudang', function ($q) use ($idPcnu) {
                $q->where('id_pcnu', $idPcnu);
            });
        }

        $stok = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $stok->items(),
            'meta' => [
                'current_page' => $stok->currentPage(),
                'last_page' => $stok->lastPage(),
                'total' => $stok->total()
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $stok = LogistikStok::with(['katalog.kategori', 'gudang'])->find($id);

        if (!$stok) {
            return response()->json(['success' => false, 'message' => 'Stok tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $stok
        ]);
    }

    public function koreksi(Request $request, $id): JsonResponse
    {
        $stok = LogistikStok::findOrFail($id);
        $this->authorize('mutasiKeluar', $stok);

        $validated = $request->validate([
            'jumlah_tersedia' => 'required|integer|min:0',
            'keterangan' => 'nullable|string'
        ]);

        $jumlahLama = $stok->jumlah_tersedia;
        $jumlahBaru = $validated['jumlah_tersedia'];
        $selisih = $jumlahBaru - $jumlahLama;

        if ($selisih !== 0) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($stok, $jumlahLama, $jumlahBaru, $selisih, $request) {
                $stok->update(['jumlah_tersedia' => $jumlahBaru]);

                LogistikMutasi::create([
                    'id_stok' => $stok->id_stok,
                    'tipe_mutasi' => 'penyesuaian',
                    'jumlah' => $selisih,
                    'asal_tujuan' => 'Koreksi Manual',
                    'keterangan' => $request->input('keterangan', 'Koreksi stok manual'),
                    'id_penginput' => $request->user()->id_pengguna
                ]);
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil dikoreksi',
            'data' => $stok->fresh()
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $idStok = $request->query('id_stok');
        
        $query = LogistikMutasi::with(['stok.katalog', 'pelaku']);

        if ($idStok) {
            $query->where('id_stok', $idStok);
        }

        $mutasi = $query->orderBy('dibuat_pada', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $mutasi->items(),
            'meta' => [
                'current_page' => $mutasi->currentPage(),
                'last_page' => $mutasi->lastPage(),
                'total' => $mutasi->total()
            ]
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $idPcnu = $request->query('id_pcnu');
        $query = LogistikStok::query();

        if ($idPcnu) {
            $query->whereHas('gudang', function ($q) use ($idPcnu) {
                $q->where('id_pcnu', $idPcnu);
            });
        }

        $totalTersedia = (int) $query->sum('jumlah_tersedia');
        $totalAlokasi = (int) $query->sum('jumlah_dialokasikan');

        return response()->json([
            'success' => true,
            'data' => [
                'total_item_tersedia' => $totalTersedia,
                'total_item_dialokasikan' => $totalAlokasi,
                'total_keseluruhan' => $totalTersedia + $totalAlokasi
            ]
        ]);
    }
}
