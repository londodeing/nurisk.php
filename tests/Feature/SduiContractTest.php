<?php

namespace Tests\Feature;

use App\Models\AuthUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SduiContractTest extends TestCase
{
    use RefreshDatabase;

    private $allowedPrimitives = [
        'Scene', 'Map', 'Marker', 'Polygon', 'Radius', 'SpatialLayer',
        'IncidentLayer', 'MissionLayer',
        'Container', 'Row', 'Column', 'Text', 'Icon', 'Image', 'Button',
        'Timeline', 'Card', 'BottomSheet', 'ListView', 'Grid', 'Divider', 'Spacer', 'Badge', 'SizedBox',
        'scrollable', 'wrap', 'list', 'empty_state',
        'Expanded', 'Flexible', 'AspectRatio',
    ];

    private function validateSduiNode(array $node, string $endpoint)
    {
        $this->assertArrayHasKey('type', $node, "Node in $endpoint is missing 'type' key.");
        $type = $node['type'];
        
        $this->assertContains(
            $type, 
            $this->allowedPrimitives, 
            "Domain component detected! '$type' is not a permitted Primitive Component in $endpoint."
        );

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->validateSduiNode($child, $endpoint);
            }
        }
        
        // Check Scene panels
        if ($type === 'Scene' && isset($node['props']['scene']['panels'])) {
            foreach ($node['props']['scene']['panels'] as $panelNode) {
                if ($panelNode !== null) {
                    $this->validateSduiNode($panelNode, $endpoint);
                }
            }
        }
    }

    private function validateRootContract(array $json, string $endpoint)
    {
        $this->assertArrayHasKey('nodes', $json, "The $endpoint must return a root 'nodes' array.");
        $this->assertIsArray($json['nodes']);

        foreach ($json['nodes'] as $node) {
            $this->validateSduiNode($node, $endpoint);
        }
    }

    public function test_public_dashboard_contract()
    {
        $response = $this->getJson('/api/public/dashboard/config');
        $response->assertStatus(200);
        
        $this->validateRootContract($response->json(), '/api/public/dashboard/config');
    }

    public function test_account_home_contract()
    {
        $user = AuthUser::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/account/home');
        
        $response->assertStatus(200);
        $json = $response->json();

        // NSS 1.0 Envelope contract
        $this->assertArrayHasKey('schema_version', $json);
        $this->assertEquals('1.0.0', $json['schema_version']);
        $this->assertArrayHasKey('scene_id', $json);
        $this->assertEquals('account_workspace', $json['scene_id']);
        $this->assertArrayHasKey('version', $json);
        $this->assertIsInt($json['version']);
        $this->assertArrayHasKey('ttl_seconds', $json);
        $this->assertEquals(120, $json['ttl_seconds']);
        $this->assertArrayHasKey('root', $json);

        // NSS 1.0 Root Node contract
        $this->validateNssNode($json['root'], '/api/account/home');
        $this->assertEquals('ListView', $json['root']['type']);
        $this->assertEquals('account_workspace', $json['root']['id']);
        $this->assertArrayHasKey('props', $json['root']);
        $this->assertEquals('Akun & Pusat Komando', $json['root']['props']['title']);
        $this->assertArrayHasKey('children', $json['root']);
        $this->assertIsArray($json['root']['children']);
    }

    private function validateNssNode(array $node, string $endpoint): void
    {
        $this->assertArrayHasKey('type', $node, "Node in $endpoint is missing 'type' key.");
        $type = $node['type'];

        $this->assertContains(
            $type,
            $this->allowedPrimitives,
            "Domain component detected! '$type' is not a permitted Primitive Component in $endpoint."
        );

        $this->assertArrayHasKey('id', $node, "Node in $endpoint is missing 'id' key.");
        $this->assertArrayHasKey('visible', $node, "Node in $endpoint is missing 'visible' key.");
        $this->assertArrayHasKey('enabled', $node, "Node in $endpoint is missing 'enabled' key.");
        $this->assertArrayHasKey('props', $node, "Node in $endpoint is missing 'props' key.");
        $this->assertArrayHasKey('actions', $node, "Node in $endpoint is missing 'actions' key.");

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->validateNssNode($child, $endpoint);
            }
        }
    }

    public function test_dashboard_home_contract()
    {
        $user = AuthUser::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/dashboard/home');
        
        $response->assertStatus(200);
        $this->validateRootContract($response->json(), '/api/dashboard/home');
    }
}
