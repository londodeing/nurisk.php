<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES");
foreach ($tables as $t) {
    $prop = "Tables_in_nurisk";
    echo $t->$prop . "\n";
}
