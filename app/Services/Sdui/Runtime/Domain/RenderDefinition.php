<?php

namespace App\Services\Sdui\Runtime\Domain;

final readonly class RenderDefinition
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $kind,
        public array $attributes = []
    ) {
    }

    public function withAttribute(string $key, mixed $value): self
    {
        $newAttrs = $this->attributes;
        $newAttrs[$key] = $value;
        return new self($this->kind, $newAttrs);
    }
}
