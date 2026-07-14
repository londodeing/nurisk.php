<?php

namespace App\Services\Sdui\Runtime\Certification;

use App\Services\Sdui\Runtime\Contracts\RuntimeNode;
use App\Services\Sdui\Runtime\Domain\NodeMetadata;
use App\Services\Sdui\Runtime\Nodes\ComponentNode;
use App\Services\Sdui\Runtime\Nodes\RenderNode;
use App\Services\Sdui\Runtime\Nodes\ScreenNode;
use App\Services\Sdui\Runtime\Nodes\SectionNode;

class RuntimeNormalizer
{
    public function normalize(RuntimeNode $node): RuntimeNode
    {
        // 1. Normalize children recursively
        $normalizedChildren = [];
        foreach ($node->getChildren() as $child) {
            $normalizedChildren[] = $this->normalize($child);
        }

        $newNode = $this->replaceChildren($node, $normalizedChildren);

        // 2. Fill missing metadata
        $meta = $newNode->getMetadata();
        if ($meta === null) {
            $meta = new NodeMetadata(['auto_generated' => true]);
        }
        if (!isset($meta->data['created_at'])) {
            $meta = $meta->with('created_at', time());
        }

        return $newNode->withMetadata($meta);
    }

    private function replaceChildren(RuntimeNode $node, array $children): RuntimeNode
    {
        if ($node instanceof ScreenNode) {
            $clone = new ScreenNode($node->getId(), $node->title, $children, $node->getMetadata(), $node->getVisibility(), $node->getState());
        } elseif ($node instanceof SectionNode) {
            $clone = new SectionNode($node->getId(), $children, $node->getMetadata(), $node->getVisibility(), $node->getState());
        } elseif ($node instanceof ComponentNode) {
            $clone = new ComponentNode($node->getId(), $children, $node->getMetadata(), $node->getVisibility(), $node->getState());
        } elseif ($node instanceof RenderNode) {
            $clone = new RenderNode($node->getId(), $node->getDefinition(), $node->getActions(), $children, $node->getMetadata(), $node->getVisibility(), $node->getState());
        } else {
            return $node;
        }

        return $clone;
    }
}
