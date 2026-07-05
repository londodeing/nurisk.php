<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPenugasanHistory;
use App\Services\TrcDashboardService;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Http\Requests\Operasi\StoreAssessmentRequest;
use App\Services\Operasi\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;

class TrcDashboardController extends Controller
{
    protected TrcDashboardService $dashboardService;

    public function __construct(TrcDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $initialData = $this->dashboardService->getPollingData();
        return view('dashboard.trc', compact('initialData'));
    }

    public function polling(): JsonResponse
    {
        $start = microtime(true);
        $data  = $this->dashboardService->getPollingData();
        $data['debug'] = ['response_time_ms' => round((microtime(true) - $start) * 1000)];
        return response()->json($data);
    }

    /**
     * Transisi status: assigned → on_route (Berangkat)
     */
    public function mulaiPenugasan(Request $request)
    {
        $user = Auth::user();
        if (!$user) return back()->with('error', 'Unauthorized');

        $penugasan = OperasiPenugasan::where('id_pengguna', $user->id_pengguna)
            ->whereIn('status_penugasan', ['assigned', 'ditugaskan', 'aktif', 'draft'])
            ->latest('dibuat_pada')
            ->first();

        if (!$penugasan) {
            return back()->with('error', 'Tidak ada penugasan aktif untuk dimulai.');
        }

        $statusLama = $penugasan->status_penugasan;
        $penugasan->update(['status_penugasan' => 'on_route']);

        OperasiPenugasanHistory::create([
            'id_penugasan'      => $penugasan->id_penugasan,
            'status_sebelumnya' => $statusLama,
            'status_baru'       => 'on_route',
            'waktu_perubahan'   => now(),
            'diubah_oleh'       => $user->id_pengguna,
        ]);

        return back()->with('success', 'Penugasan dimulai! Selamat bertugas, hati-hati di jalan.');
    }

    /**
     * Transisi status: on_route → on_site (Tiba di Lokasi)
     */
    public function tibaLokasi(Request $request)
    {
        $user = Auth::user();
        if (!$user) return back()->with('error', 'Unauthorized');

        $penugasan = OperasiPenugasan::where('id_pengguna', $user->id_pengguna)
            ->where('status_penugasan', 'on_route')
            ->latest('dibuat_pada')
            ->first();

        if (!$penugasan) {
            return back()->with('error', 'Anda belum dalam status perjalanan (on_route).');
        }

        $statusLama = $penugasan->status_penugasan;
        $penugasan->update([
            'status_penugasan' => 'on_site',
            'waktu_checkin'    => now(),
        ]);

        OperasiPenugasanHistory::create([
            'id_penugasan'      => $penugasan->id_penugasan,
            'status_sebelumnya' => $statusLama,
            'status_baru'       => 'on_site',
            'waktu_perubahan'   => now(),
            'diubah_oleh'       => $user->id_pengguna,
        ]);

        return back()->with('success', 'Status diperbarui: Anda telah tiba di lokasi kejadian.');
    }

    public function assessmentCreate(OperasiInsiden $insiden)
    {
        $this->authorize('create', [AssessmentUtama::class, $insiden]);
        return view('dashboard.trc-assessment', compact('insiden'));
    }

    public function assessmentStore(StoreAssessmentRequest $request, OperasiInsiden $insiden, AssessmentService $assessmentService)
    {
        $this->authorize('create', [AssessmentUtama::class, $insiden]);

        $data = $request->validated();
        $data['id_insiden'] = $insiden->id_insiden;
        $data['id_petugas_assessment'] = auth()->id();

        $assessmentService->createAssessment($data);

        return redirect()->route('dashboard.trc')
            ->with('success', 'Assessment berhasil disimpan. Sitrep otomatis dibuat.');
    }
}
