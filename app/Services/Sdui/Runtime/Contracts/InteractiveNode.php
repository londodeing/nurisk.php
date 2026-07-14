<?php

namespace App\Services\Sdui\Runtime\Contracts;

/**
 * Interface untuk RuntimeNode yang bisa berinteraksi dengan user (memiliki action).
 */
interface InteractiveNode extends RuntimeNode
{
    /**
     * @return array<string, mixed>
     */
    public function getActions(): array;
}
