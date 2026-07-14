<?php

namespace App\Services\Sdui\Runtime\Nodes;

use App\Services\Sdui\Runtime\Domain\NodeId;
use App\Services\Sdui\Runtime\Domain\NodeMetadata;
use App\Services\Sdui\Runtime\Domain\NodeState;
use App\Services\Sdui\Runtime\Domain\NodeVisibility;

final readonly class ScreenNode extends AbstractRuntimeNode
{
    /**
     * @param SectionNode[] $sections
     */
    public function __construct(
        NodeId $id,
        public string $title,
        public array $sections = [],
        ?NodeMetadata $metadata = null,
        NodeVisibility $visibility = NodeVisibility::VISIBLE,
        NodeState $state = NodeState::ENABLED
    ) {
        parent::__construct($id, $metadata, $visibility, $state);
    }

    public function withSection(SectionNode $section): self
    {
        $newSections = $this->sections;
        $newSections[] = $section;
        return $this->cloneWith(['sections' => $newSections]);
    }

    public function getChildren(): array
    {
        return $this->sections;
    }

    protected function cloneWith(array $properties): static
    {
        return new self(
            $properties['id'] ?? $this->id,
            $properties['title'] ?? $this->title,
            $properties['sections'] ?? $this->sections,
            array_key_exists('metadata', $properties) ? $properties['metadata'] : $this->metadata,
            $properties['visibility'] ?? $this->visibility,
            $properties['state'] ?? $this->state
        );
    }
}
