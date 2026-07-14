<?php

namespace App\Services\Sdui\Runtime;

use App\Services\Sdui\Runtime\Domain\NodeId;
use App\Services\Sdui\Runtime\Domain\RenderDefinition;
use App\Services\Sdui\Runtime\Nodes\ComponentNode;
use App\Services\Sdui\Runtime\Nodes\RenderNode;
use App\Services\Sdui\Runtime\Nodes\ScreenNode;
use App\Services\Sdui\Runtime\Nodes\SectionNode;

/**
 * Fluent API Builder untuk SDUI Runtime Engine.
 */
class Runtime
{
    public static function screen(string $id, string $title = ''): ScreenNode
    {
        return new ScreenNode(new NodeId($id), $title);
    }

    public static function section(string $id): SectionNode
    {
        return new SectionNode(new NodeId($id));
    }

    public static function component(string $id): ComponentNode
    {
        return new ComponentNode(new NodeId($id));
    }

    public static function render(string $id, string $kind, array $attributes = [], array $actions = []): RenderNode
    {
        return new RenderNode(new NodeId($id), new RenderDefinition($kind, $attributes), $actions);
    }
}
