<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisasi_pcnu', function (Blueprint $table) {
            $table->string('kode_sni', 3)->nullable()->after('nama_pcnu');
        });

        // Peta Nama PCNU ke Kode SNI BSN
        $mappings = [
            'Kudus' => 'KDS',
            'Pati' => 'PTI',
            'Jepara' => 'JPA',
            'Rembang' => 'RBG',
            'Blora' => 'BLA',
            'Grobogan' => 'PWD',
            'Purwodadi' => 'PWD',
            'Kota Semarang' => 'SMG',
            'Salatiga' => 'SLT',
            'Kabupaten Semarang' => 'UNR',
            'Ungaran' => 'UNR',
            'Demak' => 'DMK',
            'Kendal' => 'KDL',
            'Kota Surakarta' => 'SKT',
            'Solo' => 'SKT',
            'Sukoharjo' => 'SKH',
            'Karanganyar' => 'KRA',
            'Sragen' => 'SRG',
            'Boyolali' => 'BYL',
            'Klaten' => 'KLT',
            'Wonogiri' => 'WNG',
            'Kota Magelang' => 'MGG',
            'Kabupaten Magelang' => 'MKD',
            'Mungkid' => 'MKD',
            'Temanggung' => 'TMG',
            'Wonosobo' => 'WSB',
            'Purworejo' => 'PWR',
            'Kebumen' => 'KBM',
            'Banyumas' => 'PWT',
            'Purwokerto' => 'PWT',
            'Cilacap' => 'CLP',
            'Purbalingga' => 'PBG',
            'Banjarnegara' => 'BJA',
            'Kota Pekalongan' => 'PKL',
            'Kota Tegal' => 'TGL',
            'Kabupaten Pekalongan' => 'KJN',
            'Kajen' => 'KJN',
            'Kabupaten Tegal' => 'SLW',
            'Slawi' => 'SLW',
            'Batang' => 'BTG',
            'Pemalang' => 'PML',
            'Brebes' => 'BBS',
            'Lasem' => 'LSM'
        ];

        foreach ($mappings as $search => $kode) {
            DB::table('organisasi_pcnu')
                ->where('nama_pcnu', 'LIKE', '%' . $search . '%')
                // Menghindari update ulang jika ada pattern yang mirip, misal 'Kabupaten Semarang' vs 'Kota Semarang'
                // Namun Laravel update query akan jalan aman jika kita urutkan dari yang spesifik atau biarkan default.
                // Disini Kota dan Kabupaten sudah dipisah jadi insya Allah aman.
                ->update(['kode_sni' => $kode]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisasi_pcnu', function (Blueprint $table) {
            $table->dropColumn('kode_sni');
        });
    }
};
