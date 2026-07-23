<?php

namespace App\Console\Commands;

use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class BuatAdminPwnu extends Command
{
    protected $signature = 'buat:admin-pwnu {no_hp} {password} {nama}';
    protected $description = 'Buat akun admin PWNU';

    public function handle()
    {
        $user = AuthUser::create([
            'id_peran'           => 2,
            'no_hp'              => $this->argument('no_hp'),
            'kata_sandi'         => Hash::make($this->argument('password')),
            'status_akun'        => 'aktif',
            'is_tersedia'        => true,
            'default_scope_type' => 'pwnu',
            'default_scope_id'   => 1,
        ]);

        AuthPenggunaProfil::create([
            'id_pengguna'  => $user->id_pengguna,
            'nama_lengkap' => $this->argument('nama'),
        ]);

        $this->info("Admin PWNU berhasil dibuat: ID {$user->id_pengguna}, HP {$user->no_hp}");
    }
}
