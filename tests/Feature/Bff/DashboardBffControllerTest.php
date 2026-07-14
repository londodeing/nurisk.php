<?php

namespace Tests\Feature\Bff;

use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardBffControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole(string $roleName): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);

        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
        ]);
    }

    public function test_guest_cannot_access_dashboard()
    {
        $response = $this->getJson('/api/bff/dashboard');
        
        $response->assertStatus(401);
    }

    public function test_relawan_does_not_see_document_queue()
    {
        $user = $this->createAuthUserWithRole('relawan');

        $response = $this->actingAs($user)->getJson('/api/bff/dashboard');
        
        $response->assertStatus(200);
        $widgets = $response->json('data.widgets');
        
        $hasDocumentQueue = collect($widgets)->contains('type', 'DocumentQueue');
        $this->assertFalse($hasDocumentQueue, 'Relawan should not see DocumentQueue');
    }

    public function test_pcnu_sees_document_queue()
    {
        $user = $this->createAuthUserWithRole('pcnu');

        $response = $this->actingAs($user)->withHeaders([
            'X-Scope-Id' => '1',
        ])->getJson('/api/bff/dashboard');

        $response->assertStatus(200);
        $widgets = $response->json('data.widgets');
        
        $hasDocumentQueue = collect($widgets)->contains('type', 'DocumentQueue');
        $this->assertTrue($hasDocumentQueue, 'PCNU with scope should see DocumentQueue');
    }
}
