<?php

namespace Tests\Unit\Sdui\Runtime;

use App\Services\Sdui\Runtime\Runtime;
use App\Services\Sdui\Runtime\Nodes\ScreenNode;
use PHPUnit\Framework\TestCase;

class RuntimeBuilderTest extends TestCase
{
    public function test_fluent_builder_api(): void
    {
        $screen = Runtime::screen('account', 'Workspace')
            ->withSection(
                Runtime::section('identity')
                    ->withComponent(
                        Runtime::component('profile')
                            ->withRenderNode(
                                Runtime::render('avatar', 'container', ['padding' => 16])
                            )
                    )
            );

        $this->assertInstanceOf(ScreenNode::class, $screen);
        $this->assertSame('Workspace', $screen->title);
        $this->assertCount(1, $screen->sections);
        
        $section = $screen->sections[0];
        $this->assertSame('identity', $section->getId()->value);
        $this->assertCount(1, $section->components);
        
        $component = $section->components[0];
        $this->assertSame('profile', $component->getId()->value);
        $this->assertCount(1, $component->renderNodes);
        
        $render = $component->renderNodes[0];
        $this->assertSame('avatar', $render->getId()->value);
        $this->assertSame('container', $render->definition->kind);
        $this->assertSame(16, $render->definition->attributes['padding']);
    }
}
