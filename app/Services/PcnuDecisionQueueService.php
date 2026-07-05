<?php

namespace App\Services;

use App\Models\OperasiPosaju;
use App\Models\OperasiSitrep;
use App\Models\LogistikStok;
use App\Models\OperasiPenugasan;
// use App\Models\OperasiEskalasi; // Asumsi model eskalasi ada

class PcnuDecisionQueueService
{
    public function getPcnuQueue($pcnuId = null)
    {
        $queue = [];

        // 1. Posko Kritis (Critical)
        $poskoKritis = OperasiPosaju::where('status_alur', 'kritis')->count();
        if ($poskoKritis > 0) {
            $queue[] = [
                'priority' => 'critical',
                'title' => "{$poskoKritis} Posko dilaporkan dalam status kritis/lumpuh",
                'action_url' => '#'
            ];
        }

        // 2. Stok Kritis di Wilayah (High)
        $stokKritis = LogistikStok::where('jumlah_tersedia', '<=', 50)->count();
        if ($stokKritis > 0) {
            $queue[] = [
                'priority' => 'high',
                'title' => "Terdapat {$stokKritis} jenis logistik kritis di wilayah ini",
                'action_url' => '#'
            ];
        }

        // 3. Sitrep Terlambat dari Posko (High)
        $poskoAktif = OperasiPosaju::where('status_alur', 'aktif')->get();
        $poskoTelat = 0;
        foreach ($poskoAktif as $posko) {
            $sitrep = \App\Models\OperasiSitrep::where('id_insiden', $posko->id_insiden)->latest('dibuat_pada')->first();
            if (!$sitrep || $sitrep->dibuat_pada < now()->subHours(24)) {
                $poskoTelat++;
            }
        }
        
        if ($poskoTelat > 0) {
            $queue[] = [
                'priority' => 'high',
                'title' => "{$poskoTelat} Posko belum mengirimkan Sitrep harian",
                'action_url' => '#'
            ];
        }

        // 4. Eskalasi Menunggu Keputusan PCNU (Critical)
        $eskalasiMenunggu = \App\Models\OperasiEskalasi::count();
        if ($eskalasiMenunggu > 0) {
            $queue[] = [
                'priority' => 'critical',
                'title' => "{$eskalasiMenunggu} Permintaan BKO / Eskalasi menunggu persetujuan Anda",
                'action_url' => '#'
            ];
        }

        // 5. Kekurangan Relawan di Posko (Medium)
        $poskoKurangRelawan = 0;
        foreach ($poskoAktif as $posko) {
            $relawanAktif = OperasiPenugasan::where('id_posaju', $posko->id_posaju)->where('status_penugasan', 'aktif')->count();
            if ($relawanAktif < 5) {
                $poskoKurangRelawan++;
            }
        }
        
        if ($poskoKurangRelawan > 0) {
            $queue[] = [
                'priority' => 'medium',
                'title' => "Ketimpangan distribusi relawan terdeteksi di {$poskoKurangRelawan} Posko",
                'action_url' => '#'
            ];
        }

        return collect($queue)->sortBy(function ($item) {
            $weights = ['critical' => 1, 'high' => 2, 'medium' => 3];
            return $weights[$item['priority']] ?? 4;
        })->take(5)->values()->toArray();
    }
}
