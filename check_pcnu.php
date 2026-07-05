<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM organisasi_pcnu");
foreach ($cols as $c) echo "{$c->Field} - {$c->Type} - {$c->Key}\n";
