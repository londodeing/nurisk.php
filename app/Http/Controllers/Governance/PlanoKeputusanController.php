<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Governance\StoreKeputusanRequest;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Services\PlanoService;

class PlanoKeputusanController extends Controller
{
    public function __construct(private PlanoService $planoService) {}

    public function store(StoreKeputusanRequest $request, OperasiInsiden $insiden, OperasiPleno $pleno)
    {
        $this->authorize('tambahKeputusan', $pleno);

        try {
            $this->planoService->tambahKeputusan($pleno, $request->validated());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Keputusan berhasil ditambahkan.');
    }
}
