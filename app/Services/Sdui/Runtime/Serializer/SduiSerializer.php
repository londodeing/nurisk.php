<?php

namespace App\Services\Sdui\Runtime\Serializer;

use App\Services\Sdui\Runtime\Contracts\InteractiveNode;
use App\Services\Sdui\Runtime\Contracts\RenderableNode;
use App\Services\Sdui\Runtime\Contracts\RuntimeNode;
use App\Services\Sdui\Runtime\Nodes\ComponentNode;
use App\Services\Sdui\Runtime\Nodes\RenderNode;
use App\Services\Sdui\Runtime\Nodes\ScreenNode;
use App\Services\Sdui\Runtime\Nodes\SectionNode;

class SduiSerializer
{
    /**
     * @param RuntimeNode $certifiedNode
     * @return array<string, mixed>
     */
    public function serialize(RuntimeNode $certifiedNode): array
    {
        $id = $certifiedNode->getId()->value;
        $meta = $certifiedNode->getMetadata();
        $visibility = $certifiedNode->getVisibility()->value;
        $state = $certifiedNode->getState()->value;
        
        $type = 'Container';
        $props = [];
        $actions = [];

        if ($certifiedNode instanceof ScreenNode) {
            $type = 'ListView';
            $props['title'] = $certifiedNode->title;
            // Default spacing for screen sections
            $props['spacing'] = 16;
            $props['padding'] = 16;
        } elseif ($certifiedNode instanceof SectionNode) {
            $type = 'Column';
            $props['spacing'] = 16;
        } elseif ($certifiedNode instanceof ComponentNode) {
            $type = 'Column';
            $props['spacing'] = 8;
        } elseif ($certifiedNode instanceof RenderNode) {
            $type = $certifiedNode->getDefinition()->kind;
            $props = $certifiedNode->getDefinition()->attributes;
            $actions = $certifiedNode->getActions();
        }

        // Base array structure mapping to NSS JSON Contract
        $result = [
            'id' => $id,
            'type' => ucfirst($type),
            'props' => (object) $props, // cast to object to ensure {} in JSON when empty
            'actions' => (object) $actions,
            'visible' => $visibility === 'visible',
            'enabled' => $state === 'enabled',
        ];

        // Process children recursively
        $children = $certifiedNode->getChildren();
        if (!empty($children)) {
            $serializedChildren = [];
            foreach ($children as $child) {
                $serializedChildren[] = $this->serialize($child);
            }
            $result['children'] = $serializedChildren;
        }

        return $result;
    }
}
