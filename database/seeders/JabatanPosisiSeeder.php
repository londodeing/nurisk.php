<?php

namespace Database\Seeders;

use App\Models\JabatanPosisi;
use Illuminate\Database\Seeder;

class JabatanPosisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JabatanPosisi::upsert(
            [
                ['id_jabatan_posisi' => 1,  'nama_jabatan' => 'Ketua PWNU',           'slug' => 'ketua-pwnu',           'deskripsi' => 'Ketua Pengurus Wilayah NU Jawa Tengah'],
                ['id_jabatan_posisi' => 2,  'nama_jabatan' => 'Sekretaris PWNU',      'slug' => 'sekretaris-pwnu',      'deskripsi' => 'Sekretaris Pengurus Wilayah NU Jawa Tengah'],
                ['id_jabatan_posisi' => 3,  'nama_jabatan' => 'Admin PWNU',           'slug' => 'admin-pwnu',           'deskripsi' => 'Administrator sistem level PWNU'],
                ['id_jabatan_posisi' => 4,  'nama_jabatan' => 'Koordinator TRC PWNU', 'slug' => 'koordinator-trc-pwnu', 'deskripsi' => 'Koordinator Tim Reaksi Cepat PWNU'],
                ['id_jabatan_posisi' => 5,  'nama_jabatan' => 'Anggota TRC PWNU',     'slug' => 'anggota-trc-pwnu',     'deskripsi' => 'Anggota Tim Reaksi Cepat PWNU'],
                ['id_jabatan_posisi' => 6,  'nama_jabatan' => 'Ketua PCNU',           'slug' => 'ketua-pcnu',           'deskripsi' => 'Ketua Pengurus Cabang NU'],
                ['id_jabatan_posisi' => 7,  'nama_jabatan' => 'Sekretaris PCNU',      'slug' => 'sekretaris-pcnu',      'deskripsi' => 'Sekretaris Pengurus Cabang NU'],
                ['id_jabatan_posisi' => 8,  'nama_jabatan' => 'Admin PCNU',           'slug' => 'admin-pcnu',           'deskripsi' => 'Administrator sistem level PCNU'],
                ['id_jabatan_posisi' => 9,  'nama_jabatan' => 'Koordinator TRC PCNU', 'slug' => 'koordinator-trc-pcnu', 'deskripsi' => 'Koordinator Tim Reaksi Cepat PCNU'],
                ['id_jabatan_posisi' => 10, 'nama_jabatan' => 'Anggota TRC PCNU',     'slug' => 'anggota-trc-pcnu',     'deskripsi' => 'Anggota Tim Reaksi Cepat PCNU'],
                ['id_jabatan_posisi' => 11, 'nama_jabatan' => 'Komandan Pos Aju',     'slug' => 'komandan-pos-aju',     'deskripsi' => 'Komandan lapangan di Pos Aju'],
                ['id_jabatan_posisi' => 12, 'nama_jabatan' => 'Koordinator Logistik', 'slug' => 'koordinator-logistik', 'deskripsi' => 'Koordinator klaster logistik operasi'],
                ['id_jabatan_posisi' => 13, 'nama_jabatan' => 'Koordinator Medis',    'slug' => 'koordinator-medis',    'deskripsi' => 'Koordinator klaster kesehatan operasi'],
                ['id_jabatan_posisi' => 14, 'nama_jabatan' => 'Operator Sistem',      'slug' => 'operator-sistem',      'deskripsi' => 'Operator entry data dan monitoring sistem'],
                ['id_jabatan_posisi' => 15, 'nama_jabatan' => 'Relawan Umum',         'slug' => 'relawan-umum',         'deskripsi' => 'Relawan tanpa jabatan struktural khusus'],
            ],
            uniqueBy: ['id_jabatan_posisi'],
            update:   ['nama_jabatan', 'slug', 'deskripsi']
        );
    }
}
