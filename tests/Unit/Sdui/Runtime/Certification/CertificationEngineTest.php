<?php

namespace Tests\Unit\Sdui\Runtime\Certification;

use App\Services\Sdui\Runtime\Certification\RuntimeCertificationEngine;
use App\Services\Sdui\Runtime\Certification\RuntimeNormalizer;
use App\Services\Sdui\Runtime\Certification\SemanticValidator;
use App\Services\Sdui\Runtime\Certification\StructuralValidator;
use App\Services\Sdui\Runtime\Domain\NodeMetadata;
use App\Services\Sdui\Runtime\Runtime;
use PHPUnit\Framework\TestCase;

class CertificationEngineTest extends TestCase
{
    private RuntimeCertificationEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new RuntimeCertificationEngine(
            new StructuralValidator(),
            new SemanticValidator(),
            new RuntimeNormalizer()
        );
    }

    public function test_structural_validation_fails_on_empty_screen(): void
    {
        $screen = Runtime::screen('empty_screen');
        $result = $this->engine->certify($screen);
        
        $this->assertFalse($result->isCertified());
        $this->assertCount(1, $result->errors);
        $this->assertStringContainsString('must have at least 1 Section', $result->errors[0]);
    }

    public function test_structural_validation_fails_on_empty_section(): void
    {
        $screen = Runtime::screen('scr1')->withSection(Runtime::section('sec1'));
        $result = $this->engine->certify($screen);
        
        $this->assertFalse($result->isCertified());
        $this->assertCount(1, $result->errors);
        $this->assertStringContainsString('must have at least 1 Component', $result->errors[0]);
    }

    public function test_structural_validation_fails_on_empty_component(): void
    {
        $screen = Runtime::screen('scr1')->withSection(
            Runtime::section('sec1')->withComponent(Runtime::component('comp1'))
        );
        $result = $this->engine->certify($screen);
        
        $this->assertFalse($result->isCertified());
        $this->assertCount(1, $result->errors);
        $this->assertStringContainsString('must have at least 1 RenderNode', $result->errors[0]);
    }

    public function test_semantic_validation_duplicate_ids(): void
    {
        $screen = Runtime::screen('scr1')->withSection(
            Runtime::section('sec1')
                ->withComponent(Runtime::component('duplicate_id')->withRenderNode(Runtime::render('r1', 'text')))
                ->withComponent(Runtime::component('duplicate_id')->withRenderNode(Runtime::render('r2', 'text')))
        );
        
        $result = $this->engine->certify($screen);
        $this->assertFalse($result->isCertified());
        $this->assertStringContainsString('Duplicate ID found: [duplicate_id]', implode(' ', $result->errors));
    }

    public function test_semantic_validation_interactive_button_requires_action(): void
    {
        $screen = Runtime::screen('scr1')->withSection(
            Runtime::section('sec1')->withComponent(
                Runtime::component('comp1')->withRenderNode(Runtime::render('btn1', 'button'))
            )
        );

        $result = $this->engine->certify($screen);
        $this->assertFalse($result->isCertified());
        $this->assertStringContainsString("kind 'button' must have at least one action defined", implode(' ', $result->errors));
    }

    public function test_semantic_validation_avatar_requires_image_attribute(): void
    {
        $screen = Runtime::screen('scr1')->withSection(
            Runtime::section('sec1')->withComponent(
                Runtime::component('comp1')->withRenderNode(Runtime::render('av1', 'avatar'))
            )
        );

        $result = $this->engine->certify($screen);
        $this->assertFalse($result->isCertified());
        $this->assertStringContainsString("kind 'avatar' must have an 'image' attribute", implode(' ', $result->errors));
    }

    public function test_successful_certification_and_normalization(): void
    {
        // Screen valid
        $screen = Runtime::screen('valid_screen', 'My App')->withSection(
            Runtime::section('main_section')->withComponent(
                Runtime::component('profile_card')
                    ->withRenderNode(Runtime::render('av_valid', 'avatar', ['image' => 'url']))
                    ->withRenderNode(Runtime::render('btn_valid', 'button', [], ['on_tap' => ['type' => 'test']]))
            )
        );

        // Assert tidak ada metadata awal
        $this->assertNull($screen->getMetadata());

        $result = $this->engine->certify($screen);
        
        $this->assertTrue($result->isCertified());
        $this->assertCount(0, $result->errors);

        $certified = $result->certifiedRuntime;
        
        // Assert Normalization menambahkan metadata created_at
        $this->assertNotNull($certified->getMetadata());
        $this->assertTrue($certified->getMetadata()->data['auto_generated']);
        $this->assertArrayHasKey('created_at', $certified->getMetadata()->data);

        // Cek secara rekursif bahwa anak-anak juga dinormalisasi
        $sections = $certified->getChildren();
        $this->assertCount(1, $sections);
        $this->assertNotNull($sections[0]->getMetadata());
        $this->assertArrayHasKey('created_at', $sections[0]->getMetadata()->data);

        // Buktikan Immutability (node asli tidak berubah)
        $this->assertNull($screen->getMetadata());
    }

    public function test_certification_preserves_existing_metadata(): void
    {
        $customMeta = new NodeMetadata(['custom' => 'value']);
        $screen = Runtime::screen('valid_screen')->withMetadata($customMeta)->withSection(
            Runtime::section('main_section')->withComponent(
                Runtime::component('profile_card')
                    ->withRenderNode(Runtime::render('av_valid', 'avatar', ['image' => 'url']))
            )
        );

        $result = $this->engine->certify($screen);
        $this->assertTrue($result->isCertified());
        
        $certifiedMeta = $result->certifiedRuntime->getMetadata();
        $this->assertSame('value', $certifiedMeta->data['custom']);
        $this->assertArrayHasKey('created_at', $certifiedMeta->data);
    }
}
