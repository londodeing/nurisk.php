<?php

namespace App\Services\Sdui\Runtime\Nodes;

use App\Services\Sdui\Runtime\Domain\NodeId;
use App\Services\Sdui\Runtime\Domain\NodeMetadata;
use App\Services\Sdui\Runtime\Domain\NodeState;
use App\Services\Sdui\Runtime\Domain\NodeVisibility;

final readonly class SectionNode extends AbstractRuntimeNode
{
    /**
     * @param ComponentNode[] $components
     */
    public function __construct(
        NodeId $id,
        public array $components = [],
        ?NodeMetadata $metadata = null,
        NodeVisibility $visibility = NodeVisibility::VISIBLE,
        NodeState $state = NodeState::ENABLED
    ) {
        parent::__construct($id, $metadata, $visibility, $state);
    }

    public function withComponent(ComponentNode $component): self
    {
        $newComponents = $this->components;
        $newComponents[] = $component;
        return $this->cloneWith(['components' => $newComponents]);
    }

    public function getChildren(): array
    {
        return $this->components;
    }

    protected function cloneWith(array $properties): static
    {
        return new self(
            $properties['id'] ?? $this->id,
            $properties['components'] ?? $this->components,
            array_key_exists('metadata', $properties) ? $properties['metadata'] : $this->metadata,
            $properties['visibility'] ?? $this->visibility,
            $properties['state'] ?? $this->state
        );
    }
}
