<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\OperasiSuratKeluar;
use App\Models\OperasiPleno;
use App\Services\Auth\AuthorizationContextService;

class ExecutiveGovernanceApiController extends Controller
{
    public function __construct(
        private AuthorizationContextService $authCtx
    ) {}

    public function pendingDecisions(Request $request): JsonResponse
    {
        /** @var \App\Models\AuthUser $user */
        $user = $request->user();
        
        $pcnuIds = $this->authCtx->getAccessiblePcnuIds();
        $isPcnu = $this->authCtx->getScopeType() === 'pcnu';
        $pcnuId = $this->authCtx->getScopeId();

        $surat = OperasiSuratKeluar::where('status_surat', 'siap_tanda_tangan')
            ->when($isPcnu, fn($q) => $q->where('id_pcnu', $pcnuId))
            ->when(!$isPcnu && $pcnuIds !== null, fn($q) => $q->whereIn('id_pcnu', $pcnuIds))
            ->latest('dibuat_pada')
            ->take(10)
            ->get();

        $suratFormatted = $surat->map(function($s) {
            return [
                'id' => $s->id_surat,
                'perihal' => $s->perihal ?? 'Surat #' . $s->id_surat,
                'waktu' => $s->dibuat_pada ? $s->dibuat_pada->toIso8601String() : now()->toIso8601String(),
                'pemohon' => $s->pcnu ? $s->pcnu->nama_pcnu : 'Organisasi'
            ];
        });

        $pleno = OperasiPleno::where('status_pleno', 'ditinjau')
            ->when($isPcnu, fn($q) => $q->whereHas('insiden', fn($qq) => $qq->where('id_pcnu', $pcnuId)))
            ->when(!$isPcnu && $pcnuIds !== null, fn($q) => $q->whereHas('insiden', fn($qq) => $qq->whereIn('id_pcnu', $pcnuIds)))
            ->latest('dibuat_pada')
            ->take(10)
            ->get();

        $plenoFormatted = $pleno->map(function($p) {
            return [
                'id' => $p->id_pleno,
                'judul' => $p->jenis_pleno ?? 'Pleno #' . $p->id_pleno,
                'waktu' => $p->dibuat_pada ? $p->dibuat_pada->toIso8601String() : now()->toIso8601String(),
                'insiden' => $p->insiden ? $p->insiden->nama_insiden : 'Insiden Tidak Diketahui'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'surat' => $suratFormatted,
                'pleno' => $plenoFormatted
            ]
        ]);
    }

    public function processDecision(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:surat,pleno',
            'id' => 'required|string',
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string'
        ]);

        $type = $request->input('type');
        $id = $request->input('id');
        $action = $request->input('action');

        if ($type === 'surat') {
            $surat = OperasiSuratKeluar::findOrFail($id);
            if ($action === 'approve') {
                $surat->status_surat = 'final';
                $surat->ditandatangani_pada = now();
                $surat->save();

                if ($surat->id_insiden) {
                    \App\Models\OperasiPenugasan::where('id_insiden', $surat->id_insiden)
                        ->where('status_penugasan', 'draft')
                        ->update(['status_penugasan' => 'aktif']);
                }
            } else {
                $surat->status_surat = 'revisi';
                $surat->save();
            }
        } elseif ($type === 'pleno') {
            $pleno = OperasiPleno::findOrFail($id);
            if ($action === 'approve') {
                $pleno->status_pleno = 'final';
                $pleno->save();
            } else {
                $pleno->status_pleno = 'revisi';
                $pleno->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Keputusan berhasil diproses'
        ]);
    }
}
