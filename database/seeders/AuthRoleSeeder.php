<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id_peran' => 1,
                'nama_peran' => 'super_admin',
                'deskripsi' => 'IT Support & Full System Control',
                'level_otoritas' => 1,
            ],
            [
                'id_peran' => 2,
                'nama_peran' => 'pwnu',
                'deskripsi' => 'Pengguna level PWNU Jawa Tengah — akses provinsi',
                'level_otoritas' => 2,
            ],
            [
                'id_peran' => 3,
                'nama_peran' => 'pcnu',
                'deskripsi' => 'Pengguna level PCNU Cabang — akses scope kabupaten',
                'level_otoritas' => 3,
            ],
            [
                'id_peran' => 4,
                'nama_peran' => 'relawan',
                'deskripsi' => 'Pengguna level Relawan — akses lapangan terbatas',
                'level_otoritas' => 4,
            ],
            [
                'id_peran' => 5,
                'nama_peran' => 'publik',
                'deskripsi' => 'Masyarakat umum / warga pelapor',
                'level_otoritas' => 5,
            ],
            [
                'id_peran' => 6,
                'nama_peran' => 'trc',
                'deskripsi' => 'Tim Reaksi Cepat — Respon Darurat Pertama',
                'level_otoritas' => 4,
            ],
            [
                'id_peran' => 7,
                'nama_peran' => 'kandidat_admin_pcnu',
                'deskripsi' => 'Menunggu verifikasi untuk menjadi Admin PCNU',
                'level_otoritas' => 5,
            ],
            [
                'id_peran' => 8,
                'nama_peran' => 'kandidat_admin_pwnu',
                'deskripsi' => 'Menunggu verifikasi untuk menjadi Admin PWNU',
                'level_otoritas' => 5,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('auth_roles')->updateOrInsert(
                ['id_peran' => $role['id_peran']],
                [
                    'nama_peran' => $role['nama_peran'],
                    'deskripsi' => $role['deskripsi'],
                    'level_otoritas' => $role['level_otoritas'],
                ]
            );
            
            \Spatie\Permission\Models\Role::firstOrCreate(['id' => $role['id_peran'], 'name' => $role['nama_peran'], 'guard_name' => 'api']);
        }
    }
}
