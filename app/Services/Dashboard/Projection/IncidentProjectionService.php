<?php

namespace App\Services\Dashboard\Projection;

use Illuminate\Support\Facades\Cache;
use App\Models\OperasiInsiden;

class IncidentProjectionService
{
    public function getActiveIncidents(): array
    {
        return Cache::remember('projection_active_incidents', 60, function () {
            return OperasiInsiden::query()
                ->whereIn('status_insiden', ['respon', 'pemulihan'])
                ->whereNull('dihapus_pada')
                ->with([
                    'jenisBencana:id_jenis,nama_bencana,slug',
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
                        'latitude' => (float) ($i->laporanAsal?->latitude ?? -7.595),
                        'longitude' => (float) ($i->laporanAsal?->longitude ?? 110.952),
                        'severity' => $i->level_risiko ?? 'medium',
                    ];
                })->toArray();
        });
    }
}
