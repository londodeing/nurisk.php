<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$hps = ['08111111111', '08222222222', '08333333333', '08444444444', '08555555555'];
$newPass = \Illuminate\Support\Facades\Hash::make('12345678');
\App\Models\AuthUser::whereIn('no_hp', $hps)->update(['kata_sandi' => $newPass]);
echo "Updated passwords for dummy accounts to 12345678\n";
