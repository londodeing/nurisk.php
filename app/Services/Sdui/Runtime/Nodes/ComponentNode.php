<?php

namespace App\Services\Sdui\Runtime\Nodes;

use App\Services\Sdui\Runtime\Domain\NodeId;
use App\Services\Sdui\Runtime\Domain\NodeMetadata;
use App\Services\Sdui\Runtime\Domain\NodeState;
use App\Services\Sdui\Runtime\Domain\NodeVisibility;

final readonly class ComponentNode extends AbstractRuntimeNode
{
    /**
     * @param RenderNode[] $renderNodes
     */
    public function __construct(
        NodeId $id,
        public array $renderNodes = [],
        ?NodeMetadata $metadata = null,
        NodeVisibility $visibility = NodeVisibility::VISIBLE,
        NodeState $state = NodeState::ENABLED
    ) {
        parent::__construct($id, $metadata, $visibility, $state);
    }

    public function withRenderNode(RenderNode $node): self
    {
        $newNodes = $this->renderNodes;
        $newNodes[] = $node;
        return $this->cloneWith(['renderNodes' => $newNodes]);
    }

    public function getChildren(): array
    {
        return $this->renderNodes;
    }

    protected function cloneWith(array $properties): static
    {
        return new self(
            $properties['id'] ?? $this->id,
            $properties['renderNodes'] ?? $this->renderNodes,
            array_key_exists('metadata', $properties) ? $properties['metadata'] : $this->metadata,
            $properties['visibility'] ?? $this->visibility,
            $properties['state'] ?? $this->state
        );
    }
}
