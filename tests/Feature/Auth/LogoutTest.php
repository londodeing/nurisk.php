<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LogoutTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Bangun skema tabel auth_roles & auth_users di SQLite in-memory untuk testing




        AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);
    }

    /**
     * Test pengguna berhasil logout, session dibersihkan, dan di-redirect ke halaman login.
     */
    public function test_authenticated_user_can_logout_successfully(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => 1
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
