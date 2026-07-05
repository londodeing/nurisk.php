<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UatSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Organisasi Unit
        DB::table('organisasi_unit')->updateOrInsert(
            ['id_unit' => 1],
            ['nama_unit' => 'PCNU Demak', 'tipe_unit' => 'pcnu']
        );

        // 2. Create PCNU
        DB::table('organisasi_pcnu')->updateOrInsert(
            ['id_pcnu' => 1],
            ['nama_pcnu' => 'PCNU Kabupaten Demak', 'id_unit' => 1]
        );

        // 3. Create Admin PCNU Demak
        $pcnuAdminId = DB::table('auth_users')->insertGetId([
            'id_peran'           => 3, // pcnu
            'no_hp'              => 'adminpcnu',
            'kata_sandi'         => Hash::make('password'),
            'status_akun'        => 'aktif',
            'is_tersedia'        => true,
            'default_scope_type' => 'pcnu',
            'default_scope_id'   => 1,
        ]);

        DB::table('auth_pengguna_profil')->insert([
            'id_pengguna'  => $pcnuAdminId,
            'nama_lengkap' => 'Admin PCNU Demak',
        ]);
    }
}
