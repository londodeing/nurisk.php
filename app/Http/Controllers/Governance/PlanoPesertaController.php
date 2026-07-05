<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Governance\StoreKeputusanRequest;
use App\Http\Requests\Governance\UpdatePesertaVoteRequest;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Models\OperasiPlenoPeserta;
use App\Services\PlanoService;
use Illuminate\Http\Request;

class PlanoPesertaController extends Controller
{
    public function __construct(private PlanoService $planoService) {}

    public function store(Request $request, OperasiInsiden $insiden, OperasiPleno $pleno)
    {
        $this->authorize('tambahPeserta', $pleno);

        $request->validate([
            'id_pengguna' => ['required', 'integer', 'exists:auth_users,id_pengguna'],
            'peran_dalam_rapat' => ['nullable', 'string', 'max:100'],
            'hak_suara' => ['nullable', 'boolean'],
        ]);

        try {
            $this->planoService->tambahPeserta(
                $pleno,
                (int) $request->id_pengguna,
                $request->only(['peran_dalam_rapat', 'hak_suara', 'status_kehadiran'])
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function vote(UpdatePesertaVoteRequest $request, OperasiInsiden $insiden, OperasiPleno $pleno, OperasiPlenoPeserta $peserta)
    {
        try {
            $this->planoService->updateVotePeserta(
                $peserta,
                $request->validated('status_persetujuan'),
                $request->validated('catatan_peserta')
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Vote berhasil dicatat.');
    }
}
