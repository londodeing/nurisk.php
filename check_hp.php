<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\AuthUser::where('no_hp', '08111111111')->get();
echo "Count: " . $users->count() . "\n";
foreach($users as $u) {
    echo "ID: {$u->id_pengguna} | HP: {$u->no_hp} | Hash: " . substr($u->kata_sandi, 0, 10) . " | OK? " . (\Illuminate\Support\Facades\Hash::check('password', $u->kata_sandi) ? 'YES' : 'NO') . "\n";
}
