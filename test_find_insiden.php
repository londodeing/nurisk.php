<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$insiden = \App\Models\OperasiInsiden::factory()->create(['status_insiden' => 'respon']);
echo "Created Insiden UUID: " . $insiden->uuid_insiden . "\n";
$insidenDb = \App\Models\OperasiInsiden::where('uuid_insiden', $insiden->uuid_insiden)->first();
echo "Found DB: " . ($insidenDb ? 'YES' : 'NO') . "\n";
