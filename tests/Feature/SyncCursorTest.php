<?php

namespace Tests\Feature;

use App\Models\AuthRole;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\MobileDevice;
use App\Models\SyncCursor;
use Illuminate\Support\Str;

class SyncCursorTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['deskripsi' => 'Super Admin', 'level_otoritas' => 99]);
        return AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
    }

    public function test_it_returns_server_cursors_per_entity()
    {
        $user = $this->createSuperAdmin();
        $device = MobileDevice::create([
            'uuid_device' => 'device-123',
            'id_pengguna' => $user->id_pengguna,
            'platform' => 'android',
            'app_version' => '1.0.0',
            'status' => 'active',
        ]);

        SyncCursor::create(['entity_type' => 'sitrep', 'uuid_entity' => 'u1', 'cursor_value' => 5, 'action' => 'create']);
        SyncCursor::create(['entity_type' => 'sitrep', 'uuid_entity' => 'u2', 'cursor_value' => 10, 'action' => 'create']);
        SyncCursor::create(['entity_type' => 'assessment', 'uuid_entity' => 'u3', 'cursor_value' => 2, 'action' => 'create']);

        $requestId = (string) Str::uuid();

        $payload = [
            'request_id' => $requestId,
            'device_uuid' => 'device-123',
            'cursors' => [
                'sitrep' => 0,
                'assessment' => 0
            ],
            'changes' => []
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/sync', $payload);
        $response->assertStatus(200);

        $this->assertEquals(10, $response->json('data.server_cursors.sitrep'));
        $this->assertEquals(2, $response->json('data.server_cursors.assessment'));
        $this->assertEquals(0, $response->json('data.server_cursors.klaster'));
    }
}
