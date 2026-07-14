<?php
namespace App\Services\Dashboard\Builders;
class WeatherBlockBuilder implements BlockBuilderInterface {
    public function build(array $block, \App\Services\Dashboard\DashboardProjectionService $projection): array {
        return [
            'type' => 'Card',
            'props' => ['padding' => [16, 16, 16, 16]],
            'children' => [
                [
                    'type' => 'Row',
                    'props' => ['spacing' => 16, 'crossAxisAlignment' => 'center'],
                    'children' => [
                        ['type' => 'Icon', 'props' => ['name' => 'cloud', 'size' => 48, 'color' => 'info']],
                        ['type' => 'Column', 'props' => ['spacing' => 4], 'children' => [
                            ['type' => 'Text', 'props' => ['text' => 'Cerah Berawan', 'style' => 'headline']],
                            ['type' => 'Text', 'props' => ['text' => 'Berdasarkan lokasi', 'style' => 'caption']]
                        ]]
                    ]
                ]
            ]
        ];
    }
}
