<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DokumenSuratParaf;
use App\Models\DokumenSuratUtama;
use App\Models\OperasiPleno;
use App\Services\GovernanceApprovalDashboardService;
use App\Services\PlanoService;
use App\Services\SuratService;

class GovernanceApprovalController extends Controller
{
    public function __construct(
        private GovernanceApprovalDashboardService $dashboardService,
        private PlanoService $planoService,
        private SuratService $suratService
    ) {}

    /**
     * GET /governance/approval
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', OperasiPleno::class);

        $user = $request->user();
        $ringkasan = $this->dashboardService->ringkasanApproval($user);

        if ($request->wantsJson()) {
            return response()->json([
                'total_pending'    => $ringkasan['totalPending'],
                'paraf_count'      => $ringkasan['parafMenunggu']->count(),
                'pleno_count'      => $ringkasan['plenoMenunggu']->count(),
                'surat_ttd_count'  => $ringkasan['suratMenungguTtd']->count(),
            ]);
        }

        return view('dashboard.governance-approval', $ringkasan + ['user' => $user]);
    }

    /**
     * PATCH /governance/approval/paraf/{paraf}
     */
    public function prosesParaf(Request $request, DokumenSuratParaf $paraf)
    {
        $this->authorize('paraf', $paraf);

        $validated = $request->validate([
            'status_paraf' => ['required', 'in:disetujui,ditolak'],
            'catatan'      => ['required_if:status_paraf,ditolak', 'nullable', 'string', 'max:500'],
        ]);

        try {
            $this->suratService->prosesParaf(
                parafRecord:  $paraf,
                statusParaf:  $validated['status_paraf'],
                catatan:      $validated['catatan'] ?? null,
                aktor:        $request->user(),
            );
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $statusLabel = $validated['status_paraf'] === 'disetujui' ? 'disetujui' : 'ditolak';

        return response()->json([
            'success' => true,
            'message' => 'Paraf berhasil ' . $statusLabel . '.',
            'redirect' => false,
        ]);
    }

    /**
     * PATCH /governance/approval/pleno/{pleno}/finalisasi
     */
    public function finalisasiPleno(Request $request, OperasiPleno $pleno)
    {
        $this->authorize('finalisasi', $pleno);

        try {
            $this->planoService->finalisasi($pleno, $request->user());
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pleno ' . $pleno->nomor_pleno . ' berhasil difinalisasi.',
        ]);
    }

    /**
     * PATCH /governance/approval/surat/{surat}/tandatangani
     */
    public function tandatanganiSurat(Request $request, DokumenSuratUtama $surat)
    {
        $this->authorize('finalisasi', $surat);

        $request->validate([
            'konfirmasi' => ['required', 'accepted'],
        ]);

        try {
            $this->suratService->finalisasi($surat, $request->user());
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Surat ' . $surat->nomor_surat_resmi . ' berhasil ditandatangani. PDF sedang digenerate.',
        ]);
    }

    /**
     * GET /governance/approval/surat/{surat}/preview
     */
    public function previewSurat(DokumenSuratUtama $surat)
    {
        $this->authorize('view', $surat);
        $surat->load(['jenisSurat', 'penandatangan.profil', 'jabatanTtd', 'paraf.pengguna.profil', 'tembusan', 'insiden']);

        return view('governance.surat._preview', compact('surat'));
    }
}
