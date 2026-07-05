<?php

namespace Tests\Feature;

use Tests\TestCase;
use Database\Seeders\AuthRoleSeeder;
use Database\Seeders\AuthUserSeeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthUserSeederTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Bangun skema tabel auth_roles & auth_users di SQLite in-memory untuk testing



    }

    /**
     * Test bahwa AuthUserSeeder berhasil membuat akun super_admin awal secara idempotent.
     */
    public function test_auth_user_seeder_creates_super_admin_successfully(): void
    {
        // 1. Jalankan seeder peran dulu untuk integritas
        $this->seed(AuthRoleSeeder::class);

        // 2. Jalankan seeder user
        $this->seed(AuthUserSeeder::class);

        // Pastikan super_admin awal terdaftar di DB
        $this->assertDatabaseHas('auth_users', [
            'id_peran' => 1,
            'no_hp' => '081111111111',
            'status_akun' => 'aktif',
        ]);

        // Verifikasi password hashing valid
        $user = DB::table('auth_users')->where('no_hp', '081111111111')->first();
        $this->assertTrue(Hash::check('PasswordSuperAdmin123!', $user->kata_sandi));

        // 3. Jalankan kembali seeder user untuk memverifikasi idempotensi
        $this->seed(AuthUserSeeder::class);

        // Jumlah super_admin dengan no_hp tersebut harus tetap 1
        $this->assertEquals(1, DB::table('auth_users')->where('no_hp', '081111111111')->count());
    }
}
