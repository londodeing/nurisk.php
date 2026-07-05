<?php

namespace Tests\Feature;

use Tests\TestCase;
use Database\Seeders\AuthRoleSeeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthRoleSeederTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Bangun skema tabel auth_roles secara dinamis di SQLite in-memory untuk testing database-first

    }

    /**
     * Test bahwa AuthRoleSeeder berhasil berjalan, menginput tepat 5 data, dan idempotent.
     */
    public function test_auth_role_seeder_is_successful_and_idempotent(): void
    {
        // 1. Jalankan seeder pertama kali
        $this->seed(AuthRoleSeeder::class);

        // Pastikan tabel memiliki tepat 8 baris
        $this->assertEquals(8, DB::table('auth_roles')->count());

        // Verifikasi keberadaan kelima peran PRD
        $this->assertDatabaseHas('auth_roles', ['id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1]);
        $this->assertDatabaseHas('auth_roles', ['id_peran' => 2, 'nama_peran' => 'pwnu', 'level_otoritas' => 2]);
        $this->assertDatabaseHas('auth_roles', ['id_peran' => 3, 'nama_peran' => 'pcnu', 'level_otoritas' => 3]);
        $this->assertDatabaseHas('auth_roles', ['id_peran' => 4, 'nama_peran' => 'relawan', 'level_otoritas' => 4]);
        $this->assertDatabaseHas('auth_roles', ['id_peran' => 5, 'nama_peran' => 'publik', 'level_otoritas' => 5]);
        $this->assertDatabaseHas('auth_roles', ['id_peran' => 6, 'nama_peran' => 'trc', 'level_otoritas' => 4]);
        $this->assertDatabaseHas('auth_roles', ['id_peran' => 7, 'nama_peran' => 'kandidat_admin_pcnu', 'level_otoritas' => 5]);
        $this->assertDatabaseHas('auth_roles', ['id_peran' => 8, 'nama_peran' => 'kandidat_admin_pwnu', 'level_otoritas' => 5]);

        // 2. Jalankan seeder kedua kali untuk membuktikan idempotensi (tidak ada duplikasi)
        $this->seed(AuthRoleSeeder::class);

        // Jumlah baris harus tetap 8
        $this->assertEquals(8, DB::table('auth_roles')->count());
    }
}
