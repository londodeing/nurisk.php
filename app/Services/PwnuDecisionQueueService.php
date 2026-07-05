<?php

namespace App\Services;

use App\Models\OperasiInsiden;
use App\Models\OperasiPosaju;
use App\Models\LogistikStok;
use App\Models\OperasiSitrep;

class PwnuDecisionQueueService
{
    public function getPwnuQueue()
    {
        $queue = [];

        // 1. Posko Kritis > 3 lokasi (Critical)
        $poskoKritis = OperasiPosaju::where('status_alur', 'kritis')->count();
        if ($poskoKritis > 3) {
            $queue[] = [
                'priority' => 'critical',
                'title' => "Terdapat {$poskoKritis} Posko Kritis secara serentak di wilayah Banten",
                'action_url' => '#'
            ];
        }

        // 2. Insiden Prioritas Kritis (Critical)
        $insidenKritis = OperasiInsiden::aktif()->where('prioritas', 'kritis')->count();
        if ($insidenKritis > 0) {
            $queue[] = [
                'priority' => 'critical',
                'title' => "{$insidenKritis} Insiden diklasifikasikan sebagai Bencana Prioritas Kritis",
                'action_url' => '#'
            ];
        }

        // 3. Stok Bantuan Provinsi Hampir Habis (High)
        // Misal id_gudang = 1 (Gudang PWNU)
        $stokProvinsiKritis = LogistikStok::where('jumlah_tersedia', '<=', 100)->count();
        if ($stokProvinsiKritis > 0) {
            $queue[] = [
                'priority' => 'high',
                'title' => "{$stokProvinsiKritis} jenis stok penyangga (buffer stock) PWNU hampir habis",
                'action_url' => '#'
            ];
        }

        // 4. Permintaan Bantuan Eskalasi Belum Diproses (High)
        $eskalasiPending = \App\Models\OperasiEskalasi::count();
        if ($eskalasiPending > 0) {
            $queue[] = [
                'priority' => 'high',
                'title' => "Terdapat {$eskalasiPending} permintaan BKO (Eskalasi PCNU) menunggu persetujuan Anda",
                'action_url' => '#'
            ];
        }

        // 5. Cabang Tanpa Sitrep > 24 Jam (Medium)
        $poskos = OperasiPosaju::where('status_alur', 'aktif')->get();
        $poskoTanpaSitrep = 0;
        foreach ($poskos as $posko) {
            $sitrep = \App\Models\OperasiSitrep::where('id_insiden', $posko->id_insiden)->latest('dibuat_pada')->first();
            if (!$sitrep || $sitrep->dibuat_pada < now()->subHours(24)) {
                $poskoTanpaSitrep++;
            }
        }
        
        if ($poskoTanpaSitrep > 0) {
            $queue[] = [
                'priority' => 'medium',
                'title' => "Terdapat {$poskoTanpaSitrep} Posko yang belum mengirimkan Sitrep dalam 24 jam terakhir",
                'action_url' => '#'
            ];
        }

        return collect($queue)->sortBy(function ($item) {
            $weights = ['critical' => 1, 'high' => 2, 'medium' => 3];
            return $weights[$item['priority']] ?? 4;
        })->take(5)->values()->toArray();
    }
}
