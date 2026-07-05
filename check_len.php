<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM auth_users LIKE 'kata_sandi'");
print_r($cols);

$users = \App\Models\AuthUser::where('no_hp', '08111111111')->get();
foreach($users as $u) {
    echo "kata_sandi length: " . strlen($u->kata_sandi) . "\n";
    echo "hash checks out? " . (\Illuminate\Support\Facades\Hash::check('12345678', $u->kata_sandi) ? 'YES' : 'NO') . "\n";
}
