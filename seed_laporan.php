<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$jenis = \App\Models\BencanaMasterJenis::first();
if (!$jenis) {
    $jenis = \App\Models\BencanaMasterJenis::create([
        'nama_jenis' => 'Banjir',
        'kategori_bencana' => 'alam',
        'is_aktif' => 1
    ]);
}

\App\Models\LaporanKejadian::create([
    'kode_kejadian' => \App\Models\LaporanKejadian::generateKodeKejadian(),
    'waktu_kejadian' => now(),
    'id_jenis_bencana' => $jenis->id_jenis,
    'titik_kenal' => 'Desa Suka Maju, Kec. Suka Terus, RT 01/RW 02',
    'keterangan_situasi' => 'Terjadi banjir setinggi lutut orang dewasa sejak pukul 03:00 pagi akibat hujan deras semalaman.',
    'is_valid' => 'menunggu',
    'nama_pelapor' => 'Budi Santoso',
    'hp_pelapor' => '08999999999',
    'latitude' => -7.250445,
    'longitude' => 112.768845,
]);

\App\Models\LaporanKejadian::create([
    'kode_kejadian' => \App\Models\LaporanKejadian::generateKodeKejadian(),
    'waktu_kejadian' => now(),
    'id_jenis_bencana' => $jenis->id_jenis,
    'titik_kenal' => 'Jalan Lintas Selatan KM 15',
    'keterangan_situasi' => 'Pohon tumbang menutup seluruh badan jalan, macet total dari dua arah.',
    'is_valid' => 'menunggu',
    'nama_pelapor' => 'Ahmad',
    'hp_pelapor' => '08777777777',
    'latitude' => -7.260445,
    'longitude' => 112.778845,
]);

echo "2 Laporan Kejadian (dummy) berhasil dibuat.\n";
