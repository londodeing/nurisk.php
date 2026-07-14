<?php

namespace Tests\Unit\Sdui\Runtime;

use App\Services\Sdui\Runtime\Domain\NodeId;
use App\Services\Sdui\Runtime\Domain\NodeVisibility;
use App\Services\Sdui\Runtime\Domain\NodeState;
use App\Services\Sdui\Runtime\Domain\RenderDefinition;
use App\Services\Sdui\Runtime\Nodes\ComponentNode;
use App\Services\Sdui\Runtime\Nodes\RenderNode;
use App\Services\Sdui\Runtime\Nodes\ScreenNode;
use App\Services\Sdui\Runtime\Nodes\SectionNode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RuntimeNodeTest extends TestCase
{
    public function test_node_id_cannot_be_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new NodeId('   ');
    }

    public function test_screen_node_immutability(): void
    {
        $screen = new ScreenNode(new NodeId('workspace_screen'), 'Workspace');
        $section = new SectionNode(new NodeId('identity_section'));
        
        $newScreen = $screen->withSection($section);

        // Harus instance baru (immutable)
        $this->assertNotSame($screen, $newScreen);
        $this->assertCount(0, $screen->sections);
        $this->assertCount(1, $newScreen->sections);
        
        // Cek relasi Tree
        $this->assertSame($section, $newScreen->sections[0]);
    }

    public function test_render_node_creation_without_arrays(): void
    {
        $node = new RenderNode(
            new NodeId('title_text'),
            new RenderDefinition('text', ['text' => 'Hello World'])
        );

        $this->assertSame('text', $node->definition->kind);
        $this->assertSame('Hello World', $node->definition->attributes['text']);
        $this->assertSame(NodeVisibility::VISIBLE, $node->getVisibility());

        $hiddenNode = $node->withVisibility(NodeVisibility::HIDDEN);
        $this->assertNotSame($node, $hiddenNode);
        $this->assertSame(NodeVisibility::HIDDEN, $hiddenNode->getVisibility());
        
        // node lama tidak berubah
        $this->assertSame(NodeVisibility::VISIBLE, $node->getVisibility());
    }

    public function test_full_tree_immutability(): void
    {
        $text = new RenderNode(new NodeId('txt1'), new RenderDefinition('text', ['text' => 'Halo']));
        $component = (new ComponentNode(new NodeId('c1')))->withRenderNode($text);
        $section = (new SectionNode(new NodeId('s1')))->withComponent($component);
        $screen = (new ScreenNode(new NodeId('scr1'), 'Test Screen'))->withSection($section);

        $this->assertCount(1, $screen->sections);
        $this->assertCount(1, $screen->sections[0]->components);
        $this->assertCount(1, $screen->sections[0]->components[0]->renderNodes);
        $this->assertSame('text', $screen->sections[0]->components[0]->renderNodes[0]->definition->kind);
    }
}
