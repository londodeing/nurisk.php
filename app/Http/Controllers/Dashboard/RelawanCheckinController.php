<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPosaju;
use App\Services\Auth\AuthorizationContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelawanCheckinController extends Controller
{
    public function __construct(
        private AuthorizationContextService $authCtx,
    ) {}

    public function checkin(Request $request): JsonResponse
    {
        $user = $this->authCtx->getCurrentUser();
        $idPenugasan = $request->input('id_penugasan');

        $penugasan = OperasiPenugasan::where('id_penugasan', $idPenugasan)
            ->where('id_pengguna', $user?->id_pengguna)
            ->where('status_penugasan', 'aktif')
            ->first();

        if (!$penugasan) {
            return response()->json(['success' => false, 'message' => 'Penugasan tidak ditemukan'], 404);
        }

        $penugasan->update([
            'waktu_checkin' => now(),
            'lokasi_checkin' => $request->input('lokasi'),
        ]);

        return response()->json(['success' => true, 'message' => 'Check-in berhasil']);
    }

    public function checkout(Request $request): JsonResponse
    {
        $user = $this->authCtx->getCurrentUser();
        $idPenugasan = $request->input('id_penugasan');

        $penugasan = OperasiPenugasan::where('id_penugasan', $idPenugasan)
            ->where('id_pengguna', $user?->id_pengguna)
            ->where('status_penugasan', 'aktif')
            ->first();

        if (!$penugasan) {
            return response()->json(['success' => false, 'message' => 'Penugasan tidak ditemukan'], 404);
        }

        $penugasan->update([
            'waktu_checkout' => now(),
            'lokasi_checkout' => $request->input('lokasi'),
        ]);

        return response()->json(['success' => true, 'message' => 'Check-out berhasil']);
    }
}
