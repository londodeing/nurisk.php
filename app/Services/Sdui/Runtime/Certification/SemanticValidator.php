<?php

namespace App\Services\Sdui\Runtime\Certification;

use App\Services\Sdui\Runtime\Contracts\InteractiveNode;
use App\Services\Sdui\Runtime\Contracts\RuntimeNode;
use App\Services\Sdui\Runtime\Nodes\RenderNode;

class SemanticValidator
{
    /**
     * @param RuntimeNode $node
     * @param string[] $errors
     */
    public function validate(RuntimeNode $node, array &$errors): void
    {
        $seenIds = [];
        $this->traverseAndValidate($node, $errors, $seenIds);
    }

    private function traverseAndValidate(RuntimeNode $node, array &$errors, array &$seenIds): void
    {
        $id = $node->getId()->value;

        if (isset($seenIds[$id])) {
            $errors[] = "Duplicate ID found: [{$id}]. Node IDs must be unique.";
        }
        $seenIds[$id] = true;

        if ($node instanceof InteractiveNode && $node instanceof RenderNode) {
            $kind = $node->getDefinition()->kind;
            if ($kind === 'button' && empty($node->getActions())) {
                $errors[] = "InteractiveNode [{$id}] of kind 'button' must have at least one action defined.";
            }
        }

        if ($node instanceof RenderNode) {
            $kind = $node->getDefinition()->kind;
            if ($kind === 'avatar' && !isset($node->getDefinition()->attributes['image'])) {
                $errors[] = "RenderNode [{$id}] of kind 'avatar' must have an 'image' attribute.";
            }
        }

        foreach ($node->getChildren() as $child) {
            $this->traverseAndValidate($child, $errors, $seenIds);
        }
    }
}
