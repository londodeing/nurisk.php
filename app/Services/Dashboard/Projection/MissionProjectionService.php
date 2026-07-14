<?php

namespace App\Services\Dashboard\Projection;

use Illuminate\Support\Facades\Cache;
use App\Models\OperasiPenugasan;

class MissionProjectionService
{
    public function getActiveMissions(): array
    {
        return Cache::remember('projection_active_missions', 60, function () {
            return OperasiPenugasan::query()
                ->where('status_penugasan', 'aktif')
                ->whereNull('dihapus_pada')
                ->with(['insiden', 'pengguna'])
                ->get()
                ->map(function ($m) {
                    return [
                        'id' => $m->id_penugasan,
                        'incident_id' => $m->id_insiden,
                        'title' => 'Misi Penugasan ' . ($m->pengguna?->name ?? 'Tim TRC'),
                        'status' => $m->status_penugasan,
                        'assigned_team' => $m->pengguna?->name ?? 'Tim TRC',
                        'progress_percentage' => 75, // Simplified mock progress
                        'eta' => '15 mins',
                    ];
                })->toArray();
        });
    }
}
