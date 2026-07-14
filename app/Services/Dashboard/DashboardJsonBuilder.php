<?php

namespace App\Services\Dashboard;

class DashboardJsonBuilder
{
    public function build(array $layout, DashboardProjectionService $projection): array
    {
        $nodes = [];
        
        foreach ($layout as $block) {
            $node = ComponentRegistry::build($block, $projection);
            if ($node !== null) {
                $nodes[] = $node;
            }
        }

        return [
            'schema_version' => '1.0',
            'screen' => 'PublicDashboard',
            'layout' => 'vertical',
            'nodes' => $nodes
        ];
    }
}
