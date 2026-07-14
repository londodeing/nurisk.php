<?php

namespace App\Services\Dashboard\Composer;

use App\Services\Dashboard\Projection\IncidentProjectionService;
use App\Services\Dashboard\Projection\WeatherProjectionService;
use App\Services\Dashboard\Projection\AnalyticsProjectionService;
use App\Models\OperasiInsiden;
use App\Models\WeatherSnapshot;

class PublicDashboardComposer
{
    protected $incidentService;
    protected $weatherService;
    protected $analyticsService;

    public function __construct(
        IncidentProjectionService $incidentService,
        WeatherProjectionService $weatherService,
        AnalyticsProjectionService $analyticsService
    ) {
        $this->incidentService = $incidentService;
        $this->weatherService = $weatherService;
        $this->analyticsService = $analyticsService;
    }

    public function compose(): array
    {
        $analytics = $this->analyticsService->getAnalyticsAggregations();
        $weatherData = $this->weatherService->getWeatherFeeds();
        
        $latestIncidents = OperasiInsiden::query()
            ->with(['jenisBencana', 'pcnu'])
            ->latest('waktu_mulai')
            ->take(5)
            ->get();
            
        $weatherSnapshot = WeatherSnapshot::latest()->first();

        // 1. KPI Cards
        $kpiNodes = [
            'type' => 'Row',
            'props' => ['spacing' => 12],
            'children' => [
                [
                    'type' => 'Card',
                    'props' => ['expanded' => true],
                    'actions' => ['on_tap' => ['type' => 'navigate', 'target' => '/incident/list']],
                    'children' => [
                        ['type' => 'Row', 'props' => ['spacing' => 8, 'crossAxisAlignment' => 'center'], 'children' => [
                            ['type' => 'Icon', 'props' => ['name' => 'warning', 'foreground' => 'danger']],
                            ['type' => 'Text', 'props' => ['text' => 'Insiden Aktif', 'style' => 'caption']],
                        ]],
                        ['type' => 'Text', 'props' => ['text' => (string) $analytics['total_incidents_active'], 'style' => 'headline', 'foreground' => 'danger']]
                    ]
                ],
                [
                    'type' => 'Card',
                    'props' => ['expanded' => true],
                    'actions' => ['on_tap' => ['type' => 'navigate', 'target' => '/mission/list']],
                    'children' => [
                        ['type' => 'Row', 'props' => ['spacing' => 8, 'crossAxisAlignment' => 'center'], 'children' => [
                            ['type' => 'Icon', 'props' => ['name' => 'engineering', 'foreground' => 'info']],
                            ['type' => 'Text', 'props' => ['text' => 'Misi Berjalan', 'style' => 'caption']],
                        ]],
                        ['type' => 'Text', 'props' => ['text' => (string) $analytics['personnel_mobilized'], 'style' => 'headline', 'foreground' => 'info']]
                    ]
                ]
            ]
        ];

        // 2. Weather Warnings
        $warningNodes = [];
        if (!empty($weatherData['warnings'])) {
            $warningNodes = [
                'type' => 'Container',
                'props' => ['padding' => ['t' => 16, 'l' => 0, 'b' => 0, 'r' => 0]],
                'children' => [
                    ['type' => 'Text', 'props' => ['text' => 'Peringatan Dini', 'style' => 'subtitle']],
                ]
            ];
            foreach ($weatherData['warnings'] as $warning) {
                $warningNodes['children'][] = [
                    'type' => 'Card',
                    'props' => ['title' => '[BMKG] ' . $warning['title'], 'description' => $warning['description']]
                ];
            }
        }

        // 3. Weather Forecast (Hourly scrollable)
        $hourlyCards = [];
        if ($weatherSnapshot && !empty($weatherSnapshot->hourly_forecast)) {
            foreach (array_slice($weatherSnapshot->hourly_forecast, 0, 12) as $hour) {
                $hourlyCards[] = [
                    'type' => 'Card',
                    'children' => [
                        ['type' => 'Text', 'props' => ['text' => $hour['time'] ?? '', 'style' => 'caption']],
                        ['type' => 'Icon', 'props' => ['name' => 'cloud', 'foreground' => 'info']],
                        ['type' => 'Text', 'props' => ['text' => ($hour['temp'] ?? '') . '°C', 'style' => 'body']],
                    ]
                ];
            }
        }

        $weatherForecastNode = [
            'type' => 'Container',
            'props' => ['padding' => ['t' => 16, 'l' => 0, 'b' => 0, 'r' => 0]],
            'children' => [
                ['type' => 'Text', 'props' => ['text' => 'Prakiraan Cuaca (12 Jam)', 'style' => 'subtitle']],
                [
                    'type' => 'Row',
                    'props' => ['scrollable' => true, 'spacing' => 8],
                    'children' => $hourlyCards
                ]
            ]
        ];

        // 4. Recent Incidents
        $recentIncidentCards = [];
        foreach ($latestIncidents as $incident) {
            $recentIncidentCards[] = [
                'type' => 'Card',
                'actions' => ['on_tap' => ['type' => 'navigate', 'target' => '/incident/detail/' . $incident->id_insiden]],
                'props' => [
                    'title' => $incident->jenisBencana?->nama_bencana ?? 'Bencana',
                    'description' => $incident->lokasi_spesifik . ' - ' . ($incident->pcnu?->nama_pcnu ?? '')
                ]
            ];
        }

        $recentIncidentsNode = [
            'type' => 'Container',
            'props' => ['padding' => ['t' => 16, 'l' => 0, 'b' => 0, 'r' => 0]],
            'children' => array_merge(
                [['type' => 'Text', 'props' => ['text' => 'Kejadian Terkini (Jawa Tengah)', 'style' => 'subtitle']]],
                $recentIncidentCards
            )
        ];

        // 5. Donation Banner
        $donationNode = [
            'type' => 'Container',
            'props' => ['padding' => ['t' => 16, 'l' => 0, 'b' => 0, 'r' => 0]],
            'children' => [
                ['type' => 'Text', 'props' => ['text' => 'Bantu Mereka', 'style' => 'subtitle']],
                [
                    'type' => 'Card',
                    'actions' => ['on_tap' => ['type' => 'navigate', 'target' => '/donation']],
                    'children' => [
                        ['type' => 'Text', 'props' => ['text' => 'Donasi Kemanusiaan', 'style' => 'headline', 'foreground' => 'success']],
                        ['type' => 'Text', 'props' => ['text' => 'Salurkan bantuan Anda untuk korban bencana melalui NU Care-LAZISNU.', 'style' => 'body']],
                    ]
                ]
            ]
        ];

        $listChildren = [$kpiNodes];
        if (!empty($warningNodes)) {
            $listChildren[] = $warningNodes;
        }
        if (!empty($hourlyCards)) {
            $listChildren[] = $weatherForecastNode;
        }
        if (!empty($recentIncidentCards)) {
            $listChildren[] = $recentIncidentsNode;
        }
        $listChildren[] = $donationNode;

        return [
            'schema_version' => '1.0',
            'type' => 'ListView',
            'props' => ['padding' => 16, 'spacing' => 16],
            'children' => $listChildren
        ];
    }
}
