<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Models\OperasiPlenoPeserta;
use Illuminate\Http\Request;

class PlenoPesertaController extends Controller
{
    public function store(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno)
    {
        $this->authorize('update', $pleno);

        $validated = $request->validate([
            'id_pengguna' => 'required|exists:auth_users,id_pengguna',
            'peran_dalam_rapat' => 'required|string|max:50',
            'status_kehadiran' => 'required|in:hadir,absen,izin',
            'hak_suara' => 'boolean',
        ]);

        $exists = OperasiPlenoPeserta::where('id_pleno', $pleno->id_pleno)
            ->where('id_pengguna', $validated['id_pengguna'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Peserta tersebut sudah ditambahkan sebelumnya.');
        }

        $validated['id_pleno'] = $pleno->id_pleno;
        $validated['hak_suara'] = $request->has('hak_suara');

        OperasiPlenoPeserta::create($validated);

        return back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function destroy(OperasiInsiden $insiden, OperasiPleno $pleno, OperasiPlenoPeserta $peserta)
    {
        $this->authorize('update', $pleno);

        if ($peserta->id_pleno !== $pleno->id_pleno) {
            return back()->with('error', 'Data tidak valid.');
        }

        if ($peserta->peran_dalam_rapat === 'pimpinan') {
            return back()->with('error', 'Pimpinan rapat tidak dapat dihapus.');
        }

        $peserta->delete();

        return back()->with('success', 'Peserta berhasil dihapus.');
    }
}
