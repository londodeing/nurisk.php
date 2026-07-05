<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;

class AuthModelTest extends TestCase
{
    /**
     * Test konfigurasi dasar Model AuthRole.
     */
    public function test_auth_role_model_configuration(): void
    {
        $role = new AuthRole();

        $this->assertEquals('auth_roles', $role->getTable());
        $this->assertEquals('id_peran', $role->getKeyName());
        $this->assertFalse($role->timestamps);
    }

    /**
     * Test konfigurasi dasar Model AuthUser.
     */
    public function test_auth_user_model_configuration(): void
    {
        $user = new AuthUser();

        $this->assertEquals('auth_users', $user->getTable());
        $this->assertEquals('id_pengguna', $user->getKeyName());
        $this->assertEquals('dibuat_pada', AuthUser::CREATED_AT);
        $this->assertEquals('diperbarui_pada', AuthUser::UPDATED_AT);
    }

    /**
     * Test override password autentikasi AuthUser.
     */
    public function test_auth_user_password_override(): void
    {
        $user = new AuthUser();
        $user->kata_sandi = 'hashed_secure_password';

        $this->assertEquals('hashed_secure_password', $user->getAuthPassword());
    }
}
