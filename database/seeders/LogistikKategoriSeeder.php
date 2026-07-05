<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LogistikKategoriSeeder extends Seeder
{
    public function run()
    {
        DB::table('logistik_kategori')->insert([
            ['id_kategori' => 1, 'nama_kategori' => 'Pangan / Konsumsi'],
            ['id_kategori' => 2, 'nama_kategori' => 'Sandang / Pakaian'],
            ['id_kategori' => 3, 'nama_kategori' => 'Kesehatan / Medis'],
            ['id_kategori' => 4, 'nama_kategori' => 'Hunian / Shelter'],
            ['id_kategori' => 5, 'nama_kategori' => 'Kebersihan / Hygiene'],
            ['id_kategori' => 6, 'nama_kategori' => 'Peralatan / Tool'],
            ['id_kategori' => 7, 'nama_kategori' => 'Lain-lain'],
        ]);
    }
}
