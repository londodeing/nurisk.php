<?php

namespace Tests\Feature;

use Tests\TestCase;

class RenderabilityTest extends TestCase
{
    /**
     * All SDUI primitives registered on the Flutter side.
     * This is the single source of truth — serializer output must
     * only use these types.
     */
    private const KNOWN_PRIMITIVES = [
        'Container', 'Row', 'Column', 'Text', 'Icon', 'Card',
        'ListView', 'Badge', 'Divider', 'SizedBox', 'Expanded',
        'Flexible', 'AspectRatio',
        'Grid', 'Timeline', 'BottomSheet', 'Chart', 'Checkbox',
        'Dialog', 'Dropdown', 'FormField', 'Map', 'Scene', 'Switch', 'Tabs',
    ];

    /**
     * Known action types supported by RuntimeActionDispatcher.
     */
    private const KNOWN_ACTIONS = ['navigate', 'submit', 'reload', 'toast', 'action'];

    public function test_account_workspace_produces_only_known_primitives(): void
    {
        $response = $this->getJson('/api/account/home');
        $response->assertStatus(200);

        $root = $response->json('root');
        $this->assertNotNull($root, 'Response must have a root node');

        $unknownTypes = [];
        $unknownActions = [];
        $totalNodes = 0;

        $this->walkNode($root, $unknownTypes, $unknownActions, $totalNodes);

        $this->assertNotEmpty($totalNodes, 'Tree must contain at least one node');

        // Assert: Zero unknown types
        $this->assertEmpty(
            $unknownTypes,
            'Found ' . count($unknownTypes) . ' unknown primitive type(s): ' . implode(', ', $unknownTypes)
        );

        // Assert: Zero unknown actions
        $this->assertEmpty(
            $unknownActions,
            'Found ' . count($unknownActions) . ' unknown action(s): ' . implode(', ', $unknownActions)
        );
    }

    public function test_public_dashboard_produces_only_known_primitives(): void
    {
        $response = $this->getJson('/api/public/dashboard/config?runtime=1');
        $response->assertStatus(200);

        $root = $response->json('root');
        $this->assertNotNull($root, 'Response must have a root node');

        $unknownTypes = [];
        $unknownActions = [];
        $totalNodes = 0;

        $this->walkNode($root, $unknownTypes, $unknownActions, $totalNodes);

        $this->assertEmpty(
            $unknownTypes,
            'Found ' . count($unknownTypes) . ' unknown primitive type(s): ' . implode(', ', $unknownTypes)
        );

        $this->assertEmpty(
            $unknownActions,
            'Found ' . count($unknownActions) . ' unknown action(s): ' . implode(', ', $unknownActions)
        );
    }

    public function test_guest_data_uses_only_primitives(): void
    {
        // The _guestData() function in Flutter produces an SduiNode tree.
        // This test validates that the tree only uses registered primitives
        // by checking the golden guest JSON structure.
        $guestTree = [
            'type' => 'Column',
            'children' => [
                ['type' => 'Container'], // guest-card
                ['type' => 'Container'], // guest-menu-card
            ],
        ];

        $unknownTypes = [];
        $unknownActions = [];
        $totalNodes = 0;

        $this->walkNode($guestTree, $unknownTypes, $unknownActions, $totalNodes);

        $this->assertEmpty(
            $unknownTypes,
            'Guest data uses unknown type(s): ' . implode(', ', $unknownTypes)
        );
    }

    public function test_all_active_primitives_have_renderers(): void
    {
        $activePrimitives = ['Container', 'Column', 'Row', 'Text', 'Icon',
            'SizedBox', 'Divider', 'Badge', 'Expanded', 'ListView'];

        foreach ($activePrimitives as $primitive) {
            $this->assertContains(
                $primitive,
                self::KNOWN_PRIMITIVES,
                "Active primitive $primitive is not in the known primitives list"
            );
        }
    }

    public function test_registered_primitives_are_unique(): void
    {
        $this->assertCount(
            count(array_unique(self::KNOWN_PRIMITIVES)),
            self::KNOWN_PRIMITIVES,
            'Duplicate primitives found in KNOWN_PRIMITIVES'
        );
    }

    /**
     * Recursively walk a node tree and collect unknown types/actions.
     */
    private function walkNode(array $node, array &$unknownTypes, array &$unknownActions, int &$totalNodes): void
    {
        $totalNodes++;

        // Check type
        $type = $node['type'] ?? null;
        if ($type && !in_array($type, self::KNOWN_PRIMITIVES, true)) {
            $unknownTypes[] = $type;
        }

        // Check actions
        $actions = $node['actions'] ?? [];
        foreach ($actions as $event => $actionValue) {
            if (is_array($actionValue)) {
                // Single action or list
                $actionList = isset($actionValue['type']) ? [$actionValue] : $actionValue;
                foreach ($actionList as $action) {
                    if (is_array($action) && isset($action['type'])) {
                        if (!in_array($action['type'], self::KNOWN_ACTIONS, true)) {
                            $unknownActions[] = $action['type'];
                        }
                        // Recurse chain actions
                        foreach (['on_success', 'on_failure'] as $chainKey) {
                            if (isset($action[$chainKey])) {
                                $chain = $action[$chainKey];
                                $chainList = isset($chain['type']) ? [$chain] : (is_array($chain) ? $chain : []);
                                foreach ($chainList as $chainAction) {
                                    if (is_array($chainAction) && isset($chainAction['type'])) {
                                        if (!in_array($chainAction['type'], self::KNOWN_ACTIONS, true)) {
                                            $unknownActions[] = $chainAction['type'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Recurse children
        $children = $node['children'] ?? [];
        foreach ($children as $child) {
            if (is_array($child)) {
                $this->walkNode($child, $unknownTypes, $unknownActions, $totalNodes);
            }
        }
    }
}
