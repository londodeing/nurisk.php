<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\MobileDevice;

class DeviceTokenRefreshTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_can_refresh_token()
    {
        $user = \App\Models\AuthUser::factory()->create();
        $device = MobileDevice::create([
            'uuid_device' => 'test-device-uuid',
            'id_pengguna' => $user->id_pengguna,
            'platform' => 'android',
            'app_version' => '1.0.0',
            'status' => 'active',
            'trust_score' => 100,
        ]);

        $response = $this->postJson('/api/v1/device/refresh-token', [
            'device_uuid' => 'test-device-uuid',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'device_token',
                         'expires_at',
                     ]
                 ]);

        $this->assertTrue($response->json('success'));
        $this->assertNotNull($response->json('data.device_token'));
    }

    public function test_it_rejects_inactive_device()
    {
        $user = \App\Models\AuthUser::factory()->create();
        $device = MobileDevice::create([
            'uuid_device' => 'inactive-device-uuid',
            'id_pengguna' => $user->id_pengguna,
            'platform' => 'android',
            'app_version' => '1.0.0',
            'status' => 'revoked',
            'trust_score' => 10,
        ]);

        $response = $this->postJson('/api/v1/device/refresh-token', [
            'device_uuid' => 'inactive-device-uuid',
        ]);

        $response->assertStatus(403);
    }
}
