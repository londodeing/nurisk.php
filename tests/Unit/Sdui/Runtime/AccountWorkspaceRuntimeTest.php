<?php

namespace Tests\Unit\Sdui\Runtime;

use App\Services\Sdui\Runtime\Certification\RuntimeCertificationEngine;
use App\Services\Sdui\Runtime\Certification\RuntimeNormalizer;
use App\Services\Sdui\Runtime\Certification\SemanticValidator;
use App\Services\Sdui\Runtime\Certification\StructuralValidator;
use App\Services\Sdui\Runtime\Screens\AccountWorkspaceScreen;
use App\Services\Sdui\Runtime\Serializer\SduiSerializer;
use PHPUnit\Framework\TestCase;

class AccountWorkspaceRuntimeTest extends TestCase
{
    public function test_account_workspace_generates_valid_nss_json(): void
    {
        // Mock profile data
        $profileData = [
            'nama_lengkap' => 'Budi Santoso',
            'no_hp' => '081234567890',
            'nama_peran' => 'relawan',
            'status_akun' => 'aktif'
        ];
        $activeRole = [
            'nama_jabatan' => 'Koordinator TRC'
        ];

        // 1. Build Runtime Tree
        $screen = AccountWorkspaceScreen::build($profileData, $activeRole, [], [], null, null);

        // 2. Certify
        $engine = new RuntimeCertificationEngine(
            new StructuralValidator(),
            new SemanticValidator(),
            new RuntimeNormalizer()
        );
        $result = $engine->certify($screen);
        
        $this->assertTrue($result->isCertified(), 'Screen fails certification: ' . implode(', ', $result->errors));

        // 3. Serialize
        $serializer = new SduiSerializer();
        $json = $serializer->serialize($result->certifiedRuntime);

        // Assert base NSS tree properties (schema_version is at envelope level)
        $this->assertArrayNotHasKey('schema_version', $json);
        $this->assertSame('ListView', $json['type']);
        $this->assertSame('account_workspace', $json['id']);
        
        $this->assertObjectHasProperty('title', $json['props']);
        $this->assertSame('Akun & Pusat Komando', $json['props']->title);

        $this->assertArrayHasKey('children', $json);
        $this->assertCount(3, $json['children']); // IdentitySection, StatusOperasionalSection, MenuSection

        $identitySection = $json['children'][0];
        $this->assertSame('Column', $identitySection['type']);
        
        $profileComponent = $identitySection['children'][0];
        $this->assertSame('Column', $profileComponent['type']);

        $containerNode = $profileComponent['children'][0];
        $this->assertSame('Container', $containerNode['type']);
        
        // Ensure NO 'style' array is generated (Strict NSS Compliance)
        $this->assertObjectNotHasProperty('style', $containerNode['props']);
        $this->assertObjectHasProperty('padding', $containerNode['props']);
        $this->assertEquals(['all' => 16], $containerNode['props']->padding);
    }
}
