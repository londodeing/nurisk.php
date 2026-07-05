<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoginRouteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Bangun skema tabel auth_roles & auth_users di SQLite in-memory untuk testing




        AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);
    }

    /**
     * Test guest (user belum login) dapat mengakses halaman login.
     */
    public function test_guest_can_access_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test user yang sudah terautentikasi dilarang mengakses halaman login (akan di-redirect).
     */
    public function test_authenticated_user_cannot_access_login_page(): void
    {
        $user = AuthUser::factory()->aktif()->create([
            'id_peran' => 1
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }
}
