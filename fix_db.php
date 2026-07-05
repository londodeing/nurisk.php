<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\Illuminate\Support\Facades\DB::statement("ALTER TABLE laporan_kejadian DROP COLUMN alamat_lengkap, DROP COLUMN id_pcnu");
