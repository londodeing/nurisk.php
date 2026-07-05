<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\OperasiInsiden;

DB::table('organisasi_unit')->insertOrIgnore([
    'id_unit' => 99,
    'nama_unit' => 'PWNU Jatim',
    'tipe_unit' => 'pwnu',
    'parent_id' => null
]);

DB::table('organisasi_unit')->insertOrIgnore([
    'id_unit' => 1,
    'nama_unit' => 'PCNU Cilacap',
    'tipe_unit' => 'pcnu',
    'parent_id' => 99
]);

DB::table('organisasi_pcnu')->insertOrIgnore([
    'id_pcnu' => 1,
    'id_unit' => 1,
    'nama_pcnu' => 'PCNU Cilacap'
]);

DB::table('bencana_master_jenis')->insertOrIgnore([
    'id_jenis' => 11,
    'nama_bencana' => 'Likuefaksi',
    'slug' => 'likuefaksi',
    'kategori' => 'alam'
]);

$insiden = OperasiInsiden::firstOrCreate(
    ['id_insiden' => 1],
    [
        'id_pcnu' => 1,
        'kode_kejadian' => 'INS-123456',
        'id_jenis_bencana' => 11,
        'status_insiden' => 'respon',
        'prioritas' => 'sedang',
        'waktu_mulai' => now()
    ]
);

echo "Done! Insiden UUID: " . $insiden->uuid_insiden . "\n";
