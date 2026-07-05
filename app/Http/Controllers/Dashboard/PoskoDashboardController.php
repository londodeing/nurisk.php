<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPosaju;
use App\Services\Auth\AuthorizationContextService;
use App\Services\CommandCenter\ContactDirectoryService;
use App\Services\CommandCenter\DecisionQueueService;
use App\Services\CommandCenter\QuickActionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PoskoDashboardController extends Controller
{
    public function __construct(
        private AuthorizationContextService $authCtx,
        private DecisionQueueService $decisionQueue,
        private QuickActionService $quickActions,
        private ContactDirectoryService $contacts,
    ) {}

    public function index(): View
    {
        $user = $this->authCtx->getCurrentUser();
        $poskoIds = OperasiPosaju::where('pj_posaju', $user?->id_pengguna)
            ->whereNull('waktu_ditutup')
            ->pluck('id_posaju');

        $role = $user?->peran?->nama_peran;
        $queue = $this->decisionQueue->getQueue($user);
        $actions = $this->quickActions->getActions($user);
        $contacts = $this->contacts->getContacts($user);

        return view('dashboard.posko.dashboard', [
            'role' => $role,
            'pageTitle' => 'POSKO Dashboard',
            'poskoIds' => $poskoIds,
            'queue' => $queue,
            'actions' => $actions,
            'contacts' => $contacts,
            'alerts' => [],
        ]);
    }

    public function summary(Request $request): array
    {
        $user = $this->authCtx->getCurrentUser();
        $poskoIds = OperasiPosaju::where('pj_posaju', $user?->id_pengguna)
            ->whereNull('waktu_ditutup')
            ->pluck('id_posaju');

        $personelCount = OperasiPenugasan::whereHas('insiden.posaju', fn($q) => $q->whereIn('id_posaju', $poskoIds))
            ->where('status_penugasan', 'aktif')
            ->whereNotNull('waktu_checkin')
            ->whereNull('waktu_checkout')
            ->count();

        $tugasAktif = \App\Models\OperasiTugas::whereIn('id_posaju', $poskoIds)
            ->whereIn('status_tugas', ['rencana', 'berjalan'])
            ->count();

        $kebutuhanOpen = \App\Models\RelawanKebutuhan::whereIn('id_posaju', $poskoIds)
            ->where('status_rekrutmen', 'dibuka')
            ->count();

        return [
            'personel' => $personelCount,
            'tugas_aktif' => $tugasAktif,
            'kebutuhan' => $kebutuhanOpen,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function tugas(Request $request): array
    {
        $user = $this->authCtx->getCurrentUser();
        $poskoIds = OperasiPosaju::where('pj_posaju', $user?->id_pengguna)
            ->whereNull('waktu_ditutup')
            ->pluck('id_posaju');

        $tugas = \App\Models\OperasiTugas::whereIn('id_posaju', $poskoIds)
            ->with('pelaksana.profil')
            ->latest('dibuat_pada')
            ->take(20)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id_tugas,
                'judul' => $t->judul_tugas,
                'status' => $t->status_tugas,
                'progres' => $t->progres_persen,
                'pelaksana' => $t->pelaksana?->profil?->nama_lengkap ?? '—',
                'dibuat_pada' => $t->dibuat_pada?->toIso8601String(),
            ]);

        return ['tugas' => $tugas, 'timestamp' => now()->toIso8601String()];
    }

    public function personel(Request $request): array
    {
        $user = $this->authCtx->getCurrentUser();
        $poskoIds = OperasiPosaju::where('pj_posaju', $user?->id_pengguna)
            ->whereNull('waktu_ditutup')
            ->pluck('id_posaju');

        $personel = OperasiPenugasan::whereHas('insiden.posaju', fn($q) => $q->whereIn('id_posaju', $poskoIds))
            ->where('status_penugasan', 'aktif')
            ->with('pengguna.profil')
            ->get()
            ->map(fn($p) => [
                'nama' => $p->pengguna?->profil?->nama_lengkap ?? '—',
                'peran' => $p->peran_otoritas,
                'checkin' => $p->waktu_checkin?->toIso8601String(),
                'checkout' => $p->waktu_checkout?->toIso8601String(),
                'is_hadir' => $p->waktu_checkin !== null && $p->waktu_checkout === null,
            ]);

        return ['personel' => $personel, 'timestamp' => now()->toIso8601String()];
    }
}
