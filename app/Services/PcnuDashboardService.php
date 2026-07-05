<?php

namespace App\Services;

use App\Models\OperasiPosaju;
use App\Models\OperasiPenugasan;
use App\Models\OperasiSitrep;
use App\Models\LogistikStok;

class PcnuDashboardService
{
    protected $decisionQueue;

    public function __construct(PcnuDecisionQueueService $decisionQueue)
    {
        $this->decisionQueue = $decisionQueue;
    }

    public function getMetrics()
    {
        $poskoAktif = OperasiPosaju::where('status_alur', 'aktif')->count();
        $sitrepHariIni = OperasiSitrep::whereDate('dibuat_pada', today())->distinct('id_insiden')->count('id_insiden');
        
        return [
            'posko_aktif' => $poskoAktif,
            'posko_kritis' => OperasiPosaju::where('status_alur', 'kritis')->count(),
            'relawan_aktif' => OperasiPenugasan::where('status_penugasan', 'aktif')->distinct('id_pengguna')->count('id_pengguna'),
            'relawan_tersedia' => 120, // Asumsi 120 idle
            'stok_kritis' => LogistikStok::where('jumlah_tersedia', '<=', 50)->count(),
            'sitrep_terlambat' => max(0, $poskoAktif - $sitrepHariIni),
        ];
    }

    public function getHealthMatrix()
    {
        $poskos = OperasiPosaju::where('status_alur', 'aktif')->get();
        $matrix = [];
        foreach ($poskos as $posko) {
            $sitrepStatus = 'Aman';
            $sitrep = OperasiSitrep::where('id_insiden', $posko->id_insiden)->latest('dibuat_pada')->first();
            if (!$sitrep || $sitrep->dibuat_pada < now()->subHours(24)) {
                $sitrepStatus = 'Terlambat';
            }

            $relawanCukup = OperasiPenugasan::where('id_insiden', $posko->id_insiden)->where('status_penugasan', 'aktif')->count() >= 5;
            $stokKritis = LogistikStok::where('id_posaju', $posko->id_posaju)->where('jumlah_tersedia', '<=', 50)->count() > 0;

            $badge = 'success';
            if ($sitrepStatus === 'Terlambat' || !$relawanCukup || $stokKritis) {
                $badge = 'warning';
                if ($sitrepStatus === 'Terlambat' && $stokKritis) $badge = 'danger';
            }

            $matrix[] = [
                'posko' => $posko->nama_posaju,
                'status' => 'Aktif',
                'sitrep' => $sitrepStatus,
                'relawan' => $relawanCukup ? 'Cukup' : 'Kurang',
                'logistik' => $stokKritis ? 'Kritis' : 'Aman',
                'health_badge' => $badge
            ];
        }
        return $matrix;
    }

    public function getResourceDistribution()
    {
        $poskos = OperasiPosaju::where('status_alur', 'aktif')->get();
        $totalRelawan = OperasiPenugasan::where('status_penugasan', 'aktif')->count();
        $totalLogistik = LogistikStok::sum('jumlah_tersedia');
        
        $distribution = [];
        foreach ($poskos as $posko) {
            $relawanPosko = OperasiPenugasan::where('id_insiden', $posko->id_insiden)->where('status_penugasan', 'aktif')->count();
            $logistikPosko = LogistikStok::where('id_posaju', $posko->id_posaju)->sum('jumlah_tersedia');
            
            $distribution[] = [
                'posko' => $posko->nama_posaju,
                'relawan_percent' => $totalRelawan > 0 ? round(($relawanPosko / $totalRelawan) * 100) : 0,
                'logistik_percent' => $totalLogistik > 0 ? round(($logistikPosko / $totalLogistik) * 100) : 0
            ];
        }
        return $distribution;
    }

    public function getEscalationQueue()
    {
        $eskalasi = \App\Models\OperasiEskalasi::take(5)->get();
        $queue = [];
        foreach ($eskalasi as $item) {
            $queue[] = [
                'id' => $item->id_eskalasi,
                'type' => 'Permintaan Eskalasi',
                'desc' => 'Eskalasi: ' . \Illuminate\Support\Str::limit($item->alasan_eskalasi, 50),
                'time' => 'Baru saja'
            ];
        }
        return $queue;
    }

    public function getPollingData()
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'kpi' => $this->getMetrics(),
            'decision_queue' => $this->decisionQueue->getPcnuQueue(),
            'health_matrix' => $this->getHealthMatrix(),
            'resource_distribution' => $this->getResourceDistribution(),
            'escalation_queue' => $this->getEscalationQueue()
        ];
    }
}
