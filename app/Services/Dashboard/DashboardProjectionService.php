<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use App\Models\AssessmentUtama;
use App\Models\Assessment\AssessmentKebutuhanNumerik;

class DashboardProjectionService
{
    public function getActiveIncidents(): array
    {
        return Cache::remember('projection_active_incidents', 300, function () {
            return OperasiInsiden::query()
                ->whereIn('status_insiden', ['respon', 'pemulihan'])
                ->whereNull('dihapus_pada')
                ->with([
                    'jenisBencana:id_jenis,nama_bencana,slug,ikon_map',
                    'pcnu:id_pcnu,nama_pcnu',
                    'laporanAsal:id_laporan_kejadian,latitude,longitude,keterangan_situasi',
                ])
                ->get()
                ->map(function ($i) {
                    return [
                        'id' => $i->id_insiden,
                        'title' => $i->kode_kejadian . ' - ' . ($i->jenisBencana?->nama_bencana ?? 'Bencana'),
                        'description' => $i->laporanAsal?->keterangan_situasi,
                        'status' => $i->status_insiden,
                    ];
                })->toArray();
        });
    }

    public function getGlobalKpi(): array
    {
        return Cache::remember('projection_global_kpi', 300, function () {
            $totalInsiden = OperasiInsiden::whereIn('status_insiden', ['respon', 'pemulihan'])->count();
            $totalPersonel = OperasiPenugasan::aktif()->count();
            
            return [
                'total_insiden' => $totalInsiden,
                'total_personel' => $totalPersonel,
                'kebutuhan_gap' => 0, // Placeholder
                'korban_terdampak' => 0, // Placeholder
            ];
        });
    }

    public function getWarnings(): array
    {
        return [
            [
                'id' => '1',
                'source' => 'BMKG',
                'title' => 'Peringatan Banjir',
                'description' => 'Siaga banjir pesisir utara.',
                'severity' => 'warning',
            ]
        ];
    }
}
