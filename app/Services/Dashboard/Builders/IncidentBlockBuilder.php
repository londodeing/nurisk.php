<?php
namespace App\Services\Dashboard\Builders;
class IncidentBlockBuilder implements BlockBuilderInterface {
    public function build(array $block, \App\Services\Dashboard\DashboardProjectionService $projection): array {
        $incidents = $projection->getActiveIncidents();
        $items = array_map(function($i) {
            return [
                'type' => 'Card',
                'props' => ['padding' => [16, 16, 16, 16], 'margin' => [0, 0, 8, 0]],
                'children' => [
                    ['type' => 'Text', 'props' => ['text' => $i['title'], 'style' => 'headline']],
                    ['type' => 'Text', 'props' => ['text' => $i['description'] ?? 'Tidak ada keterangan', 'style' => 'body']]
                ]
            ];
        }, $incidents);

        return [
            'type' => 'List',
            'props' => ['title' => 'Insiden Aktif'],
            'children' => $items
        ];
    }
}
