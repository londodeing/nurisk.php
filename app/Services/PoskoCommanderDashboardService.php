<?php

namespace App\Services;

class PoskoCommanderDashboardService
{
    protected $decisionQueue;

    public function __construct(PoskoCommanderDecisionQueueService $decisionQueue)
    {
        $this->decisionQueue = $decisionQueue;
    }

    public function getMetrics()
    {
        // For a generic commander scope (or passed poskoId)
        $poskoId = null; // Can be updated if poskoId is injected
        $poskoAktifQuery = \App\Models\OperasiPosaju::where('status_alur', 'aktif');
        if ($poskoId) $poskoAktifQuery->where('id_posaju', $poskoId);
        
        $personelQuery = \App\Models\OperasiPenugasan::where('status_penugasan', 'aktif');
        if ($poskoId) $personelQuery->where('id_posaju', $poskoId);
        
        $logistikQuery = \App\Models\LogistikStok::where('jumlah_tersedia', '<=', 50);
        if ($poskoId) $logistikQuery->where('id_posaju', $poskoId);

        $eskalasiQuery = \App\Models\OperasiEskalasi::query();
        // if ($poskoId) $eskalasiQuery->whereHas('insiden', ...);

        return [
            'posko_aktif' => $poskoAktifQuery->count(),
            'personel_bertugas' => $personelQuery->count(),
            'logistik_kritis' => $logistikQuery->count(),
            'eskalasi_aktif' => $eskalasiQuery->count(),
        ];
    }

    public function getAlerts()
    {
        $alerts = [];
        $logistikKritis = \App\Models\LogistikStok::where('jumlah_tersedia', '<=', 20)->count();
        if ($logistikKritis > 0) {
            $alerts[] = ['level' => 'critical', 'message' => "Stok {$logistikKritis} jenis logistik sangat kritis.", 'badge' => 'danger'];
        }

        $sitrepToday = \App\Models\OperasiSitrep::whereDate('dibuat_pada', today())->count();
        if ($sitrepToday === 0) {
            $alerts[] = ['level' => 'high', 'message' => 'Sitrep shift ini belum dikirim.', 'badge' => 'warning'];
        }

        $personelAktif = \App\Models\OperasiPenugasan::where('status_penugasan', 'aktif')->count();
        if ($personelAktif < 10) {
            $alerts[] = ['level' => 'medium', 'message' => 'Personel TRC kurang dari standar.', 'badge' => 'warning text-dark'];
        }

        return $alerts;
    }

    public function getResources()
    {
        $totalPersonel = \App\Models\OperasiPenugasan::whereIn('status_penugasan', ['ditugaskan', 'aktif', 'selesai'])->count();
        $usedPersonel = \App\Models\OperasiPenugasan::where('status_penugasan', 'aktif')->count();
        $personelPercent = $totalPersonel > 0 ? round(($usedPersonel / max(1, $totalPersonel)) * 100) : 0;

        $totalLogistik = \App\Models\LogistikStok::sum('jumlah_tersedia');
        $usedLogistik = 0; // Requires Mutasi table to calculate properly, defaulting to 0 for now
        $logistikPercent = 0;

        return [
            'personel' => ['used' => $usedPersonel, 'total' => max($totalPersonel, $usedPersonel), 'percent' => $personelPercent],
            'logistik' => ['used' => $usedLogistik, 'total' => max($totalLogistik, $usedLogistik), 'percent' => $logistikPercent],
            'posko' => ['status' => 'Operasional', 'percent' => 100]
        ];
    }

    public function getEscalations()
    {
        $eskalasi = \App\Models\OperasiEskalasi::take(5)->get();
        $escalations = [];
        foreach ($eskalasi as $e) {
            $escalations[] = [
                'id' => $e->id_eskalasi,
                'summary' => \Illuminate\Support\Str::limit($e->alasan_eskalasi, 40),
                'severity' => 'Tinggi',
                'time' => 'Terbaru',
                'badge' => 'danger'
            ];
        }
        return $escalations;
    }

    public function getPollingData()
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'kpi' => $this->getMetrics(),
            'alerts' => $this->getAlerts(),
            'decision_queue' => $this->decisionQueue->getCommanderQueue(),
            'resources' => $this->getResources(),
            'escalations' => $this->getEscalations()
        ];
    }
}
