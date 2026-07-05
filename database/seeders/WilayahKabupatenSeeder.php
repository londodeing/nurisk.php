<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahKabupatenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kabupatens = [
            ['id_kab' => '3301', 'nama_kab' => 'Kabupaten Cilacap', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3302', 'nama_kab' => 'Kabupaten Banyumas', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3303', 'nama_kab' => 'Kabupaten Purbalingga', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3304', 'nama_kab' => 'Kabupaten Banjarnegara', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3305', 'nama_kab' => 'Kabupaten Kebumen', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3306', 'nama_kab' => 'Kabupaten Purworejo', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3307', 'nama_kab' => 'Kabupaten Wonosobo', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3308', 'nama_kab' => 'Kabupaten Magelang', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3309', 'nama_kab' => 'Kabupaten Boyolali', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3310', 'nama_kab' => 'Kabupaten Klaten', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3311', 'nama_kab' => 'Kabupaten Sukoharjo', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3312', 'nama_kab' => 'Kabupaten Wonogiri', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3313', 'nama_kab' => 'Kabupaten Karanganyar', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3314', 'nama_kab' => 'Kabupaten Sragen', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3315', 'nama_kab' => 'Kabupaten Grobogan', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3316', 'nama_kab' => 'Kabupaten Blora', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3317', 'nama_kab' => 'Kabupaten Rembang', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3318', 'nama_kab' => 'Kabupaten Pati', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3319', 'nama_kab' => 'Kabupaten Kudus', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3320', 'nama_kab' => 'Kabupaten Jepara', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3321', 'nama_kab' => 'Kabupaten Demak', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3322', 'nama_kab' => 'Kabupaten Semarang', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3323', 'nama_kab' => 'Kabupaten Temanggung', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3324', 'nama_kab' => 'Kabupaten Kendal', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3325', 'nama_kab' => 'Kabupaten Batang', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3326', 'nama_kab' => 'Kabupaten Pekalongan', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3327', 'nama_kab' => 'Kabupaten Pemalang', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3328', 'nama_kab' => 'Kabupaten Tegal', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3329', 'nama_kab' => 'Kabupaten Brebes', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3371', 'nama_kab' => 'Kota Magelang', 'tipe' => 'Kota'],
            ['id_kab' => '3372', 'nama_kab' => 'Kota Surakarta', 'tipe' => 'Kota'],
            ['id_kab' => '3373', 'nama_kab' => 'Kota Salatiga', 'tipe' => 'Kota'],
            ['id_kab' => '3374', 'nama_kab' => 'Kota Semarang', 'tipe' => 'Kota'],
            ['id_kab' => '3375', 'nama_kab' => 'Kota Pekalongan', 'tipe' => 'Kota'],
            ['id_kab' => '3376', 'nama_kab' => 'Kota Tegal', 'tipe' => 'Kota'],
        ];

        foreach ($kabupatens as $kab) {
            DB::table('wilayah_kabupaten')->updateOrInsert(
                ['id_kab' => $kab['id_kab']],
                [
                    'nama_kab' => $kab['nama_kab'],
                    'tipe' => $kab['tipe'],
                ]
            );
        }
    }
}
