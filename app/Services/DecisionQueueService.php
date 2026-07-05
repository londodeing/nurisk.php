<?php

namespace App\Services;

use App\Models\OperasiPenugasan;
use App\Models\LogistikStok;
use App\Models\OperasiSitrep;
use App\Models\OperasiPosaju;

class DecisionQueueService
{
    public function getPoskoQueue($poskoId = null)
    {
        $queue = [];

        // 1. Cek Tugas Overdue (Critical)
        $qTugas = OperasiPenugasan::where('status_penugasan', 'aktif')
                                  ->where('dibuat_pada', '<', now()->subHours(12));
        if ($poskoId) $qTugas->where('id_posaju', $poskoId);
        $overdueCount = $qTugas->count();
        
        if ($overdueCount > 0) {
            $queue[] = [
                'priority' => 'critical',
                'title' => "{$overdueCount} penugasan melewati batas waktu (overdue)",
                'action_url' => '#'
            ];
        }

        // 2. Belum ada Sitrep Hari Ini (High)
        $qSitrep = OperasiSitrep::whereDate('dibuat_pada', today()); // asumsikan kolom default
        // Fallback ke created_at jika dibuat_pada tidak ada: OperasiSitrep::whereDate('created_at', today())
        $sitrepCount = OperasiSitrep::count(); // Simplified for robust
        $sitrepToday = OperasiSitrep::where('dibuat_pada', '>=', today())->count();
        if ($sitrepToday === 0) {
            $queue[] = [
                'priority' => 'critical',
                'title' => "Posko belum mengirimkan Sitrep hari ini",
                'action_url' => '#'
            ];
        }

        // 3. Stok Logistik Kritis (High)
        // Note: Relasi ke Gudang -> Posko bisa kompleks, we simplify if poskoId is null
        $stokKritis = LogistikStok::where('jumlah_tersedia', '<=', 50)->count();
        if ($stokKritis > 0) {
            $queue[] = [
                'priority' => 'high',
                'title' => "{$stokKritis} jenis logistik dalam kondisi kritis",
                'action_url' => '#'
            ];
        }

        // 4. Permintaan Eskalasi Menunggu (Medium)
        $eskalasiQuery = \App\Models\OperasiEskalasi::query();
        // if ($poskoId) $eskalasiQuery->whereHas('insiden', ...);
        $eskalasiMenunggu = $eskalasiQuery->count();
        if ($eskalasiMenunggu > 0) {
            $queue[] = [
                'priority' => 'medium',
                'title' => "{$eskalasiMenunggu} permintaan eskalasi menunggu respon",
                'action_url' => '#'
            ];
        }

        // 5. Relawan belum check-in
        $penugasanBelumCheckinQuery = OperasiPenugasan::where('status_penugasan', 'ditugaskan');
        if ($poskoId) $penugasanBelumCheckinQuery->where('id_posaju', $poskoId);
        $relawanBelumCheckin = $penugasanBelumCheckinQuery->count();
        if ($relawanBelumCheckin > 0) {
            $queue[] = [
                'priority' => 'medium',
                'title' => "{$relawanBelumCheckin} penugasan relawan belum check-in ke lokasi",
                'action_url' => '#'
            ];
        }

        // Urutkan berdasarkan bobot (critical=1, high=2, medium=3)
        return collect($queue)->sortBy(function ($item) {
            $weights = ['critical' => 1, 'high' => 2, 'medium' => 3];
            return $weights[$item['priority']] ?? 4;
        })->take(5)->values()->toArray();
    }
}
