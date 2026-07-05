<?php

namespace Tests\Feature\Api;

use App\Models\AuthRole;
use App\Models\AuthUser;
use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/v1/sync/status');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/sync', [
            'request_id' => '00000000-0000-0000-0000-000000000000',
            'device_uuid' => 'test',
            'cursors' => [],
            'changes' => [],
        ]);
        $response->assertStatus(401);

        $response = $this->getJson('/api/operasi/posaju');
        $response->assertStatus(401);

        $response = $this->getJson('/api/relawan/available');
        $response->assertStatus(401);
    }

    public function test_public_endpoints_work_without_auth(): void
    {
        $response = $this->getJson('/api/wilayah/kabupaten');
        $response->assertStatus(200);

        $response = $this->postJson('/api/relawan/daftar', []);
        // daftar endpoint requires authenticated relawan role (pre-existing)
        $response->assertStatus(403);
    }

    public function test_authenticated_access_succeeds(): void
    {
        $role = AuthRole::firstOrCreate(
            ['nama_peran' => 'super_admin'],
            ['deskripsi' => 'Super Admin', 'level_otoritas' => 99]
        );

        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/sync/status');
        $response->assertStatus(200);
    }

    public function test_public_health_check(): void
    {
        $response = $this->getJson('/health');
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'version', 'git_sha', 'time', 'database', 'cache', 'storage', 'queue', 'disk', 'migration']);
    }
}
