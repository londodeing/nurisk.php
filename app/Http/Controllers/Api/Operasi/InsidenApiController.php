<?php

namespace App\Http\Controllers\Api\Operasi;

use App\Http\Controllers\Controller;
use App\Models\LogistikStok;
use App\Models\LogistikMutasi;
use App\Models\LogistikPermintaan;
use App\Models\OperasiPosaju;
use App\Models\OperasiInsiden;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Http\JsonResponse;

class InsidenApiController extends Controller
{
    public function summary(AuthorizationContextService $ctx): JsonResponse
    {
        $scopePcnuId = $ctx->hasRole('pcnu') ? $ctx->getScopeId() : null;

        $insidenAktifIds = OperasiInsiden::whereIn('status_insiden', ['respon', 'pemulihan'])
            ->whereNull('dihapus_pada')
            ->when($scopePcnuId, fn($q) => $q->where('id_pcnu', $scopePcnuId))
            ->pluck('id_insiden');

        $posajuIds = OperasiPosaju::whereIn('id_insiden', $insidenAktifIds)
            ->where('status_alur', 'aktif')
            ->pluck('id_posaju');

        $stokIds = LogistikStok::whereIn('id_posaju', $posajuIds)->pluck('id_stok');

        return response()->json([
            'total_posaju'       => $posajuIds->count(),
            'total_stok'         => $stokIds->count(),
            'total_mutasi'       => LogistikMutasi::whereIn('id_stok', $stokIds)->count(),
            'permintaan_pending' => LogistikPermintaan::where('status_permintaan', 'pending')
                ->whereIn('id_posaju_tujuan', $posajuIds)
                ->count(),
        ]);
    }
}
