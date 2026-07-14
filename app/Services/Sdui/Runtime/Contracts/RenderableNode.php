<?php

namespace App\Services\Sdui\Runtime\Contracts;

use App\Services\Sdui\Runtime\Domain\RenderDefinition;

/**
 * Interface untuk RuntimeNode yang memiliki representasi visual / primitif.
 */
interface RenderableNode extends RuntimeNode
{
    public function getDefinition(): RenderDefinition;
}
