<?php
namespace App\Services\Dashboard\Builders;
class WarningBlockBuilder implements BlockBuilderInterface {
    public function build(array $block, \App\Services\Dashboard\DashboardProjectionService $projection): array {
        $warnings = $projection->getWarnings();
        $items = array_map(function($w) {
            return [
                'type' => 'Row',
                'props' => ['spacing' => 8],
                'children' => [
                    ['type' => 'Icon', 'props' => ['name' => 'warning', 'color' => $w['severity']]],
                    ['type' => 'Text', 'props' => ['text' => $w['title'], 'color' => $w['severity']]]
                ]
            ];
        }, $warnings);

        return [
            'type' => 'Container',
            'props' => ['backgroundColor' => 'warning', 'padding' => [16, 16, 16, 16]],
            'children' => [
                [
                    'type' => 'Column',
                    'props' => ['spacing' => 8],
                    'children' => $items
                ]
            ]
        ];
    }
}
