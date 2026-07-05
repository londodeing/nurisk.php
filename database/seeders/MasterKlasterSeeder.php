<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterKlasterSeeder extends Seeder
{
    public function run(): void
    {
        $klasters = [
            ['nama_klaster' => 'Kesehatan', 'deskripsi' => 'Penanganan medis, obat-obatan, dan trauma healing.'],
            ['nama_klaster' => 'Pencarian dan Penyelamatan (SAR)', 'deskripsi' => 'Evakuasi korban dan pencarian orang hilang.'],
            ['nama_klaster' => 'Logistik', 'deskripsi' => 'Pengelolaan distribusi bantuan, makanan, dan peralatan.'],
            ['nama_klaster' => 'Pengungsian dan Perlindungan', 'deskripsi' => 'Penyediaan tempat berlindung sementara dan perlindungan rentan.'],
            ['nama_klaster' => 'Pendidikan', 'deskripsi' => 'Fasilitas pendidikan darurat dan pemulihan sekolah.'],
            ['nama_klaster' => 'Sarana Prasarana', 'deskripsi' => 'Pemulihan infrastruktur dasar seperti jalan, listrik, air bersih.'],
            ['nama_klaster' => 'Ekonomi', 'deskripsi' => 'Pemulihan mata pencaharian warga terdampak.'],
            ['nama_klaster' => 'Pemulihan Dini', 'deskripsi' => 'Transisi dari masa tanggap darurat menuju rehabilitasi.'],
        ];

        foreach ($klasters as $klaster) {
            DB::table('master_klaster')->insertOrIgnore(array_merge($klaster, [
                'is_aktif' => true,
                'dibuat_pada' => now(),
                'diperbarui_pada' => now(),
            ]));
        }
    }
}
