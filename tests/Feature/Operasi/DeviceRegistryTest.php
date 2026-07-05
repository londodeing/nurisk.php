<?php

namespace Tests\Feature\Operasi;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\MobileDevice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeviceRegistryTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole(string $roleName): AuthUser
    {
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);
        return AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran
        ]);
    }

    public function test_device_auto_registration_on_sync()
    {
        $user = $this->createAuthUserWithRole('super_admin');

        $response = $this->actingAs($user)
            ->postJson(route('api.v1.sync'), [
                'request_id' => \Illuminate\Support\Str::uuid()->toString(),
                'device_uuid' => 'test-device-uuid-123',
                'cursors' => ['assessment' => 0],
                'changes' => []
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('mobile_devices', [
            'uuid_device' => 'test-device-uuid-123',
            'id_pengguna' => $user->id_pengguna,
            'status' => 'active'
        ]);
    }

    public function test_revoked_device_is_forbidden_to_sync()
    {
        $user = $this->createAuthUserWithRole('super_admin');
        
        $device = MobileDevice::create([
            'uuid_device' => 'revoked-device-uuid',
            'id_pengguna' => $user->id_pengguna,
            'platform' => 'android',
            'app_version' => '1.0.0',
            'status' => 'revoked'
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('api.v1.sync'), [
                'request_id' => \Illuminate\Support\Str::uuid()->toString(),
                'device_uuid' => 'revoked-device-uuid',
                'cursors' => ['assessment' => 0],
                'changes' => []
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden: Device has been revoked');
    }

    public function test_device_uuid_is_unique()
    {
        $user = $this->createAuthUserWithRole('super_admin');

        MobileDevice::create([
            'uuid_device' => 'unique-device-001',
            'id_pengguna' => $user->id_pengguna,
            'platform' => 'android',
            'app_version' => '1.0.0',
            'status' => 'active',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->expectExceptionMessageMatches('/unique|duplicate|Integrity/i');

        MobileDevice::create([
            'uuid_device' => 'unique-device-001',
            'id_pengguna' => $user->id_pengguna,
            'platform' => 'ios',
            'app_version' => '1.0.0',
            'status' => 'active',
        ]);
    }

    public function test_device_token_rotation_updates_expiry()
    {
        $user = $this->createAuthUserWithRole('super_admin');

        $device = MobileDevice::create([
            'uuid_device' => 'token-rotation-device',
            'id_pengguna' => $user->id_pengguna,
            'platform' => 'android',
            'app_version' => '1.0.0',
            'status' => 'active',
            'device_token' => 'old-token',
            'token_expires_at' => now()->subDay(),
        ]);

        // Simulate token rotation
        $device->device_token = 'new-token-xyz';
        $device->token_expires_at = now()->addDays(30);
        $device->save();

        $device->refresh();

        $this->assertEquals('new-token-xyz', $device->device_token);
        $this->assertTrue($device->token_expires_at->isFuture());
    }

    public function test_inactive_device_is_forbidden_to_sync()
    {
        $user = $this->createAuthUserWithRole('super_admin');

        MobileDevice::create([
            'uuid_device' => 'inactive-device-uuid',
            'id_pengguna' => $user->id_pengguna,
            'platform' => 'android',
            'app_version' => '1.0.0',
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('api.v1.sync'), [
                'request_id' => \Illuminate\Support\Str::uuid()->toString(),
                'device_uuid' => 'inactive-device-uuid',
                'cursors' => ['assessment' => 0],
                'changes' => []
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Forbidden: Device has been inactive');
    }
}
