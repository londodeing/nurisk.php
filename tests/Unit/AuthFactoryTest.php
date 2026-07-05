<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Foundation\Testing\WithFaker;

class AuthFactoryTest extends TestCase
{
    use WithFaker;

    /**
     * Test pembuatan role menggunakan AuthRoleFactory.
     */
    public function test_auth_role_factory_creates_instance(): void
    {
        $role = AuthRole::firstOrNew(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);

        $this->assertInstanceOf(AuthRole::class, $role);
        $this->assertNotEmpty($role->nama_peran);
        $this->assertNotEmpty($role->level_otoritas);
    }

    /**
     * Test pembuatan user menggunakan AuthUserFactory dengan status default (menunggu).
     */
    public function test_auth_user_factory_creates_instance_with_default_status(): void
    {
        $user = AuthUser::factory()->make([
            'id_peran' => 1
        ]);

        $this->assertInstanceOf(AuthUser::class, $user);
        $this->assertEquals('menunggu', $user->status_akun);
        $this->assertNotEmpty($user->no_hp);
        $this->assertNotEmpty($user->kata_sandi);
    }

    /**
     * Test state 'aktif' pada AuthUserFactory.
     */
    public function test_auth_user_factory_aktif_state(): void
    {
        $user = AuthUser::factory()->aktif()->make([
            'id_peran' => 1
        ]);

        $this->assertEquals('aktif', $user->status_akun);
    }
}
