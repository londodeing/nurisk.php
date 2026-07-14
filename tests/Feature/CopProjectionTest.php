<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Dashboard\Projection\IncidentProjectionService;
use App\Services\Dashboard\Projection\MissionProjectionService;
use App\Services\Dashboard\Projection\ResourceProjectionService;
use App\Services\Dashboard\Projection\WeatherProjectionService;
use App\Services\Dashboard\Projection\AnalyticsProjectionService;

class CopProjectionTest extends TestCase
{
    public function test_incident_projection_returns_array()
    {
        $service = new IncidentProjectionService();
        $incidents = $service->getActiveIncidents();
        $this->assertIsArray($incidents);
    }

    public function test_mission_projection_returns_array()
    {
        $service = new MissionProjectionService();
        $missions = $service->getActiveMissions();
        $this->assertIsArray($missions);
    }

    public function test_resource_projection_returns_structure()
    {
        $service = new ResourceProjectionService();
        $resources = $service->getStandbyResources();
        $this->assertArrayHasKey('personnel', $resources);
        $this->assertArrayHasKey('logistics', $resources);
    }

    public function test_weather_projection_returns_structure()
    {
        $service = new WeatherProjectionService();
        $feeds = $service->getWeatherFeeds();
        $this->assertArrayHasKey('location', $feeds);
        $this->assertArrayHasKey('warnings', $feeds);
    }

    public function test_analytics_projection_returns_structure()
    {
        $service = new AnalyticsProjectionService();
        $analytics = $service->getAnalyticsAggregations();
        $this->assertArrayHasKey('total_incidents_active', $analytics);
        $this->assertArrayHasKey('personnel_mobilized', $analytics);
    }
}
