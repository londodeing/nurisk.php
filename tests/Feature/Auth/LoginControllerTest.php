<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoginControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Bangun skema tabel auth_roles & auth_users di SQLite in-memory untuk testing




        // Buat role dasar super_admin
        AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);
    }

    /**
     * Test menampilkan halaman form login.
     */
    public function test_login_form_can_be_rendered(): void
    {
        $response = $this->get('/login');

        // Note: Route /login akan kita aktifkan di AUTH-009, namun test ini memicu controller showLoginForm langsung.
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test login berhasil me-redirect ke dashboard.
     */
    public function test_login_redirects_to_dashboard_on_success(): void
    {
        $user = AuthUser::factory()->create([
            'id_peran' => 1,
            'no_hp' => '081234567890',
            'kata_sandi' => Hash::make('PasswordAman123!'),
            'status_akun' => AuthUser::STATUS_AKTIF
        ]);

        $response = $this->post('/login', [
            'no_hp' => '081234567890',
            'kata_sandi' => 'PasswordAman123!'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login gagal karena kredensial salah mengembalikan back redirect dengan error.
     */
    public function test_login_redirects_back_with_errors_on_failure(): void
    {
        AuthUser::factory()->create([
            'id_peran' => 1,
            'no_hp' => '081234567890',
            'kata_sandi' => Hash::make('PasswordAman123!'),
            'status_akun' => AuthUser::STATUS_AKTIF
        ]);

        $response = $this->post('/login', [
            'no_hp' => '081234567890',
            'kata_sandi' => 'SalahPassword!'
        ]);

        $response->assertSessionHasErrors('no_hp');
        $this->assertGuest();
    }
}
