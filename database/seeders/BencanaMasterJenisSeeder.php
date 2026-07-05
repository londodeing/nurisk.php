<?php

namespace Database\Seeders;

use App\Models\BencanaMasterJenis;
use Illuminate\Database\Seeder;

class BencanaMasterJenisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BencanaMasterJenis::upsert([
            ['id_jenis' => 1,  'nama_bencana' => 'Banjir',                     'slug' => 'banjir',                    'kategori' => 'alam'],
            ['id_jenis' => 2,  'nama_bencana' => 'Banjir Bandang',              'slug' => 'banjir-bandang',             'kategori' => 'alam'],
            ['id_jenis' => 3,  'nama_bencana' => 'Cuaca Ekstrem',               'slug' => 'cuaca-ekstrem',              'kategori' => 'alam'],
            ['id_jenis' => 4,  'nama_bencana' => 'Gelombang Ekstrem dan Abrasi','slug' => 'gelombang-ekstrem-abrasi',   'kategori' => 'alam'],
            ['id_jenis' => 5,  'nama_bencana' => 'Gempa Bumi',                  'slug' => 'gempa-bumi',                 'kategori' => 'alam'],
            ['id_jenis' => 6,  'nama_bencana' => 'Kebakaran Hutan dan Lahan',   'slug' => 'karhutla',                   'kategori' => 'alam'],
            ['id_jenis' => 7,  'nama_bencana' => 'Kekeringan',                  'slug' => 'kekeringan',                 'kategori' => 'alam'],
            ['id_jenis' => 8,  'nama_bencana' => 'Letusan Gunung Api',          'slug' => 'gunung-meletus',             'kategori' => 'alam'],
            ['id_jenis' => 9,  'nama_bencana' => 'Tanah Longsor',               'slug' => 'tanah-longsor',              'kategori' => 'alam'],
            ['id_jenis' => 10, 'nama_bencana' => 'Tsunami',                     'slug' => 'tsunami',                    'kategori' => 'alam'],
            ['id_jenis' => 11, 'nama_bencana' => 'Likuefaksi',                  'slug' => 'likuefaksi',                 'kategori' => 'alam'],
            ['id_jenis' => 12, 'nama_bencana' => 'Kebakaran Pemukiman',         'slug' => 'kebakaran-pemukiman',        'kategori' => 'non_alam'],
            ['id_jenis' => 13, 'nama_bencana' => 'Epidemi / Wabah Penyakit',    'slug' => 'epidemi-wabah',              'kategori' => 'non_alam'],
        ], uniqueBy: ['id_jenis'], update: ['nama_bencana', 'slug', 'kategori']);
    }
}
