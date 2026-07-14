<?php

namespace App\Services\Dashboard\Composer;

use App\Models\OperasiPenugasan; // Or whatever mission model is appropriate

class PublicMissionListComposer
{
    public function compose(): array
    {
        // For now, simulate an empty mission list or fetch from appropriate model
        $missionCards = [];
        
        if (class_exists('App\Models\OperasiPenugasan')) {
            $missions = \App\Models\OperasiPenugasan::query()
                ->aktif()
                ->with(['insiden', 'relawan'])
                ->latest()
                ->take(10)
                ->get();
                
            foreach ($missions as $mission) {
                $missionCards[] = [
                    'type' => 'Card',
                    'props' => [
                        'title' => 'Misi #' . $mission->id,
                        'description' => 'Penugasan Relawan - Status: ' . $mission->status
                    ]
                ];
            }
        }

        if (empty($missionCards)) {
            $missionCards[] = [
                'type' => 'Container',
                'props' => ['padding' => [32, 16, 32, 16]],
                'children' => [
                    ['type' => 'Text', 'props' => ['text' => 'Tidak ada misi aktif saat ini.', 'style' => 'body']]
                ]
            ];
        }

        return [
            'schema_version' => '1.0',
            'type' => 'ListView',
            'props' => ['padding' => 16, 'spacing' => 12],
            'children' => $missionCards
        ];
    }
}
