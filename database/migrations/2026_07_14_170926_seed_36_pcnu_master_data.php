<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pcnuList = [
            ['nama' => 'PCNU Kabupaten Kudus', 'kode_sni' => 'KDS', 'id_wilayah' => '3319'],
            ['nama' => 'PCNU Kabupaten Pati', 'kode_sni' => 'PTI', 'id_wilayah' => '3318'],
            ['nama' => 'PCNU Kabupaten Jepara', 'kode_sni' => 'JPA', 'id_wilayah' => '3320'],
            ['nama' => 'PCNU Kabupaten Rembang', 'kode_sni' => 'RBG', 'id_wilayah' => '3317'],
            ['nama' => 'PCNU Kabupaten Blora', 'kode_sni' => 'BLA', 'id_wilayah' => '3316'],
            ['nama' => 'PCNU Kabupaten Grobogan', 'kode_sni' => 'PWD', 'id_wilayah' => '3315'],
            ['nama' => 'PCNU Kota Semarang', 'kode_sni' => 'SMG', 'id_wilayah' => '3374'],
            ['nama' => 'PCNU Kota Salatiga', 'kode_sni' => 'SLT', 'id_wilayah' => '3373'],
            ['nama' => 'PCNU Kabupaten Semarang', 'kode_sni' => 'UNR', 'id_wilayah' => '3322'],
            ['nama' => 'PCNU Kabupaten Demak', 'kode_sni' => 'DMK', 'id_wilayah' => '3321'],
            ['nama' => 'PCNU Kabupaten Kendal', 'kode_sni' => 'KDL', 'id_wilayah' => '3324'],
            ['nama' => 'PCNU Kota Surakarta', 'kode_sni' => 'SKT', 'id_wilayah' => '3372'],
            ['nama' => 'PCNU Kabupaten Sukoharjo', 'kode_sni' => 'SKH', 'id_wilayah' => '3311'],
            ['nama' => 'PCNU Kabupaten Karanganyar', 'kode_sni' => 'KRA', 'id_wilayah' => '3313'],
            ['nama' => 'PCNU Kabupaten Sragen', 'kode_sni' => 'SRG', 'id_wilayah' => '3314'],
            ['nama' => 'PCNU Kabupaten Boyolali', 'kode_sni' => 'BYL', 'id_wilayah' => '3309'],
            ['nama' => 'PCNU Kabupaten Klaten', 'kode_sni' => 'KLT', 'id_wilayah' => '3310'],
            ['nama' => 'PCNU Kabupaten Wonogiri', 'kode_sni' => 'WNG', 'id_wilayah' => '3312'],
            ['nama' => 'PCNU Kota Magelang', 'kode_sni' => 'MGG', 'id_wilayah' => '3371'],
            ['nama' => 'PCNU Kabupaten Magelang', 'kode_sni' => 'MKD', 'id_wilayah' => '3308'],
            ['nama' => 'PCNU Kabupaten Temanggung', 'kode_sni' => 'TMG', 'id_wilayah' => '3323'],
            ['nama' => 'PCNU Kabupaten Wonosobo', 'kode_sni' => 'WSB', 'id_wilayah' => '3307'],
            ['nama' => 'PCNU Kabupaten Purworejo', 'kode_sni' => 'PWR', 'id_wilayah' => '3306'],
            ['nama' => 'PCNU Kabupaten Kebumen', 'kode_sni' => 'KBM', 'id_wilayah' => '3305'],
            ['nama' => 'PCNU Kabupaten Banyumas', 'kode_sni' => 'PWT', 'id_wilayah' => '3302'],
            ['nama' => 'PCNU Kabupaten Cilacap', 'kode_sni' => 'CLP', 'id_wilayah' => '3301'],
            ['nama' => 'PCNU Kabupaten Purbalingga', 'kode_sni' => 'PBG', 'id_wilayah' => '3303'],
            ['nama' => 'PCNU Kabupaten Banjarnegara', 'kode_sni' => 'BJA', 'id_wilayah' => '3304'],
            ['nama' => 'PCNU Kota Pekalongan', 'kode_sni' => 'PKL', 'id_wilayah' => '3375'],
            ['nama' => 'PCNU Kota Tegal', 'kode_sni' => 'TGL', 'id_wilayah' => '3376'],
            ['nama' => 'PCNU Kabupaten Pekalongan', 'kode_sni' => 'KJN', 'id_wilayah' => '3326'],
            ['nama' => 'PCNU Kabupaten Tegal', 'kode_sni' => 'SLW', 'id_wilayah' => '3328'],
            ['nama' => 'PCNU Kabupaten Batang', 'kode_sni' => 'BTG', 'id_wilayah' => '3325'],
            ['nama' => 'PCNU Kabupaten Pemalang', 'kode_sni' => 'PML', 'id_wilayah' => '3327'],
            ['nama' => 'PCNU Kabupaten Brebes', 'kode_sni' => 'BBS', 'id_wilayah' => '3329'],
            ['nama' => 'PCNU Lasem', 'kode_sni' => 'LSM', 'id_wilayah' => '3317'], // Lasem secara administratif masuk Kabupaten Rembang
        ];

        foreach ($pcnuList as $pcnu) {
            $existingPcnu = DB::table('organisasi_pcnu')
                ->where('nama_pcnu', $pcnu['nama'])
                ->first();

            if ($existingPcnu) {
                DB::table('organisasi_pcnu')
                    ->where('id_pcnu', $existingPcnu->id_pcnu)
                    ->update(['kode_sni' => $pcnu['kode_sni']]);
            } else {
                $idUnit = DB::table('organisasi_unit')->insertGetId([
                    'nama_unit' => $pcnu['nama'],
                    'tipe_unit' => 'pcnu',
                    'id_wilayah' => $pcnu['id_wilayah'],
                    'parent_id' => null,
                ]);

                DB::table('organisasi_pcnu')->insert([
                    'id_unit' => $idUnit,
                    'nama_pcnu' => $pcnu['nama'],
                    'kode_sni' => $pcnu['kode_sni'],
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Secara umum kita tidak mendelete data master jika di-rollback
    }
};
