<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mendapatkan password dari env, fallback ke password default jika tidak diset
        $password = env('SUPER_ADMIN_PASSWORD', 'PasswordSuperAdmin123!');

        DB::table('auth_users')->updateOrInsert(
            ['no_hp' => '081111111111'],
            [
                'id_peran' => 1, // super_admin
                'id_unit' => null, // null allowed, unit tidak wajib untuk bootstrap super_admin
                'kata_sandi' => Hash::make($password),
                'status_akun' => 'aktif',
                'is_tersedia' => 1,
                'default_scope_type' => null,
                'default_scope_id' => null,
                'dibuat_pada' => now(),
                'diperbarui_pada' => now(),
            ]
        );
    }
}
