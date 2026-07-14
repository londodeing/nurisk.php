<?php

namespace App\Services\Dashboard\Composer;

use App\Services\Dashboard\Projection\IncidentProjectionService;
use App\Services\Dashboard\Projection\MissionProjectionService;
use App\Services\Dashboard\Projection\ResourceProjectionService;
use App\Services\Dashboard\Projection\WeatherProjectionService;
use App\Services\Dashboard\Projection\AnalyticsProjectionService;

class COPComposer
{
    protected $incidentService;
    protected $missionService;
    protected $resourceService;
    protected $weatherService;
    protected $analyticsService;

    public function __construct(
        IncidentProjectionService $incidentService,
        MissionProjectionService $missionService,
        ResourceProjectionService $resourceService,
        WeatherProjectionService $weatherService,
        AnalyticsProjectionService $analyticsService
    ) {
        $this->incidentService = $incidentService;
        $this->missionService = $missionService;
        $this->resourceService = $resourceService;
        $this->weatherService = $weatherService;
        $this->analyticsService = $analyticsService;
    }

    public function compose(): array
    {
        $incidents = $this->incidentService->getActiveIncidents();
        $missions = $this->missionService->getActiveMissions();
        $resources = $this->resourceService->getStandbyResources();
        $weather = $this->weatherService->getWeatherFeeds();
        $analytics = $this->analyticsService->getAnalyticsAggregations();

        // 1. Compile Incident Layer
        $incidentPrimitives = [];
        foreach ($incidents as $inc) {
            $incidentPrimitives[] = [
                'type' => 'Marker',
                'props' => [
                    'id' => 'incident_' . $inc['id'],
                    'latitude' => $inc['latitude'],
                    'longitude' => $inc['longitude'],
                    'icon' => 'warning',
                    'color' => $inc['severity'] === 'critical' ? 'danger' : 'warning',
                    'title' => $inc['title']
                ]
            ];
        }

        // 2. Compile Spatial Layer (Polygons & Buffers)
        $spatialPrimitives = [
            [
                'type' => 'Polygon',
                'props' => [
                    'id' => 'evacuation_zone_a',
                    'fillColor' => 'danger',
                    'opacity' => 0.4,
                    'coordinates' => [
                        [-7.59, 110.95],
                        [-7.59, 110.96],
                        [-7.60, 110.96],
                        [-7.60, 110.95]
                    ]
                ]
            ],
            [
                'type' => 'Radius',
                'props' => [
                    'id' => 'blast_radius_1',
                    'center' => [-7.595, 110.952],
                    'radius_km' => 2.5,
                    'fillColor' => 'warning',
                    'opacity' => 0.3
                ]
            ]
        ];

        // 3. Compile Mission Layer
        $missionPrimitives = [];
        // Optional: you can add mission locations or routes here
        
        // 4. Compile Overlay Panels
        $kpiNodes = [
            [
                'type' => 'Card',
                'props' => ['expanded' => true],
                'children' => [
                    ['type' => 'Row', 'props' => ['spacing' => 8, 'crossAxisAlignment' => 'center'], 'children' => [
                        ['type' => 'Icon', 'props' => ['name' => 'warning', 'color' => 'danger']],
                        ['type' => 'Text', 'props' => ['text' => 'Insiden Aktif', 'style' => 'caption']],
                    ]],
                    ['type' => 'Text', 'props' => ['text' => (string) $analytics['total_incidents_active'], 'style' => 'headline', 'color' => 'danger']]
                ]
            ],
            [
                'type' => 'Card',
                'props' => ['expanded' => true],
                'children' => [
                    ['type' => 'Row', 'props' => ['spacing' => 8, 'crossAxisAlignment' => 'center'], 'children' => [
                        ['type' => 'Icon', 'props' => ['name' => 'engineering', 'color' => 'info']],
                        ['type' => 'Text', 'props' => ['text' => 'Misi Berjalan', 'style' => 'caption']],
                    ]],
                    ['type' => 'Text', 'props' => ['text' => (string) $analytics['personnel_mobilized'], 'style' => 'headline', 'color' => 'info']]
                ]
            ]
        ];

        $timelineEvents = [];
        foreach ($weather['warnings'] as $warning) {
            $timelineEvents[] = [
                'time' => 'Baru Saja',
                'description' => '[BMKG] ' . $warning['title'] . ': ' . $warning['description']
            ];
        }
        foreach ($missions as $mis) {
            $timelineEvents[] = [
                'time' => $mis['eta'] . ' ETA',
                'description' => 'Misi: ' . $mis['title'] . ' (' . $mis['assigned_team'] . ')'
            ];
        }

        // Return the structured Scene SDUI Response
        return [
            'schema_version' => '1.0',
            'type' => 'Scene',
            'props' => [
                'scene' => [
                    'camera' => [
                        'center_lat' => -7.595,
                        'center_lng' => 110.952,
                        'zoom' => 12.0,
                        'bearing' => 0.0,
                        'tilt' => 0.0
                    ],
                    'layers' => [
                        [
                            'id' => 'spatial_layer',
                            'type' => 'SpatialLayer',
                            'z_index' => 5,
                            'visible' => true,
                            'opacity' => 1.0,
                            'primitives' => $spatialPrimitives
                        ],
                        [
                            'id' => 'incident_layer',
                            'type' => 'IncidentLayer',
                            'z_index' => 10,
                            'visible' => true,
                            'opacity' => 1.0,
                            'primitives' => $incidentPrimitives
                        ],
                        [
                            'id' => 'mission_layer',
                            'type' => 'MissionLayer',
                            'z_index' => 11,
                            'visible' => true,
                            'opacity' => 1.0,
                            'primitives' => $missionPrimitives
                        ]
                    ],
                    'panels' => [
                        'top_bar' => [
                            'type' => 'Container',
                            'props' => ['padding' => [16, 16, 16, 16]],
                            'children' => [
                                [
                                    'type' => 'Row',
                                    'children' => $kpiNodes
                                ]
                            ]
                        ],
                        'overlay_right' => [
                            'type' => 'Timeline',
                            'props' => [
                                'title' => 'Linimasa Aktivitas Terkini',
                                'items' => $timelineEvents
                            ]
                        ],
                        'bottom_sheet' => [
                            'type' => 'BottomSheet',
                            'props' => ['initial_size' => 0.15],
                            'children' => [
                                ['type' => 'Text', 'props' => ['text' => 'Pilih marker untuk melihat detail', 'style' => 'body']]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
