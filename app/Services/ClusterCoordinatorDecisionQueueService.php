<?php

namespace App\Services;

class ClusterCoordinatorDecisionQueueService
{
    public function getQueue()
    {
        // 5 item prioritas maksimal untuk koordinator klaster
        $queue = [
            [
                'priority' => 'critical',
                'title' => 'Kecamatan X belum menerima layanan kesehatan 24 jam',
                'impact' => 'Risiko peningkatan korban.',
                'recommendation' => 'Kirim 2 tenaga medis tambahan.',
                'action_label' => 'Tugaskan Medis',
                'action_url' => '#'
            ],
            [
                'priority' => 'critical',
                'title' => 'Wabah diare meluas di Posko Pengungsian C',
                'impact' => 'Ancaman wabah mematikan bagi balita.',
                'recommendation' => 'Eskalasi ke Dinas Kesehatan setempat.',
                'action_label' => 'Eskalasi Eksternal',
                'action_url' => '#'
            ],
            [
                'priority' => 'high',
                'title' => 'Posko A kekurangan selimut (> 200 jiwa)',
                'impact' => 'Kerentanan hipotermia malam hari.',
                'recommendation' => 'Mutasi dari Posko B (Surplus 300).',
                'action_label' => 'Setujui Mutasi',
                'action_url' => '#'
            ],
            [
                'priority' => 'high',
                'title' => 'Akses Posko D terputus longsor susulan',
                'impact' => 'Distribusi pangan tertahan 12 jam.',
                'recommendation' => 'Koordinasi TRC untuk rute alternatif udara/air.',
                'action_label' => 'Rapat Darurat',
                'action_url' => '#'
            ],
            [
                'priority' => 'medium',
                'title' => 'Kelebihan stok relawan di Posko Induk',
                'impact' => 'Personel tidak produktif (Idle).',
                'recommendation' => 'Sebar relawan ke 3 desa belum terlayani.',
                'action_label' => 'Rotasi Relawan',
                'action_url' => '#'
            ],
        ];

        return collect($queue)->sortBy(function ($item) {
            $weights = ['critical' => 1, 'high' => 2, 'medium' => 3];
            return $weights[$item['priority']] ?? 4;
        })->take(5)->values()->toArray();
    }
}
