<?php

namespace App\Services;

use App\Models\OperasiInsiden;
use App\Models\OperasiPosaju;
use App\Models\OperasiPenugasan;
use App\Models\LogistikStok;

class PwnuDashboardService
{
    protected $decisionQueue;

    public function __construct(PwnuDecisionQueueService $decisionQueue)
    {
        $this->decisionQueue = $decisionQueue;
    }

    public function getMetrics()
    {
        return [
            'total_insiden' => OperasiInsiden::aktif()->count(),
            'total_posko' => OperasiPosaju::where('status_alur', 'aktif')->count(),
            'total_relawan' => OperasiPenugasan::where('status_penugasan', 'aktif')->distinct('id_pengguna')->count('id_pengguna'),
            'cabang_terdampak' => OperasiInsiden::aktif()->distinct('id_pcnu')->count('id_pcnu'),
        ];
    }

    public function getCriticalAreas()
    {
        $insidens = OperasiInsiden::aktif()->with('pcnu')->get();
        $critical = [];
        
        foreach($insidens->groupBy('id_pcnu') as $pcnuId => $items) {
            $score = 0;
            foreach($items as $i) {
                if ($i->prioritas === 'kritis') $score += 50;
                elseif ($i->prioritas === 'tinggi') $score += 30;
                elseif ($i->prioritas === 'sedang') $score += 10;
                else $score += 5;
            }
            $cabang = $items->first()->pcnu?->nama_pcnu ?? 'Wilayah Tanpa PCNU';
            
            $status = 'Aman'; $badge = 'success';
            if ($score >= 80) { $status = 'Bahaya'; $badge = 'danger'; }
            elseif ($score >= 40) { $status = 'Siaga'; $badge = 'warning'; }
            
            $critical[] = [
                'cabang' => $cabang,
                'score' => min(100, $score),
                'status' => $status,
                'badge' => $badge
            ];
        }
        
        usort($critical, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($critical, 0, 5);
    }

    public function getTrends()
    {
        $insidenBaru = [];
        $sitrepMasuk = [];
        $relawanAktif = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $insidenBaru[] = OperasiInsiden::whereDate('dibuat_pada', $date)->count();
            $sitrepMasuk[] = \App\Models\OperasiSitrep::whereDate('dibuat_pada', $date)->count();
            $relawanAktif[] = OperasiPenugasan::whereDate('dibuat_pada', $date)->count();
        }

        return [
            'insiden_baru' => $insidenBaru,
            'sitrep_masuk' => $sitrepMasuk,
            'relawan_aktif' => $relawanAktif
        ];
    }

    public function getResourceCapacity()
    {
        $totalRelawan = \App\Models\AuthUser::whereHas('peran', fn($q) => $q->where('nama_peran', 'relawan'))->count();
        $relawanTerpakai = OperasiPenugasan::where('status_penugasan', 'aktif')->distinct('id_pengguna')->count('id_pengguna');
        $relawanPercent = $totalRelawan > 0 ? round(($relawanTerpakai / $totalRelawan) * 100) : 0;

        $totalLogistik = LogistikStok::sum('jumlah_tersedia');
        $logistikTerpakai = 0; // Requires Mutasi table to calculate properly, defaulting to 0 for now
        $logistikPercent = 0;

        $totalPosko = OperasiPosaju::count();
        $poskoTerpakai = OperasiPosaju::where('status_alur', 'aktif')->count();
        $poskoPercent = $totalPosko > 0 ? round(($poskoTerpakai / $totalPosko) * 100) : 0;

        return [
            'relawan' => ['terpakai' => $relawanTerpakai, 'total' => max($totalRelawan, $relawanTerpakai), 'percent' => $relawanPercent],
            'logistik' => ['terpakai' => $logistikTerpakai, 'total' => max($totalLogistik, $logistikTerpakai), 'percent' => $logistikPercent],
            'posko' => ['terpakai' => $poskoTerpakai, 'total' => max($totalPosko, $poskoTerpakai), 'percent' => $poskoPercent]
        ];
    }

    public function getPollingData()
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'kpi' => $this->getMetrics(),
            'decision_queue' => $this->decisionQueue->getPwnuQueue(),
            'critical_areas' => $this->getCriticalAreas(),
            'trends' => $this->getTrends(),
            'resources' => $this->getResourceCapacity()
        ];
    }
}
