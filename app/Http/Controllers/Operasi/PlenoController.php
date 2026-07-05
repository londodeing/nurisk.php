<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Services\PlanoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlenoController extends Controller
{
    public function index(OperasiInsiden $insiden)
    {
        $this->authorize('viewAny', [OperasiPleno::class, $insiden]);

        $plenoList = OperasiPleno::where('id_insiden', $insiden->id_insiden)
            ->with(['pimpinan.profil', 'notulis.profil'])
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        return view('operasi.pleno.index', compact('insiden', 'plenoList'));
    }

    public function create(OperasiInsiden $insiden)
    {
        $this->authorize('create', [OperasiPleno::class, $insiden]);

        $pimpinanList = \App\Models\AuthUser::where('status_akun', 'aktif')
            ->with('profil')
            ->get(); // Ideally, filter by Ketua NU Peduli role

        return view('operasi.pleno.create', compact('insiden', 'pimpinanList'));
    }

    public function store(Request $request, OperasiInsiden $insiden)
    {
        $this->authorize('create', [OperasiPleno::class, $insiden]);

        $validated = $request->validate([
            'nomor_pleno' => 'nullable|string|max:100',
            'jenis_pleno' => 'required|string|max:50',
            'waktu_pleno' => 'required|date',
            'lokasi_pleno' => 'nullable|string|max:255',
            'pimpinan_pleno' => 'required|exists:auth_users,id_pengguna',
            'notulis_pleno' => 'nullable|exists:auth_users,id_pengguna',
        ]);

        $validated['id_insiden'] = $insiden->id_insiden;
        $validated['status_pleno'] = 'draft';
        
        if (empty($validated['notulis_pleno'])) {
            $validated['notulis_pleno'] = Auth::id();
        }

        $pleno = app(PlanoService::class)->buatPlano($validated);

        // Auto-add pimpinan to peserta list
        \App\Models\OperasiPlenoPeserta::create([
            'id_pleno' => $pleno->id_pleno,
            'id_pengguna' => $validated['pimpinan_pleno'],
            'peran_dalam_rapat' => 'pimpinan',
            'status_kehadiran' => 'hadir',
            'hak_suara' => true,
        ]);

        return redirect()->route('insiden.pleno.show', [$insiden, $pleno])
            ->with('success', 'Draft Pleno berhasil dibuat.');
    }

    public function show(OperasiInsiden $insiden, OperasiPleno $pleno)
    {
        $this->authorize('view', $pleno);

        $pleno->loadMissing([
            'pimpinan.profil',
            'notulis.profil',
            'peserta.pengguna.profil',
            'keputusan'
        ]);

        $penggunaList = \App\Models\AuthUser::where('status_akun', 'aktif')
            ->with('profil')
            ->get();
            
        $masterKlasterList = \App\Models\MasterKlaster::where('is_aktif', true)->get();

        return view('operasi.pleno.show', compact('insiden', 'pleno', 'penggunaList', 'masterKlasterList'));
    }

    public function finalize(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno)
    {
        $this->authorize('finalize', $pleno);

        if ($pleno->isFinal()) {
            return back()->with('error', 'Pleno sudah difinalisasi sebelumnya.');
        }

        $pleno->update([
            'status_pleno' => 'final',
            'disetujui_oleh' => Auth::id(),
            'waktu_disetujui' => now(),
            'waktu_difinalisasi' => now(),
        ]);

        event(new \App\Events\Operasi\PlenoFinalized($pleno));

        return back()->with('success', 'Pleno berhasil dikunci dan difinalisasi. Automasi telah dijalankan.');
    }
}
