<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Models\OperasiKlaster;
use App\Models\OperasiInsiden;
use App\Models\MasterKlaster;
use App\Services\Operasi\KlasterService;
use App\Services\Operasi\OperasiKlasterService;
use Illuminate\Http\Request;

class KlasterWebController extends Controller
{
    public function __construct(
        private KlasterService $klasterService,
        private OperasiKlasterService $operasiKlasterService
    ) {}

    public function index(Request $request)
    {
        $klasters = OperasiKlaster::with(['masterKlaster', 'insiden'])
            ->when($request->status, fn($q, $v) => $q->where('status_klaster', $v))
            ->paginate(15);

        return view('operasi.klaster.index', compact('klasters'));
    }

    public function create()
    {
        $insidenList = OperasiInsiden::where('is_locked', false)
            ->orderBy('dibuat_pada', 'desc')
            ->get(['id_insiden', 'kode_kejadian']);

        $masterKlasters = MasterKlaster::where('is_aktif', true)->get();

        return view('operasi.klaster.create', compact('insidenList', 'masterKlasters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_insiden'        => ['required', 'integer', 'exists:operasi_insiden,id_insiden'],
            'id_master_klaster' => ['required', 'integer', 'exists:master_klaster,id_master_klaster'],
            'target_cakupan'    => ['nullable', 'string', 'max:500'],
            'catatan'           => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $klaster = $this->klasterService->createKlaster($validated);
            return redirect()->route('klaster.index')
                ->with('success', 'Klaster berhasil diaktifkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function show(OperasiKlaster $klaster)
    {
        $klaster->load(['masterKlaster', 'insiden', 'pembuat.profil']);
        return view('operasi.klaster.show', compact('klaster'));
    }

    public function updateProgress(Request $request, OperasiKlaster $klaster)
    {
        $validated = $request->validate([
            'progres_persen' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $this->operasiKlasterService->updateProgress($klaster, $validated['progres_persen']);

        return back()->with('success', 'Progres klaster diperbarui.');
    }

    public function complete(Request $request, OperasiKlaster $klaster)
    {
        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->klasterService->closeKlaster($klaster, $validated['catatan'] ?? null);
            return back()->with('success', 'Klaster berhasil ditutup.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
