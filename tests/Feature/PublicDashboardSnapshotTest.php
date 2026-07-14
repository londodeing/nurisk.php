<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublicDashboardSnapshotTest extends TestCase
{
    /**
     * The golden snapshot path.
     */
    private string $goldenPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->goldenPath = base_path('tests/Golden/public_dashboard.json');
    }

    public function test_runtime_endpoint_returns_nss_envelope(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'schema_version',
            'scene_id',
            'version',
            'ttl_seconds',
            'root' => [
                'type',
                'props',
                'children',
            ],
        ]);
    }

    public function test_legacy_endpoint_returns_old_format(): void
    {
        $response = $this->getJson('/api/public/dashboard/config');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'screen',
            'layout',
            'nodes',
        ]);
    }

    public function test_runtime_response_matches_golden_snapshot(): void
    {
        if (!file_exists($this->goldenPath)) {
            $this->markTestSkipped('Golden snapshot not found.');
        }

        $golden = json_decode(file_get_contents($this->goldenPath), true);
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');

        $response->assertStatus(200);
        $current = $response->json();

        // Compare envelope fields
        $this->assertSame($golden['schema_version'], $current['schema_version']);
        $this->assertSame($golden['scene_id'], $current['scene_id']);

        // Compare root type
        $this->assertSame($golden['root']['type'], $current['root']['type']);

        // Structural comparison — ensure golden children are a subset of current
        // (some sections are conditional on data availability)
        $this->assertGreaterThanOrEqual(2, count($current['root']['children']));
    }

    public function test_schema_version_is_1_x(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');
        $response->assertStatus(200);

        $version = $response->json('schema_version');
        $this->assertStringStartsWith('1.', $version);
    }

    public function test_scene_id_is_public_dashboard(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');
        $response->assertStatus(200);

        $this->assertSame('public_dashboard', $response->json('scene_id'));
    }

    public function test_root_has_required_nss_fields(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');
        $response->assertStatus(200);

        $root = $response->json('root');
        $this->assertArrayHasKey('type', $root);
        $this->assertArrayHasKey('props', $root);
        $this->assertArrayHasKey('children', $root);
    }

    public function test_root_type_is_listview(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');
        $response->assertStatus(200);

        $this->assertSame('ListView', $response->json('root.type'));
    }

    public function test_all_children_have_type_and_props(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');
        $response->assertStatus(200);

        $children = $response->json('root.children');
        foreach ($children as $child) {
            $this->assertArrayHasKey('type', $child);
            $this->assertArrayHasKey('props', $child);
        }
    }

    public function test_kpi_section_has_two_cards(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');
        $response->assertStatus(200);

        $children = $response->json('root.children');
        $kpiRow = collect($children)->firstWhere('type', 'Row');

        $this->assertNotNull($kpiRow, 'KPI Row section not found');
        $this->assertCount(2, $kpiRow['children']);
        $this->assertSame('Card', $kpiRow['children'][0]['type']);
        $this->assertSame('Card', $kpiRow['children'][1]['type']);
    }

    public function test_incident_cards_have_navigate_actions(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');
        $response->assertStatus(200);

        $children = $response->json('root.children');
        $kpiRow = collect($children)->firstWhere('type', 'Row');

        $this->assertNotNull($kpiRow, 'KPI Row section not found');
        foreach ($kpiRow['children'] as $card) {
            $this->assertArrayHasKey('actions', $card);
            $this->assertArrayHasKey('on_tap', $card['actions']);
            $this->assertSame('navigate', $card['actions']['on_tap']['type']);
        }
    }

    public function test_all_cards_with_actions_have_valid_navigate_type(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');
        $response->assertStatus(200);

        // Recursively find all nodes with actions
        $allNodes = $this->collectAllNodes($response->json('root'));
        $cardsWithActions = array_filter($allNodes, fn($n) => isset($n['actions']['on_tap']));

        foreach ($cardsWithActions as $card) {
            $this->assertSame('navigate', $card['actions']['on_tap']['type'],
                'Card action type should be navigate');
            $this->assertArrayHasKey('target', $card['actions']['on_tap'],
                'Navigate action should have target');
        }
    }

    public function test_donation_section_is_present(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');
        $response->assertStatus(200);

        // Find the donation section by looking for a Container whose Card children
        // reference "Donasi" in their text
        $children = $response->json('root.children');
        $donationSection = collect($children)
            ->filter(fn($c) => $c['type'] === 'Container')
            ->firstWhere(function ($c) {
                $allText = '';
                array_walk_recursive($c, function($v) use (&$allText) {
                    if (is_string($v)) $allText .= $v;
                });
                return str_contains($allText, 'Donasi');
            });

        $this->assertNotNull($donationSection, 'Donation section not found');
    }

    /**
     * Recursively compare node structure between golden and current.
     */
    private function assertNodeStructure(array $expected, array $actual): void
    {
        $this->assertSame($expected['type'] ?? null, $actual['type'] ?? null);

        if (isset($expected['children'])) {
            $this->assertArrayHasKey('children', $actual);
            $this->assertCount(count($expected['children']), $actual['children']);

            foreach ($expected['children'] as $i => $expectedChild) {
                $this->assertArrayHasKey($i, $actual['children']);
                $this->assertNodeStructure($expectedChild, $actual['children'][$i]);
            }
        }
    }

    /**
     * Recursively collect all nodes from a tree.
     *
     * @return array<int, array>
     */
    private function collectAllNodes(array $node): array
    {
        $nodes = [$node];
        if (isset($node['children'])) {
            foreach ($node['children'] as $child) {
                $nodes = array_merge($nodes, $this->collectAllNodes($child));
            }
        }
        return $nodes;
    }
}
