<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pcnu = \Illuminate\Support\Facades\DB::table('organisasi_pcnu')->get();
foreach ($pcnu as $p) echo "{$p->id_pcnu} - {$p->nama_pcnu}\n";
