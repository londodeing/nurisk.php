<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Services\Auth\AuthenticationService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthenticationServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected AuthenticationService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = new AuthenticationService();

        // Bangun skema tabel auth_roles & auth_users di SQLite in-memory untuk testing

        // Bangun skema tabel auth_roles & auth_users di SQLite in-memory untuk testing




        // Buat role dasar super_admin
        AuthRole::firstOrCreate(['nama_peran' => 'super_admin'], ['level_otoritas' => 1]);
    }

    /**
     * Test login berhasil dengan kredensial valid dan status aktif.
     */
    public function test_login_successful_with_active_user(): void
    {
        $user = AuthUser::factory()->create([
            'id_peran' => 1,
            'no_hp' => '081234567890',
            'kata_sandi' => Hash::make('PasswordAman123!'),
            'status_akun' => 'aktif'
        ]);

        $result = $this->authService->login([
            'no_hp' => '081234567890',
            'kata_sandi' => 'PasswordAman123!'
        ]);

        $this->assertTrue($result);
        $this->assertEquals($user->id_pengguna, Auth::id());
        $this->assertNotNull(AuthUser::find($user->id_pengguna)->terakhir_masuk);
    }

    /**
     * Test login gagal karena password salah.
     */
    public function test_login_fails_with_invalid_password(): void
    {
        $user = AuthUser::factory()->create([
            'id_peran' => 1,
            'no_hp' => '081234567890',
            'kata_sandi' => Hash::make('PasswordAman123!'),
            'status_akun' => 'aktif'
        ]);

        $this->expectException(ValidationException::class);

        $this->authService->login([
            'no_hp' => '081234567890',
            'kata_sandi' => 'SalahPassword!'
        ]);
    }

    /**
     * Test login gagal karena status akun non-aktif (menunggu).
     */
    public function test_login_fails_when_user_is_not_active(): void
    {
        $user = AuthUser::factory()->create([
            'id_peran' => 1,
            'no_hp' => '081234567890',
            'kata_sandi' => Hash::make('PasswordAman123!'),
            'status_akun' => 'menunggu' // Status akun default/belum aktif
        ]);

        $this->expectException(ValidationException::class);

        $this->authService->login([
            'no_hp' => '081234567890',
            'kata_sandi' => 'PasswordAman123!'
        ]);
    }
}
