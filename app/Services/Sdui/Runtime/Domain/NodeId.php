<?php

namespace App\Services\Sdui\Runtime\Domain;

final readonly class NodeId
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException("NodeId tidak boleh kosong.");
        }
    }
}
