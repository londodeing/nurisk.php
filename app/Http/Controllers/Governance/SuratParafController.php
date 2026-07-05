<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Governance\ParafSuratRequest;
use App\Http\Requests\Governance\TambahParafRequest;
use App\Models\DokumenSuratParaf;
use App\Models\DokumenSuratUtama;
use App\Services\SuratService;

class SuratParafController extends Controller
{
    public function __construct(private SuratService $suratService) {}

    public function store(TambahParafRequest $request, DokumenSuratUtama $surat)
    {
        $this->authorize('update', $surat);

        try {
            $this->suratService->tambahParaf(
                $surat,
                (int) $request->id_pengguna,
                (int) $request->urutan
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Paraf berhasil ditambahkan.');
    }

    public function update(ParafSuratRequest $request, DokumenSuratUtama $surat, DokumenSuratParaf $paraf)
    {
        $this->authorize('paraf', $paraf);

        try {
            $this->suratService->prosesParaf(
                $surat,
                $paraf,
                $request->validated('status_paraf'),
                $request->validated('catatan'),
                $request->user()
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Paraf berhasil diproses.');
    }
}
