<?php

namespace App\Services\Dashboard\Composer;

use App\Models\OperasiInsiden;

class PublicIncidentListComposer
{
    public function compose(): array
    {
        $incidents = OperasiInsiden::query()
            ->whereIn('status_insiden', ['respon', 'pemulihan'])
            ->with(['jenisBencana', 'pcnu'])
            ->latest('waktu_mulai')
            ->get();

        $incidentCards = [];
        foreach ($incidents as $incident) {
            $incidentCards[] = [
                'type' => 'Card',
                'actions' => ['on_tap' => ['type' => 'navigate', 'target' => '/incident/' . $incident->id_insiden]],
                'props' => [
                    'title' => $incident->jenisBencana?->nama_bencana ?? 'Bencana',
                    'description' => $incident->lokasi_spesifik . ' - ' . ($incident->pcnu?->nama_pcnu ?? '') . "\nStatus: " . ($incident->status ?? 'Aktif')
                ]
            ];
        }

        if (empty($incidentCards)) {
            $incidentCards[] = [
                'type' => 'Container',
                'props' => ['padding' => [32, 16, 32, 16]],
                'children' => [
                    ['type' => 'Text', 'props' => ['text' => 'Tidak ada insiden aktif saat ini.', 'style' => 'body']]
                ]
            ];
        }

        return [
            'schema_version' => '1.0',
            'type' => 'ListView',
            'props' => ['padding' => 16, 'spacing' => 12],
            'children' => $incidentCards
        ];
    }
}
