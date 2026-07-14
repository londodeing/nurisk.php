<?php

namespace App\Services\Sdui\Runtime\Domain;

final readonly class NodeMetadata
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(public array $data = [])
    {
    }

    public function with(string $key, mixed $value): self
    {
        $newData = $this->data;
        $newData[$key] = $value;
        return new self($newData);
    }
}
