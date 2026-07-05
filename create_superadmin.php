<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\AuthPenggunaProfil;
use Illuminate\Support\Facades\Hash;

$role = AuthRole::updateOrCreate(
    ['nama_peran' => 'super_admin'],
    [
        'level_otoritas' => 100, // Highest level
        'deskripsi' => 'Administrator Tertinggi'
    ]
);

$noHp = '081234567890';
$password = 'SuperAdmin123!';

$user = AuthUser::updateOrCreate(
    ['no_hp' => $noHp],
    [
        'id_peran' => $role->id_peran,
        'kata_sandi' => Hash::make($password),
        'status_akun' => AuthUser::STATUS_AKTIF,
        'is_tersedia' => 1,
    ]
);

AuthPenggunaProfil::updateOrCreate(
    ['id_pengguna' => $user->id_pengguna],
    [
        'nama_lengkap' => 'Super Administrator',
        'email' => 'superadmin@nurisk.test',
    ]
);

echo "Superadmin created successfully.\n";
echo "No HP: {$noHp}\n";
echo "Password: {$password}\n";
