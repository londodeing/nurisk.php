<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PimpinanPcnuKudusSeeder extends Seeder
{
    public function run(): void
    {
        $idPcnu = 1;

        $existing = DB::table('auth_users')
            ->join('auth_pengguna_profil', 'auth_users.id_pengguna', '=', 'auth_pengguna_profil.id_pengguna')
            ->where('auth_users.default_scope_type', 'pcnu')
            ->where('auth_users.default_scope_id', $idPcnu)
            ->where('auth_users.id_peran', 3)
            ->where('auth_pengguna_profil.nama_lengkap', 'Ketua NU Peduli PCNU Kudus')
            ->first();

        if ($existing) {
            $userId = $existing->id_pengguna;
            $this->command->info('Akun pimpinan PCNU Kudus sudah ada (ID: ' . $userId . ').');
        } else {
            $userId = DB::table('auth_users')->insertGetId([
                'id_peran'           => 3,
                'no_hp'              => '082200001001',
                'kata_sandi'         => Hash::make('password'),
                'status_akun'        => 'aktif',
                'is_tersedia'        => true,
                'default_scope_type' => 'pcnu',
                'default_scope_id'   => $idPcnu,
            ]);

            DB::table('auth_pengguna_profil')->insert([
                'id_pengguna'  => $userId,
                'nama_lengkap' => 'Ketua NU Peduli PCNU Kudus',
                'nik'          => '3319' . str_pad((string) rand(0, 99999999999999), 14, '0', STR_PAD_LEFT),
                'email'        => 'ketua.nupeduli@kudus.nu.or.id',
            ]);

            $this->command->info('Akun pimpinan NU Peduli PCNU Kudus berhasil dibuat (ID: ' . $userId . ').');
        }

        if (Schema::hasTable('master_jabatan')) {
            $jabatanKetuaPcnu = DB::table('master_jabatan')
                ->where('slug', 'ketua-pcnu')
                ->first();

            if ($jabatanKetuaPcnu) {
                DB::table('pengguna_jabatan')->updateOrInsert(
                    [
                        'id_pengguna'        => $userId,
                        'id_jabatan_posisi'  => $jabatanKetuaPcnu->id_jabatan_posisi,
                    ],
                    [
                        'tipe_lingkup' => 'pcnu',
                        'id_lingkup'   => $idPcnu,
                        'status_aktif' => true,
                        'ditugaskan_pada' => now(),
                    ]
                );
                $this->command->info('Jabatan ketua-pcnu diberikan ke user ID: ' . $userId);
            }
        }

        $this->command->warn('No. HP: 082200001001 | Password: password');
    }
}
