<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Governance\StorePlanoRequest;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Models\AuthUser;
use App\Services\PlanoService;
use Illuminate\Http\Request;

class PlanoController extends Controller
{
    public function __construct(private PlanoService $planoService) {}

    public function index(Request $request, OperasiInsiden $insiden)
    {
        $this->authorize('viewAny', OperasiPleno::class);
        $this->authorize('view', $insiden);

        $plenos = OperasiPleno::byInsiden($insiden->id_insiden)
            ->with(['pimpinan.profil', 'peserta'])
            ->latest('dibuat_pada')
            ->paginate(10);

        return view('governance.pleno.index', compact('insiden', 'plenos'));
    }

    public function create(OperasiInsiden $insiden)
    {
        $this->authorize('create', OperasiPleno::class);

        $authUsers = AuthUser::with('profil')->where('status_akun', 'aktif')->orderBy('no_hp')->get();

        return view('governance.pleno.create', compact('insiden', 'authUsers'));
    }

    public function store(StorePlanoRequest $request, OperasiInsiden $insiden)
    {
        $this->authorize('view', $insiden);

        $data = array_merge($request->validated(), ['id_insiden' => $insiden->id_insiden]);
        $pleno = $this->planoService->buatPlano($data);

        return redirect()->route('insiden.pleno.show', [$insiden, $pleno])
            ->with('success', 'Pleno berhasil dibuat.');
    }

    public function show(OperasiInsiden $insiden, OperasiPleno $pleno)
    {
        $this->authorize('view', $pleno);

        $pleno->load(['pimpinan.profil', 'notulis.profil', 'keputusan', 'peserta.pengguna.profil', 'eskalasi']);

        $authUsers = AuthUser::with('profil')->where('status_akun', 'aktif')->orderBy('no_hp')->get();

        return view('governance.pleno.show', compact('insiden', 'pleno', 'authUsers'));
    }

    public function tinjau(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno)
    {
        $this->authorize('tambahKeputusan', $pleno);

        if ($pleno->status_pleno !== 'draft') {
            return back()->with('error', 'Hanya pleno berstatus draft yang dapat ditinjau.');
        }

        $pleno->update(['status_pleno' => 'ditinjau']);

        return back()->with('success', 'Status pleno diubah ke "ditinjau".');
    }

    public function finalisasi(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno)
    {
        $this->authorize('finalisasi', $pleno);

        try {
            $this->planoService->finalisasi($pleno, $request->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pleno berhasil difinalisasi.');
    }
}
