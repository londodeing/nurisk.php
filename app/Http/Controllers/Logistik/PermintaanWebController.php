<?php

namespace App\Http\Controllers\Logistik;

use App\Http\Controllers\Controller;
use App\Models\LogistikPermintaan;
use App\Models\OperasiPosaju;
use Illuminate\Http\Request;

class PermintaanWebController extends Controller
{
    public function index(Request $request)
    {
        $permintaan = LogistikPermintaan::with(['posaju'])
            ->orderBy('dibuat_pada', 'desc')
            ->paginate(15);

        return view('logistik.permintaan.index', compact('permintaan'));
    }

    public function create()
    {
        $posajus = OperasiPosaju::where('status_alur', 'aktif')
            ->orderBy('nama_posaju')
            ->get(['id_posaju', 'nama_posaju']);

        return view('logistik.permintaan.create', compact('posajus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_posaju_tujuan' => ['required', 'integer', 'exists:operasi_posaju,id_posaju'],
            'prioritas'        => ['nullable', 'string', 'in:biasa,mendesak,darurat'],
            'keterangan'       => ['nullable', 'string', 'max:1000'],
        ]);

        $permintaan = LogistikPermintaan::create([
            'id_posaju_tujuan' => $validated['id_posaju_tujuan'],
            'prioritas'        => $validated['prioritas'] ?? 'biasa',
            'status_permintaan' => 'draft',
        ]);

        return redirect()->route('logistik.permintaan.index')
            ->with('success', 'Permintaan logistik berhasil diajukan.');
    }

    public function setujui(LogistikPermintaan $permintaan)
    {
        if (!in_array($permintaan->status_permintaan, ['draft'])) {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $permintaan->update([
            'status_permintaan' => 'diajukan',
        ]);

        return back()->with('success', 'Permintaan disetujui dan diajukan.');
    }
}
