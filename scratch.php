<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$peran = \App\Models\Peran::where('nama_peran', 'trc')->first();
if ($peran) echo "TRC Level: " . $peran->level_otoritas . "\n";
else echo "Peran TRC not found.\n";
