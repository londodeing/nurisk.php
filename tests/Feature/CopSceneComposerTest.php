<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Dashboard\Composer\COPComposer;
use App\Services\Dashboard\Projection\IncidentProjectionService;
use App\Services\Dashboard\Projection\MissionProjectionService;
use App\Services\Dashboard\Projection\ResourceProjectionService;
use App\Services\Dashboard\Projection\WeatherProjectionService;
use App\Services\Dashboard\Projection\AnalyticsProjectionService;

class CopSceneComposerTest extends TestCase
{
    public function test_composer_outputs_structured_scene()
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
        $this->assertEquals('Scene', $sdui['type']);
        $this->assertArrayHasKey('scene', $sdui);

        $scene = $sdui['scene'];
        
        $this->assertArrayHasKey('camera', $scene);
        $this->assertArrayHasKey('layers', $scene);
        $this->assertArrayHasKey('panels', $scene);

        $this->assertIsArray($scene['layers']);
        
        $hasIncidentLayer = false;
        $hasSpatialLayer = false;

        foreach ($scene['layers'] as $layer) {
            if ($layer['id'] === 'incident_layer') {
                $hasIncidentLayer = true;
                $this->assertArrayHasKey('primitives', $layer);
            }
            if ($layer['id'] === 'spatial_layer') {
                $hasSpatialLayer = true;
                $this->assertArrayHasKey('primitives', $layer);
                $this->assertEquals('Polygon', $layer['primitives'][0]['type']);
                $this->assertEquals('Radius', $layer['primitives'][1]['type']);
            }
        }
        $this->assertTrue($hasIncidentLayer, 'Incident layer should exist in scene.');
        $this->assertTrue($hasSpatialLayer, 'Spatial layer should exist in scene.');
    }
}
