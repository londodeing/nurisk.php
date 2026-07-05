<?php

use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use App\Models\AuthRole;
use App\Models\WilayahDesa;
use App\Models\PenggunaJabatan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$password = Hash::make('password');

$roleRelawan = AuthRole::where('nama_peran', 'relawan')->first()->id_peran;
$roleTrc = AuthRole::where('nama_peran', 'trc')->first()->id_peran;

$desa = WilayahDesa::first();
$id_desa = $desa ? $desa->id_desa : '3301012001';

$createUser = function($nama, $hp, $roleId, $jabatanId = null) use ($password, $id_desa) {
    // cek jika sdh ada
    $user = AuthUser::where('no_hp', $hp)->first();
    if($user) {
        return $user;
    }

    $user = AuthUser::create([
        'id_peran' => $roleId,
        'no_hp' => $hp,
        'kata_sandi' => $password,
        'status_akun' => AuthUser::STATUS_AKTIF,
        'status_ketersediaan' => AuthUser::READINESS_READY,
        'is_tersedia' => true,
        'default_scope_type' => 'pcnu',
        'default_scope_id' => 1, // PCNU Kudus
    ]);

    AuthPenggunaProfil::create([
        'id_pengguna' => $user->id_pengguna,
        'nama_lengkap' => $nama,
        'nik' => '3319' . rand(100000000000, 999999999999),
        'email' => strtolower(str_replace(' ', '_', $nama)) . "@kudus.local",
        'tempat_lahir' => 'Kudus',
        'tanggal_lahir' => '1990-01-01',
        'jenis_kelamin' => 'L',
        'alamat' => 'Alamat ' . $nama,
        'id_desa_domisili' => $id_desa,
        'profesi' => 'Relawan',
        'pengalaman_kebencanaan' => '-',
    ]);

    if ($jabatanId) {
        PenggunaJabatan::create([
            'id_pengguna' => $user->id_pengguna,
            'id_jabatan_posisi' => $jabatanId,
            'tipe_lingkup' => 'pcnu',
            'id_lingkup' => 1,
            'ditugaskan_pada' => now(),
            'status_aktif' => 1,
        ]);
    }
    
    // Add Spatie role if applicable
    if ($jabatanId == 11) {
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $spatieRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'koordinator_pos_aju', 'guard_name' => 'api']);
            \Illuminate\Support\Facades\DB::table('model_has_roles')->insertOrIgnore([
                'role_id' => $spatieRole->id,
                'model_type' => AuthUser::class,
                'model_id' => $user->id_pengguna,
            ]);
        }
    } else if ($jabatanId == 12 || $jabatanId == 13) {
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $spatieRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'koordinator_klaster', 'guard_name' => 'api']);
            \Illuminate\Support\Facades\DB::table('model_has_roles')->insertOrIgnore([
                'role_id' => $spatieRole->id,
                'model_type' => AuthUser::class,
                'model_id' => $user->id_pengguna,
            ]);
        }
    }

    return $user;
};

$createUser('Akun Uji TRC Kudus', '081211111111', $roleTrc, 10); // Anggota TRC PCNU (id=10)
$createUser('Akun Uji Koor Pos Aju', '081222222222', $roleRelawan, 11); // Komandan Pos Aju (id=11)
$createUser('Akun Uji Koor Klaster', '081233333333', $roleRelawan, 12); // Koordinator Logistik (id=12)
$createUser('Akun Uji Relawan', '081244444444', $roleRelawan, 15); // Relawan Umum (id=15)

echo "Done\n";
