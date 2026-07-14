<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Dashboard\Composer\COPComposer;
use App\Services\Dashboard\Projection\IncidentProjectionService;
use App\Services\Dashboard\Projection\MissionProjectionService;
use App\Services\Dashboard\Projection\ResourceProjectionService;
use App\Services\Dashboard\Projection\WeatherProjectionService;
use App\Services\Dashboard\Projection\AnalyticsProjectionService;

class CopComposerTest extends TestCase
{
    public function test_composer_assembles_sdui_primitives()
    {
        $composer = new COPComposer(
            new IncidentProjectionService(),
            new MissionProjectionService(),
            new ResourceProjectionService(),
            new WeatherProjectionService(),
            new AnalyticsProjectionService()
        );

        $sdui = $composer->compose();

        $this->assertEquals('1.0', $sdui['schema_version']);
        $this->assertEquals('COPDashboard', $sdui['screen']);
        $this->assertNotEmpty($sdui['nodes']);

        // Assert no domain components exist in the layout tree
        foreach ($sdui['nodes'] as $node) {
            $this->assertNotEquals('IncidentCard', $node['type']);
            $this->assertNotEquals('MissionCard', $node['type']);
            $this->assertNotEquals('WeatherCard', $node['type']);
        }
    }
}
