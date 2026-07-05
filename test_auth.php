<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\AuthUser::factory()->aktif()->create();
echo "User created. ID: {$user->id_pengguna}\n";
