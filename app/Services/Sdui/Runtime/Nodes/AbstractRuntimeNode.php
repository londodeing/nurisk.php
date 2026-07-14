<?php

namespace App\Services\Sdui\Runtime\Nodes;

use App\Services\Sdui\Runtime\Contracts\RuntimeNode;
use App\Services\Sdui\Runtime\Domain\NodeId;
use App\Services\Sdui\Runtime\Domain\NodeMetadata;
use App\Services\Sdui\Runtime\Domain\NodeState;
use App\Services\Sdui\Runtime\Domain\NodeVisibility;

abstract readonly class AbstractRuntimeNode implements RuntimeNode
{
    public function __construct(
        protected NodeId $id,
        protected ?NodeMetadata $metadata = null,
        protected NodeVisibility $visibility = NodeVisibility::VISIBLE,
        protected NodeState $state = NodeState::ENABLED
    ) {
    }

    public function getId(): NodeId
    {
        return $this->id;
    }

    public function getMetadata(): ?NodeMetadata
    {
        return $this->metadata;
    }

    public function getVisibility(): NodeVisibility
    {
        return $this->visibility;
    }

    public function getState(): NodeState
    {
        return $this->state;
    }

    public function withMetadata(?NodeMetadata $metadata): static
    {
        return $this->cloneWith(['metadata' => $metadata]);
    }

    public function withVisibility(NodeVisibility $visibility): static
    {
        return $this->cloneWith(['visibility' => $visibility]);
    }

    public function withState(NodeState $state): static
    {
        return $this->cloneWith(['state' => $state]);
    }

    /**
     * Helper to clone with modified properties.
     * PHP 8.2 readonly classes can't be easily mutated after creation,
     * so we create a new instance using reflection or explicit constructors in subclasses.
     * For simplicity, subclasses must implement this.
     */
    abstract protected function cloneWith(array $properties): static;
}
