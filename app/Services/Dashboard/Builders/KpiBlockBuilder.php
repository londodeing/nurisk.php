<?php
namespace App\Services\Dashboard\Builders;
class KpiBlockBuilder implements BlockBuilderInterface {
    public function build(array $block, \App\Services\Dashboard\DashboardProjectionService $projection): array {
        $kpi = $projection->getGlobalKpi();
        return [
            'type' => 'Grid',
            'props' => ['layout' => 'responsiveGrid', 'spacing' => 16],
            'children' => [
                [
                    'type' => 'Card',
                    'props' => ['padding' => [16, 16, 16, 16]],
                    'children' => [
                        ['type' => 'Column', 'props' => ['spacing' => 8, 'crossAxisAlignment' => 'center'], 'children' => [
                            ['type' => 'Text', 'props' => ['text' => (string) $kpi['total_insiden'], 'style' => 'display']],
                            ['type' => 'Text', 'props' => ['text' => 'Total Insiden', 'style' => 'caption']]
                        ]]
                    ]
                ],
                [
                    'type' => 'Card',
                    'props' => ['padding' => [16, 16, 16, 16]],
                    'children' => [
                        ['type' => 'Column', 'props' => ['spacing' => 8, 'crossAxisAlignment' => 'center'], 'children' => [
                            ['type' => 'Text', 'props' => ['text' => (string) $kpi['total_personel'], 'style' => 'display']],
                            ['type' => 'Text', 'props' => ['text' => 'Personel Aktif', 'style' => 'caption']]
                        ]]
                    ]
                ]
            ]
        ];
    }
}
