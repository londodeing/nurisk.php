<?php

namespace App\Services;

class PoskoCommanderDecisionQueueService
{
    public function getCommanderQueue($poskoId = null)
    {
        $queue = [];

        // 1. Kekurangan Logistik (Critical)
        $logistikQuery = \App\Models\LogistikStok::where('jumlah_tersedia', '<=', 20);
        if ($poskoId) $logistikQuery->where('id_posaju', $poskoId);
        $stokKritis = $logistikQuery->count();
        if ($stokKritis > 0) {
            $queue[] = [
                'priority' => 'critical',
                'title' => "{$stokKritis} jenis logistik dalam kondisi kritis",
                'impact' => 'Operasi berpotensi terhenti.',
                'recommendation' => 'Ajukan eskalasi logistik segera.',
                'action_label' => 'Lihat Logistik',
                'action_url' => '#'
            ];
        }

        // 2. Relawan Tanpa Penugasan / Idle (High)
        // Relawan ditugaskan ke posko ini tapi belum check-in ke tugas, atau status "ready"
        // Kita hitung yang status ketersediaan idle di posko ini.
        $relawanIdle = \App\Models\OperasiPenugasan::where('status_penugasan', 'ditugaskan');
        if ($poskoId) $relawanIdle->where('id_posaju', $poskoId);
        $countIdle = $relawanIdle->count();
        if ($countIdle > 0) {
            $queue[] = [
                'priority' => 'high',
                'title' => "{$countIdle} Relawan belum memiliki penugasan lapangan yang aktif",
                'impact' => 'Kapasitas TRC terbuang sia-sia di lapangan.',
                'recommendation' => 'Tugaskan relawan segera.',
                'action_label' => 'Tugaskan Relawan',
                'action_url' => '#'
            ];
        }

        // 3. Sitrep Telat (High)
        $sitrepQuery = \App\Models\OperasiSitrep::whereDate('dibuat_pada', today());
        if ($poskoId) {
            $posko = \App\Models\OperasiPosaju::find($poskoId);
            if ($posko) $sitrepQuery->where('id_insiden', $posko->id_insiden);
        }
        if ($sitrepQuery->count() === 0) {
            $queue[] = [
                'priority' => 'high',
                'title' => 'Sitrep shift ini belum dikirim',
                'impact' => 'Pusat tidak dapat memantau kondisi terkini.',
                'recommendation' => 'Kirim sitrep segera.',
                'action_label' => 'Buat Sitrep',
                'action_url' => '#'
            ];
        }

        return collect($queue)->sortBy(function ($item) {
            $weights = ['critical' => 1, 'high' => 2, 'medium' => 3];
            return $weights[$item['priority']] ?? 4;
        })->take(5)->values()->toArray();
    }
}
