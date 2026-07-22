<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = \App\Models\AuthUser::find(58);
echo \Illuminate\Support\Facades\Hash::check('password', $u->kata_sandi) ? "ID 58 matches 'password'\n" : "ID 58 doesn't match 'password'\n";
