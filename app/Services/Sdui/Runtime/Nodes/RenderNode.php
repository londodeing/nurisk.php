<?php

namespace App\Services\Sdui\Runtime\Nodes;

use App\Services\Sdui\Runtime\Domain\NodeId;
use App\Services\Sdui\Runtime\Domain\NodeMetadata;
use App\Services\Sdui\Runtime\Domain\NodeState;
use App\Services\Sdui\Runtime\Contracts\InteractiveNode;
use App\Services\Sdui\Runtime\Contracts\RenderableNode;
use App\Services\Sdui\Runtime\Domain\NodeVisibility;
use App\Services\Sdui\Runtime\Domain\RenderDefinition;

final readonly class RenderNode extends AbstractRuntimeNode implements RenderableNode, InteractiveNode
{
    /**
     * @param array<string, mixed> $actions
     * @param RenderNode[] $children
     */
    public function __construct(
        NodeId $id,
        public RenderDefinition $definition,
        public array $actions = [],
        public array $children = [],
        ?NodeMetadata $metadata = null,
        NodeVisibility $visibility = NodeVisibility::VISIBLE,
        NodeState $state = NodeState::ENABLED
    ) {
        parent::__construct($id, $metadata, $visibility, $state);
    }

    public function getDefinition(): RenderDefinition
    {
        return $this->definition;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function withAction(string $event, array $actionConfig): self
    {
        $newActions = $this->actions;
        $newActions[$event] = $actionConfig;
        return $this->cloneWith(['actions' => $newActions]);
    }

    public function withChild(RenderNode $child): self
    {
        $newChildren = $this->children;
        $newChildren[] = $child;
        return $this->cloneWith(['children' => $newChildren]);
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    protected function cloneWith(array $properties): static
    {
        return new self(
            $properties['id'] ?? $this->id,
            $properties['definition'] ?? $this->definition,
            $properties['actions'] ?? $this->actions,
            $properties['children'] ?? $this->children,
            array_key_exists('metadata', $properties) ? $properties['metadata'] : $this->metadata,
            $properties['visibility'] ?? $this->visibility,
            $properties['state'] ?? $this->state
        );
    }
}
