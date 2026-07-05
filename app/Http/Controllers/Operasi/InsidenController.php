<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operasi\StoreInsidenRequest;
use App\Http\Requests\Operasi\UpdateInsidenRequest;
use App\Models\BencanaMasterJenis;
use App\Models\OperasiJurnal;
use App\Models\OperasiPleno;
use App\Models\OrganisasiPcnu;
use App\Models\OperasiInsiden;
use App\Services\InsidenService;
use Illuminate\Http\Request;

class InsidenController extends Controller
{
    public function __construct(private InsidenService $insidenService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', OperasiInsiden::class);

        $query = $this->insidenService->queryByScope()
                      ->with(['jenisBencana', 'pcnu'])
                      ->latest('dibuat_pada');

        // Filter opsional
        if ($request->filled('status')) {
            $query->where('status_insiden', $request->status);
        }
        if ($request->filled('prioritas')) {
            $query->where('prioritas', $request->prioritas);
        }

        $insideni = $query->paginate(15)->withQueryString();
        $jenisBencana = BencanaMasterJenis::orderBy('nama_bencana')->get();

        return view('operasi.insiden.index', compact('insideni', 'jenisBencana'));
    }

    public function create()
    {
        $this->authorize('create', OperasiInsiden::class);
        $jenisBencana = BencanaMasterJenis::orderBy('nama_bencana')->get();
        $pcnuList = OrganisasiPcnu::orderBy('nama_pcnu')->get();
        return view('operasi.insiden.create', compact('jenisBencana', 'pcnuList'));
    }

    public function store(StoreInsidenRequest $request)
    {
        $insiden = $this->insidenService->buatInsiden($request->validated());
        return redirect()->route('insiden.show', $insiden)
                         ->with('success', 'Insiden berhasil dicatat.');
    }

    public function show(OperasiInsiden $insiden)
    {
        $this->authorize('view', $insiden);
        $insiden->loadMissing([
            'jenisBencana', 'pcnu',
            'riwayatStatus.pengguna.profil',
            'laporanAsal',
            'posaju',
            'assessments.petugas.profil',
            'penugasan.pengguna.profil',
            'penerimaSpk.profil',
        ]);

        // Ambil daftar TRC di PCNU yang sama
        $trcList = \App\Models\AuthUser::where('default_scope_type', 'pcnu')
            ->where('default_scope_id', $insiden->id_pcnu)
            ->whereHas('peran', function($q) {
                $q->where('nama_peran', 'like', '%trc%')
                  ->orWhere('nama_peran', 'like', '%relawan%');
            })
            ->with(['profil', 'peran'])
            ->get();

        // Pleno & Jurnal untuk tab (moved from view inline queries)
        $plenoList = OperasiPleno::where('id_insiden', $insiden->id_insiden)
            ->with(['pimpinan.profil'])
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        $jurnalList = OperasiJurnal::where('id_insiden', $insiden->id_insiden)
            ->with(['pengguna.profil'])
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        return view('operasi.insiden.show', compact('insiden', 'trcList', 'plenoList', 'jurnalList'));
    }

    public function edit(OperasiInsiden $insiden)
    {
        $this->authorize('update', $insiden);
        $jenisBencana = BencanaMasterJenis::orderBy('nama_bencana')->get();
        return view('operasi.insiden.edit', compact('insiden', 'jenisBencana'));
    }

    public function update(UpdateInsidenRequest $request, OperasiInsiden $insiden)
    {
        try {
            $this->insidenService->updateInsiden($insiden, $request->validated());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('insiden.show', $insiden)
                         ->with('success', 'Data insiden berhasil diperbarui.');
    }

    public function destroy(OperasiInsiden $insiden)
    {
        $this->authorize('delete', $insiden);
        $this->insidenService->hapusInsiden($insiden);
        return redirect()->route('insiden.index')
                         ->with('success', 'Insiden berhasil dihapus.');
    }
}
