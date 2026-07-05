<?php

namespace App\Services\CommandCenter;

use App\Models\AuthUser;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPleno;
use App\Models\OperasiPosaju;
use App\Models\OperasiSitrep;
use App\Models\OperasiSuratKeluar;
use App\Models\OperasiTugas;
use App\Models\RelawanKebutuhan;
use App\Services\Auth\AuthorizationContextService;

class DecisionQueueService
{
    public function __construct(
        private AuthorizationContextService $authCtx,
    ) {}

    public function getQueue(AuthUser $user): array
    {
        $role = $user->peran?->nama_peran;

        return match ($role) {
            'super_admin', 'pwnu' => $this->forPwnu($user),
            'pcnu' => $this->forPcnu($user),
            'relawan' => $this->forRelawan($user),
            default => [],
        };
    }

    private function forPwnu(AuthUser $user): array
    {
        $pcnuIds = $this->authCtx->getAccessiblePcnuIds();
        $queue = [];

        $surat = OperasiSuratKeluar::where('status_surat', 'siap_tanda_tangan')
            ->when($pcnuIds !== null, fn($q) => $q->whereIn('id_pcnu', $pcnuIds))
            ->latest('dibuat_pada')
            ->take(3)
            ->get();

        foreach ($surat as $s) {
            $queue[] = [
                'severity' => 'critical',
                'kategori' => 'surat',
                'judul' => 'Surat menunggu tanda tangan',
                'deskripsi' => $s->perihal ?? 'Surat #' . $s->id_surat,
                'waktu' => $s->dibuat_pada,
                'tautan' => url('/surat/' . $s->id_surat),
                'aksi_tersedia' => [
                    ['label' => 'Review', 'route' => url('/surat/' . $s->id_surat . '/edit'), 'method' => 'GET', 'icon' => 'bi-eye', 'color' => 'primary'],
                ],
            ];
        }

        $pleno = OperasiPleno::where('status_pleno', 'ditinjau')
            ->when($pcnuIds !== null, fn($q) => $q->whereHas('insiden', fn($qq) => $qq->whereIn('id_pcnu', $pcnuIds)))
            ->latest('dibuat_pada')
            ->take(3)
            ->get();

        foreach ($pleno as $p) {
            $queue[] = [
                'severity' => 'critical',
                'kategori' => 'pleno',
                'judul' => 'Pleno menunggu finalisasi',
                'deskripsi' => 'Pleno #' . $p->id_pleno . ' — ' . ($p->jenis_pleno ?? ''),
                'waktu' => $p->dibuat_pada,
                'tautan' => url('/insiden/' . $p->id_insiden . '/pleno/' . $p->id_pleno),
                'aksi_tersedia' => [
                    ['label' => 'Finalisasi', 'route' => url('/insiden/' . $p->id_insiden . '/pleno/' . $p->id_pleno . '/finalisasi'), 'method' => 'PATCH', 'icon' => 'bi-check2-square', 'color' => 'success'],
                ],
            ];
        }

        return $queue;
    }

    private function forPcnu(AuthUser $user): array
    {
        $pcnuId = $this->authCtx->getScopeId();
        $queue = [];

        $sitrepOverdue = OperasiSitrep::whereHas('insiden', fn($q) => $q->where('id_pcnu', $pcnuId)->aktif())
            ->selectRaw('id_insiden, MAX(waktu_sitrep) as last_sitrep')
            ->groupBy('id_insiden')
            ->having('last_sitrep', '<', now()->subHours(12))
            ->get();

        foreach ($sitrepOverdue as $s) {
            $queue[] = [
                'severity' => 'critical',
                'kategori' => 'sitrep',
                'judul' => 'Sitrep overdue',
                'deskripsi' => 'Insiden #' . $s->id_insiden . ' — ' . now()->diffInHours($s->last_sitrep) . ' jam tanpa update',
                'waktu' => $s->last_sitrep,
                'tautan' => url('/insiden/' . $s->id_insiden . '/sitrep/create'),
                'aksi_tersedia' => [
                    ['label' => 'Buat Sitrep', 'route' => url('/insiden/' . $s->id_insiden . '/assessment/create'), 'method' => 'GET', 'icon' => 'bi-file-earmark-text', 'color' => 'primary'],
                ],
            ];
        }

        $poskoWithoutPj = OperasiPosaju::whereHas('insiden', fn($q) => $q->where('id_pcnu', $pcnuId))
            ->whereNull('pj_posaju')
            ->whereNull('waktu_ditutup')
            ->get();

        foreach ($poskoWithoutPj as $p) {
            $queue[] = [
                'severity' => 'high',
                'kategori' => 'posko',
                'judul' => 'Posko tanpa penanggung jawab',
                'deskripsi' => $p->nama_posaju . ' belum memiliki PJ',
                'waktu' => $p->dibuat_pada,
                'tautan' => url('/insiden/' . $p->id_insiden . '/posaju/' . $p->id_posaju . '/edit'),
                'aksi_tersedia' => [
                    ['label' => 'Assign PJ', 'route' => url('/insiden/' . $p->id_insiden . '/posaju/' . $p->id_posaju . '/edit'), 'method' => 'GET', 'icon' => 'bi-person-plus', 'color' => 'info'],
                ],
            ];
        }

        return $queue;
    }

    private function forRelawan(AuthUser $user): array
    {
        $queue = [];

        $tugasBaru = OperasiTugas::where('ditugaskan_ke', $user->id_pengguna)
            ->where('status_tugas', 'rencana')
            ->latest('dibuat_pada')
            ->get();

        foreach ($tugasBaru as $t) {
            $queue[] = [
                'severity' => 'critical',
                'kategori' => 'tugas',
                'judul' => 'Tugas baru: ' . $t->judul_tugas,
                'deskripsi' => $t->target_indikator ?? 'Belum ada target',
                'waktu' => $t->dibuat_pada,
                'tautan' => '#',
                'aksi_tersedia' => [
                    ['label' => 'Mulai', 'route' => '#', 'method' => 'POST', 'icon' => 'bi-play-circle', 'color' => 'success'],
                ],
            ];
        }

        return $queue;
    }
}
