<?php

namespace Tests\Feature;

use App\Models\AuthRole;
use App\Models\AuthUser;
use App\Models\MobileDevice;
use Database\Seeders\MasterKlasterSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeviceApiTest extends TestCase
{
    use DatabaseTransactions;

    private string $token;
    private AuthUser $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterKlasterSeeder::class);

        $role = AuthRole::firstOrCreate(
            ['nama_peran' => 'super_admin'],
            ['deskripsi' => 'Super Admin', 'level_otoritas' => 100]
        );
        $user = AuthUser::factory()->aktif()->create(['id_peran' => $role->id_peran]);
        $this->user = $user;
        $this->token = $user->createToken('test-token', ['*'])->plainTextToken;
    }

    /** @test */
    public function it_lists_devices_for_authenticated_user()
    {
        MobileDevice::factory()->count(3)->create([
            'id_pengguna' => $this->user->id_pengguna,
            'uuid_device' => fn() => (string) \Illuminate\Support\Str::uuid(),
        ]);

        $response = $this->getJson('/api/v1/devices', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_rejects_unauthenticated_access()
    {
        $response = $this->getJson('/api/v1/devices');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_revokes_a_specific_device()
    {
        $device = MobileDevice::factory()->create(['id_pengguna' => $this->user->id_pengguna]);

        // Create a Sanctum token associated with this device
        $deviceToken = $this->user->createToken('device-token', ['*']);
        $deviceToken->accessToken->device_uuid = $device->uuid_device;
        $deviceToken->accessToken->save();

        $response = $this->deleteJson("/api/v1/devices/{$device->uuid_device}", [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('revoked', $device->fresh()->status);
    }

    /** @test */
    public function it_logs_out_all_devices()
    {
        MobileDevice::factory()->count(3)->create([
            'id_pengguna' => $this->user->id_pengguna,
            'uuid_device' => fn() => (string) \Illuminate\Support\Str::uuid(),
        ]);

        $response = $this->postJson('/api/v1/devices/logout-all', [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
        $this->assertEquals(3, $this->user->mobileDevices()->where('status', 'revoked')->count());
    }
}
