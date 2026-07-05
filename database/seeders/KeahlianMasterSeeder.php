<?php

namespace Database\Seeders;

use App\Models\AuthKeahlianMaster;
use Illuminate\Database\Seeder;

class KeahlianMasterSeeder extends Seeder
{
    public function run(): void
    {
        $keahlian = [
            ['nama_keahlian' => 'SAR Darat',       'deskripsi' => 'Pencarian dan pertolongan di darat'],
            ['nama_keahlian' => 'SAR Air',         'deskripsi' => 'Pencarian dan pertolongan di air'],
            ['nama_keahlian' => 'Kedaruratan Medis', 'deskripsi' => 'Pertolongan pertama dan triase medis'],
            ['nama_keahlian' => 'Logistik',        'deskripsi' => 'Manajemen rantai pasok dan distribusi bantuan'],
            ['nama_keahlian' => 'Dapur Umum',      'deskripsi' => 'Penyediaan makanan darurat'],
            ['nama_keahlian' => 'Evakuasi',        'deskripsi' => 'Evakuasi korban dan penduduk terdampak'],
            ['nama_keahlian' => 'Komunikasi Darurat', 'deskripsi' => 'Radio komunikasi dan telekomunikasi darurat'],
            ['nama_keahlian' => 'Manajemen Pengungsian', 'deskripsi' => 'Pengelolaan lokasi pengungsian'],
            ['nama_keahlian' => 'Pemetaan & GIS',  'deskripsi' => 'Pemetaan wilayah bencana dan SIG'],
            ['nama_keahlian' => 'Psikososial',     'deskripsi' => 'Dukungan psikososial bagi korban'],
            ['nama_keahlian' => 'Operasional',     'deskripsi' => 'Dukungan operasional dan administrasi'],
            ['nama_keahlian' => 'Transportasi',    'deskripsi' => 'Kendaraan darurat dan transportasi logistik'],
            ['nama_keahlian' => 'IT & Data',       'deskripsi' => 'Teknologi informasi dan pengelolaan data'],
            ['nama_keahlian' => 'Keamanan',        'deskripsi' => 'Pengamanan lokasi dan pengaturan lalu lintas'],
            ['nama_keahlian' => 'Pemadam Kebakaran', 'deskripsi' => 'Pemadaman kebakaran hutan dan pemukiman'],
        ];

        foreach ($keahlian as $item) {
            AuthKeahlianMaster::firstOrCreate(
                ['nama_keahlian' => $item['nama_keahlian']],
                ['deskripsi' => $item['deskripsi']]
            );
        }
    }
}
