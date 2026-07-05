<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Governance\StoreEskalasiRequest;
use App\Models\OperasiEskalasi;
use App\Models\OperasiInsiden;
use App\Services\PlanoService;

class EskalasiController extends Controller
{
    public function __construct(private PlanoService $planoService) {}

    public function store(StoreEskalasiRequest $request, OperasiInsiden $insiden)
    {
        $this->authorize('create', [OperasiEskalasi::class, $insiden]);

        try {
            $this->planoService->eskalasiInsiden($insiden, $request->validated(), $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Eskalasi berhasil dicatat.');
    }
}
