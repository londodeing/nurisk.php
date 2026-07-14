<?php

namespace Tests\Feature\Profil;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\AuthPenggunaProfil;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ToggleTersediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->postJson('/api/v1/profil/toggle-tersedia', [
            'id_pengguna' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_toggle_flips_is_tersedia_from_1_to_0(): void
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
            'is_tersedia' => 1,
            'status_akun' => 'aktif',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profil/toggle-tersedia');

        $response->assertStatus(200);
        $response->assertJson([
            'type' => 'reload_scene',
            'scene_id' => 'akun',
            'is_tersedia_before' => true,
            'is_tersedia_after' => false,
        ]);

        $freshUser = $user->fresh();
        $this->assertFalse((bool) $freshUser->is_tersedia);
        $this->assertEquals(0, $freshUser->is_tersedia);
    }

    public function test_toggle_flips_is_tersedia_from_0_to_1(): void
    {
        $role = AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => $role->id_peran,
            'is_tersedia' => 0,
            'status_akun' => 'aktif',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profil/toggle-tersedia');

        $response->assertStatus(200);
        $response->assertJson([
            'type' => 'reload_scene',
            'scene_id' => 'akun',
            'is_tersedia_before' => false,
            'is_tersedia_after' => true,
        ]);

        $freshUser = $user->fresh();
        $this->assertTrue((bool) $freshUser->is_tersedia);
        $this->assertEquals(1, $freshUser->is_tersedia);
    }

    public function test_toggle_works_for_all_roles(): void
    {
        $roles = ['super_admin', 'pwnu', 'pcnu', 'relawan', 'trc'];

        foreach ($roles as $roleName) {
            $role = AuthRole::factory()->create(['nama_peran' => $roleName]);
            $user = AuthUser::factory()->aktif()->create([
                'id_peran' => $role->id_peran,
                'is_tersedia' => 1,
                'status_akun' => 'aktif',
            ]);

            $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profil/toggle-tersedia');
            $response->assertStatus(200);

            $this->assertFalse((bool) $user->fresh()->is_tersedia, "Failed for role: $roleName");
        }
    }
}
