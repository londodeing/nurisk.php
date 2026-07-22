<?php

namespace App\Services\Projection;

use App\Models\OperasiInsiden;
use App\Models\OperationalObject;
use Illuminate\Support\Facades\Log;

class IncidentProjectionService
{
    /**
     * Project a single incident into the operational_objects read model.
     */
    public function projectIncident(OperasiInsiden $incident): void
    {
        try {
            // Eager load necessary relationships for the projection
            $incident->loadMissing(['laporanAsal', 'jenisBencana', 'pcnu', 'assessments', 'riwayatStatus']);

            $objectId = 'INC-' . $incident->id_insiden;
            
            $status = strtoupper($incident->status_insiden);
            
            // Map status to colors and icons
            $color = match ($status) {
                'DRAFT' => '#9CA3AF',
                'TERVERIFIKASI' => '#F97316',
                'RESPON' => '#EF4444',
                'PEMULIHAN' => '#3B82F6',
                'SELESAI' => '#22C55E',
                'DIBATALKAN' => '#9CA3AF',
                default => '#9CA3AF'
            };

            // Get coordinate from original report
            $latitude = null;
            $longitude = null;
            if ($incident->laporanAsal) {
                $latitude = $incident->laporanAsal->latitude;
                $longitude = $incident->laporanAsal->longitude;
            }

            // Build Timeline JSON
            $timeline = [];
            foreach ($incident->riwayatStatus as $riwayat) {
                $timeline[] = [
                    'time' => $riwayat->created_at?->toIso8601String() ?? now()->toIso8601String(),
                    'status' => strtoupper($riwayat->status_baru),
                    'title' => 'Status diubah menjadi ' . $riwayat->status_baru,
                ];
            }

            // Build Popup JSON
            $popup = [
                'header' => 'Insiden: ' . ($incident->jenisBencana->nama_jenis ?? 'Bencana'),
                'summary' => $incident->laporanAsal->deskripsi ?? 'Tidak ada deskripsi',
                'status' => $status,
                'assessment_count' => $incident->assessments->count(),
                'buttons' => ['view_details', 'update_status']
            ];

            // Upsert the Operational Object
            OperationalObject::updateOrCreate(
                ['id' => $objectId],
                [
                    'object_type' => 'incident',
                    'status' => $status,
                    'title' => ($incident->jenisBencana->nama_jenis ?? 'Bencana') . ' - ' . ($incident->pcnu->nama_pcnu ?? ''),
                    'summary' => $incident->laporanAsal->deskripsi ?? '',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'icon' => 'local_fire_department',
                    'color' => $color,
                    'priority' => 50,
                    'popup_json' => $popup,
                    'timeline_json' => $timeline,
                    'dashboard_json' => [],
                    'permissions' => [],
                    'refresh_interval' => 60,
                ]
            );

            Log::info("Projected Incident [{$objectId}] to OperationalObject successfully.");

        } catch (\Exception $e) {
            Log::error("Failed to project incident [INC-{$incident->id_insiden}]: " . $e->getMessage());
        }
    }

    /**
     * Delete projection if incident is soft-deleted
     */
    public function removeProjection(int $incidentId): void
    {
        OperationalObject::where('id', 'INC-' . $incidentId)->delete();
    }
}
