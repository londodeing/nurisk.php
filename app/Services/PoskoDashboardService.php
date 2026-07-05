<?php

namespace App\Services;

use App\Models\OperasiInsiden;
use App\Models\OperasiPosaju;
use App\Models\OperasiPenugasan;
use App\Models\OperasiSitrep;
use App\Models\LogistikStok;

class PoskoDashboardService
{
    protected $decisionQueue;

    public function __construct(DecisionQueueService $decisionQueue)
    {
        $this->decisionQueue = $decisionQueue;
    }

    public function getMetrics()
    {
        // 5 Query Database untuk KPI (Teroptimasi)
        return [
            'posko_aktif' => OperasiPosaju::where('status_alur', 'aktif')->count(),
            'relawan_aktif' => OperasiPenugasan::where('status_penugasan', 'aktif')->distinct('id_pengguna')->count('id_pengguna'),
            'tugas_terbuka' => OperasiPenugasan::where('status_penugasan', 'aktif')->count(),
            'sitrep_hari_ini' => OperasiSitrep::whereDate('dibuat_pada', today())->count(),
            'kebutuhan_mendesak' => LogistikStok::where('jumlah_tersedia', '<=', 50)->count(),
        ];
    }

    public function getFeed()
    {
        // Activity Feed: 20 sitrep terbaru sebagai proxy feed
        $sitreps = OperasiSitrep::with('pembuat')->orderBy('dibuat_pada', 'desc')->take(20)->get();
        return $sitreps->map(function($s) {
            $nama = $s->pembuat ? ($s->pembuat->profil?->nama_lengkap ?? $s->pembuat->no_hp ?? 'Anonim') : 'Anonim';
            return [
                'waktu' => $s->dibuat_pada ? $s->dibuat_pada->format('H:i') : '-',
                'pesan' => "Sitrep dilaporkan oleh {$nama}",
                'tipe' => 'sitrep'
            ];
        });
    }

    public function getPollingData()
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'metrics' => $this->getMetrics(),
            'decision_queue' => $this->decisionQueue->getPoskoQueue(),
            'feed' => $this->getFeed()
        ];
    }
}
