<?php

namespace Tests\Feature;

use App\Models\AuthRole;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\MobileDevice;
use Illuminate\Support\Str;

class SyncIdempotencyTest extends TestCase
{
    use DatabaseTransactions;

    private function createSuperAdmin(): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['deskripsi' => 'Super Admin', 'level_otoritas' => 99]);
        return AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
    }

    public function test_it_handles_duplicate_requests_idempotently()
    {
        $user = $this->createSuperAdmin();
        $device = MobileDevice::create([
            'uuid_device' => 'device-123',
            'id_pengguna' => $user->id_pengguna,
            'platform' => 'android',
            'app_version' => '1.0.0',
            'status' => 'active',
            'trust_score' => 100,
        ]);

        $requestId = (string) Str::uuid();

        $payload = [
            'request_id' => $requestId,
            'device_uuid' => 'device-123',
            'cursors' => [
                'sitrep' => 0
            ],
            'changes' => []
        ];

        // First request
        $response1 = $this->actingAs($user)->postJson('/api/v1/sync', $payload);
        $response1->assertStatus(200);

        // Second request with same request_id
        $response2 = $this->actingAs($user)->postJson('/api/v1/sync', $payload);
        $response2->assertStatus(200);

        // Both responses should be exactly the same
        $this->assertEquals($response1->json(), $response2->json());

        // Check if only one MobileSyncQueue was created
        $this->assertDatabaseCount('mobile_sync_queues', 1);
    }
}
