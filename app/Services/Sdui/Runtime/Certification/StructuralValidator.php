<?php

namespace App\Services\Sdui\Runtime\Certification;

use App\Services\Sdui\Runtime\Contracts\RuntimeNode;
use App\Services\Sdui\Runtime\Nodes\ComponentNode;
use App\Services\Sdui\Runtime\Nodes\RenderNode;
use App\Services\Sdui\Runtime\Nodes\ScreenNode;
use App\Services\Sdui\Runtime\Nodes\SectionNode;

class StructuralValidator
{
    /**
     * @param RuntimeNode $node
     * @param string[] $errors
     */
    public function validate(RuntimeNode $node, array &$errors): void
    {
        $id = $node->getId()->value;

        if ($node instanceof ScreenNode) {
            if (empty($node->sections)) {
                $errors[] = "Screen [{$id}] must have at least 1 Section.";
            }
        } elseif ($node instanceof SectionNode) {
            if (empty($node->components)) {
                $errors[] = "Section [{$id}] must have at least 1 Component.";
            }
        } elseif ($node instanceof ComponentNode) {
            if (empty($node->renderNodes)) {
                $errors[] = "Component [{$id}] must have at least 1 RenderNode.";
            }
        }

        foreach ($node->getChildren() as $child) {
            $this->validate($child, $errors);
        }
    }
}
