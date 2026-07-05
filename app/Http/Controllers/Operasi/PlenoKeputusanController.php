<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Models\OperasiPlenoKeputusan;
use Illuminate\Http\Request;

class PlenoKeputusanController extends Controller
{
    public function store(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno)
    {
        $this->authorize('update', $pleno);

        $validated = $request->validate([
            'kategori_objek' => 'required|string|max:50',
            'jenis_keputusan' => 'required|string|max:50',
            'deskripsi_keputusan' => 'required|string',
            'payload' => 'nullable|array',
        ]);

        $tipeTargetMap = [
            'status_insiden' => 'insiden',
            'aktivasi_posko' => 'pos_aju',
            'aktivasi_klaster' => 'klaster',
            'mobilisasi_relawan' => 'personil',
            'eskalasi_wilayah' => 'insiden',
            'logistik' => 'logistik',
            'lainnya' => 'insiden',
        ];

        $kategoriObjek = $validated['kategori_objek'];
        $tipeTarget = $tipeTargetMap[$kategoriObjek] ?? 'insiden';

        OperasiPlenoKeputusan::create([
            'id_pleno' => $pleno->id_pleno,
            'kategori_objek' => $kategoriObjek,
            'jenis_keputusan' => $validated['jenis_keputusan'],
            'tipe_target_keputusan' => $tipeTarget,
            'deskripsi_keputusan' => $validated['deskripsi_keputusan'],
            'payload_eksekusi' => $validated['payload'] ?? null,
            'status_pelaksanaan' => 'rencana',
        ]);

        return back()->with('success', 'Keputusan berhasil ditambahkan.');
    }

    public function destroy(OperasiInsiden $insiden, OperasiPleno $pleno, OperasiPlenoKeputusan $keputusan)
    {
        $this->authorize('update', $pleno);

        if ($keputusan->id_pleno !== $pleno->id_pleno) {
            return back()->with('error', 'Data tidak valid.');
        }

        $keputusan->delete();

        return back()->with('success', 'Keputusan berhasil dihapus.');
    }
}
