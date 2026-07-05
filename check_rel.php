<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pcnu = \Illuminate\Support\Facades\DB::table('organisasi_pcnu')
    ->join('organisasi_unit', 'organisasi_pcnu.id_unit', '=', 'organisasi_unit.id_unit')
    ->join('wilayah_kabupaten', 'organisasi_unit.id_wilayah', '=', 'wilayah_kabupaten.id_kab')
    ->select('organisasi_pcnu.id_pcnu', 'organisasi_pcnu.nama_pcnu', 'wilayah_kabupaten.nama_kab')
    ->limit(5)
    ->get();

print_r($pcnu);
