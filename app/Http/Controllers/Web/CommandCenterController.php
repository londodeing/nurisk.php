<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommandCenterController extends Controller
{
    public function index(Request $request)
    {
        // Command center hanya untuk internal — bukan publik
        $this->authorize('viewCommandCenter');

        $ctx  = app(\App\Services\Auth\AuthorizationContextService::class);
        $user = $request->user();

        // Data awal untuk render pertama (SSR) — polling AJAX selanjutnya
        // yang mengupdate ini. SSR mencegah "blank screen" 30 detik pertama.
        $insidenAktif = \App\Models\OperasiInsiden::query()
            ->whereIn('status_insiden', ['respon', 'pemulihan'])
            ->whereNull('dihapus_pada')
            ->with(['jenisBencana', 'pcnu', 'posaju' => fn($q) => $q->where('status_alur', 'aktif')])
            ->when(
                $ctx->hasRole('pcnu') && $ctx->getScopeId(),
                fn($q) => $q->where('id_pcnu', $ctx->getScopeId())
            )
            ->get();

        // Ringkasan KPI awal
        $kpi = $this->hitungKpi($ctx);

        return view('dashboard.command-center', compact('insidenAktif', 'kpi', 'user'));
    }

    private function hitungKpi($ctx): array
    {
        $scopePcnuId = $ctx->hasRole('pcnu') ? $ctx->getScopeId() : null;

        $baseInsiden = \App\Models\OperasiInsiden::whereIn('status_insiden', ['respon', 'pemulihan'])
            ->whereNull('dihapus_pada')
            ->when($scopePcnuId, fn($q) => $q->where('id_pcnu', $scopePcnuId));

        $totalInsiden = $baseInsiden->count();

        $totalPersonel = \App\Models\OperasiPenugasan::aktif()
            ->when($scopePcnuId, fn($q) => $q->whereHas('insiden', fn($i) => $i->where('id_pcnu', $scopePcnuId)))
            ->count();

        $totalPosaju = \App\Models\OperasiPosaju::where('status_alur', 'aktif')
            ->when($scopePcnuId, fn($q) => $q->whereHas('insiden', fn($i) => $i->where('id_pcnu', $scopePcnuId)))
            ->count();

        return [
            'total_insiden'    => $totalInsiden,
            'total_personel'   => $totalPersonel,
            'total_posaju'     => $totalPosaju,
            'stok_kritis_count' => 0, // Diisi oleh modul logistik jika tersedia
        ];
    }
}
