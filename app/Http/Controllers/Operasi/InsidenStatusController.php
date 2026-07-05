<?php

namespace App\Http\Controllers\Operasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operasi\UbahStatusInsidenRequest;
use App\Models\OperasiInsiden;
use App\Services\InsidenService;

class InsidenStatusController extends Controller
{
    public function __construct(private InsidenService $insidenService) {}

    /**
     * PUT /insiden/{insiden}/status
     */
    public function update(UbahStatusInsidenRequest $request, OperasiInsiden $insiden)
    {
        try {
            $this->insidenService->ubahStatus(
                insiden: $insiden,
                statusBaru: $request->validated('status_baru'),
                pengguna: $request->user(),
                alasan: $request->validated('alasan'),
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Status insiden berhasil diubah menjadi ' . $request->status_baru . '.');
    }
}
