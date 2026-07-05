<?php

namespace Database\Seeders;

use App\Models\MasterJabatanPenandatangan;
use App\Models\MasterSuratJenis;
use Illuminate\Database\Seeder;

class MasterSuratSeeder extends Seeder
{
    public function run(): void
    {
        $jenis = [
            ['kode_jenis' => 'SK', 'nama_jenis' => 'Surat Keputusan', 'kategori' => 'ORGANISASI', 'format_nomor' => '{SEQ}/PCNU/SK/{BULAN_ROMAWI}/{TAHUN}'],
            ['kode_jenis' => 'ST', 'nama_jenis' => 'Surat Tugas', 'kategori' => 'OPERASI', 'format_nomor' => '{SEQ}/PCNU/ST/{BULAN_ROMAWI}/{TAHUN}'],
            ['kode_jenis' => 'SE', 'nama_jenis' => 'Surat Edaran', 'kategori' => 'UMUM', 'format_nomor' => '{SEQ}/PCNU/SE/{BULAN_ROMAWI}/{TAHUN}'],
            ['kode_jenis' => 'SU', 'nama_jenis' => 'Surat Umum', 'kategori' => 'UMUM', 'format_nomor' => '{SEQ}/PCNU/SU/{BULAN_ROMAWI}/{TAHUN}'],
        ];

        foreach ($jenis as $data) {
            MasterSuratJenis::updateOrCreate(['kode_jenis' => $data['kode_jenis']], [
                'nama_jenis' => $data['nama_jenis'],
                'kategori' => $data['kategori'],
                'format_nomor' => $data['format_nomor'],
            ]);
        }

        $jabatan = [
            ['nama_jabatan' => 'Ketua PCNU', 'urutan_hierarki' => 1],
            ['nama_jabatan' => 'Wakil Ketua PCNU', 'urutan_hierarki' => 2],
            ['nama_jabatan' => 'Sekretaris PCNU', 'urutan_hierarki' => 3],
            ['nama_jabatan' => 'Wakil Sekretaris PCNU', 'urutan_hierarki' => 4],
            ['nama_jabatan' => 'Bendahara PCNU', 'urutan_hierarki' => 5],
        ];

        foreach ($jabatan as $data) {
            MasterJabatanPenandatangan::updateOrCreate(
                ['nama_jabatan' => $data['nama_jabatan']],
                ['urutan_hierarki' => $data['urutan_hierarki']]
            );
        }
    }
}
