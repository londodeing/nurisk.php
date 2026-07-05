<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Urutan CREATE penting — parent table (master) sebelum child

        if (!Schema::hasTable('assessment_kebutuhan_numerik_master')) {
            Schema::create('assessment_kebutuhan_numerik_master', function (Blueprint $t) {
                $t->integer('id_item')->autoIncrement();
                $t->string('kode_item', 50)->unique();
                $t->string('nama_item', 150);
                $t->string('satuan_default', 30)->default('unit');
                $t->enum('kategori', ['pangan','sandang','papan','kesehatan','peralatan','lainnya'])
                  ->default('lainnya');
                $t->boolean('aktif')->default(true);
                $t->integer('urutan')->default(0);
            });
            // Seed langsung di migration agar selalu ada
            DB::table('assessment_kebutuhan_numerik_master')->insertOrIgnore([
                ['kode_item'=>'sembako',    'nama_item'=>'Paket Sembako',       'satuan_default'=>'paket',  'kategori'=>'pangan',    'urutan'=>1],
                ['kode_item'=>'beras',      'nama_item'=>'Beras',               'satuan_default'=>'kg',     'kategori'=>'pangan',    'urutan'=>2],
                ['kode_item'=>'mie_instan', 'nama_item'=>'Mie Instan',          'satuan_default'=>'dus',    'kategori'=>'pangan',    'urutan'=>3],
                ['kode_item'=>'air_bersih', 'nama_item'=>'Air Bersih',          'satuan_default'=>'liter',  'kategori'=>'pangan',    'urutan'=>4],
                ['kode_item'=>'selimut',    'nama_item'=>'Selimut',             'satuan_default'=>'lembar', 'kategori'=>'sandang',   'urutan'=>5],
                ['kode_item'=>'pakaian',    'nama_item'=>'Pakaian Layak Pakai', 'satuan_default'=>'set',    'kategori'=>'sandang',   'urutan'=>6],
                ['kode_item'=>'matras',     'nama_item'=>'Matras / Tikar',      'satuan_default'=>'lembar', 'kategori'=>'papan',     'urutan'=>7],
                ['kode_item'=>'tenda',      'nama_item'=>'Tenda Pengungsian',   'satuan_default'=>'unit',   'kategori'=>'papan',     'urutan'=>8],
                ['kode_item'=>'terpal',     'nama_item'=>'Terpal',              'satuan_default'=>'lembar', 'kategori'=>'papan',     'urutan'=>9],
                ['kode_item'=>'obat_obatan','nama_item'=>'Paket Obat-obatan',   'satuan_default'=>'paket',  'kategori'=>'kesehatan', 'urutan'=>10],
                ['kode_item'=>'masker',     'nama_item'=>'Masker',              'satuan_default'=>'lusin',  'kategori'=>'kesehatan', 'urutan'=>11],
                ['kode_item'=>'pampers',    'nama_item'=>'Popok / Pampers',     'satuan_default'=>'dus',    'kategori'=>'kesehatan', 'urutan'=>12],
                ['kode_item'=>'susu_bayi',  'nama_item'=>'Susu Bayi / Formula', 'satuan_default'=>'kaleng', 'kategori'=>'kesehatan', 'urutan'=>13],
                ['kode_item'=>'perahu',     'nama_item'=>'Perahu Karet',        'satuan_default'=>'unit',   'kategori'=>'peralatan', 'urutan'=>14],
                ['kode_item'=>'pompa_air',  'nama_item'=>'Pompa Air',           'satuan_default'=>'unit',   'kategori'=>'peralatan', 'urutan'=>15],
                ['kode_item'=>'genset',     'nama_item'=>'Genset Portabel',     'satuan_default'=>'unit',   'kategori'=>'peralatan', 'urutan'=>16],
            ]);
        }

        $tables = [
            'assessment_lokasi_detail'       => function (Blueprint $t) {
                $t->bigIncrements('id_lokasi_detail');
                $t->unsignedBigInteger('id_assessment')->unique();
                $t->char('id_kec', 6)->nullable();
                $t->char('id_desa', 10)->nullable();
                $t->text('alamat_spesifik')->nullable();
                $t->string('region_terdampak', 255)->nullable();
                $t->timestamp('dibuat_pada')->useCurrent();
                $t->foreign('id_assessment','fk_alokdet_assessment')
                  ->references('id_assessment_utama')->on('assessment_utama')->onDelete('cascade');
                $t->foreign('id_kec','fk_alokdet_kec')
                  ->references('id_kec')->on('wilayah_kecamatan')->onDelete('set null');
                $t->foreign('id_desa','fk_alokdet_desa')
                  ->references('id_desa')->on('wilayah_desa')->onDelete('set null');
            },
            'assessment_narasi_detail'       => function (Blueprint $t) {
                $t->bigIncrements('id_narasi_detail');
                $t->unsignedBigInteger('id_assessment')->unique();
                $t->text('sebaran_dampak')->nullable();
                $t->text('kondisi_umum')->nullable();
                $t->text('upaya_penanganan')->nullable();
                $t->text('kendala_lapangan')->nullable();
                $t->text('kendala_tambahan')->nullable();
                $t->text('rekomendasi_aksi')->nullable();
                $t->timestamp('dibuat_pada')->useCurrent();
                $t->foreign('id_assessment','fk_anadet_assessment')
                  ->references('id_assessment_utama')->on('assessment_utama')->onDelete('cascade');
            },
            'assessment_kebutuhan_lanjutan'  => function (Blueprint $t) {
                $t->bigIncrements('id_kebutuhan_lanjutan');
                $t->unsignedBigInteger('id_assessment')->unique();
                $t->text('kebutuhan_dana')->nullable();
                $t->text('kebutuhan_relawan')->nullable();
                $t->text('kebutuhan_logistik')->nullable();
                $t->text('kebutuhan_peralatan')->nullable();
                $t->text('kebutuhan_medis')->nullable();
                $t->text('kebutuhan_pangan')->nullable();
                $t->text('kebutuhan_lainnya')->nullable();
                $t->timestamp('dibuat_pada')->useCurrent();
                $t->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
                $t->foreign('id_assessment','fk_akebutlanjut_assessment')
                  ->references('id_assessment_utama')->on('assessment_utama')->onDelete('cascade');
            },
            'assessment_kebutuhan_numerik'   => function (Blueprint $t) {
                $t->bigIncrements('id_kebutuhan_num');
                $t->unsignedBigInteger('id_assessment');
                $t->integer('id_item');
                $t->decimal('jumlah_dibutuhkan', 12, 2)->default(0);
                $t->decimal('jumlah_tersedia', 12, 2)->default(0);
                $t->string('satuan', 30)->default('unit');
                $t->enum('prioritas', ['darurat','penting','normal'])->default('normal');
                $t->string('keterangan', 255)->nullable();
                $t->timestamp('dibuat_pada')->useCurrent();
                $t->unique(['id_assessment','id_item'], 'uk_assessment_item');
                $t->foreign('id_assessment','fk_akebutnum_assessment')
                  ->references('id_assessment_utama')->on('assessment_utama')->onDelete('cascade');
                $t->foreign('id_item','fk_akebutnum_item')
                  ->references('id_item')->on('assessment_kebutuhan_numerik_master');
            },
            'assessment_dampak_manusia_v2'   => function (Blueprint $t) {
                $t->bigIncrements('id_dampak_v2');
                $t->unsignedBigInteger('id_assessment')->unique();
                $t->integer('meninggal')->default(0);
                $t->integer('hilang')->default(0);
                $t->integer('luka_berat')->default(0);
                $t->integer('luka_ringan')->default(0);
                $t->integer('terdampak_jiwa')->default(0);
                $t->integer('terdampak_kk')->default(0);
                $t->integer('pengungsi_jiwa')->default(0);
                $t->integer('pengungsi_kk')->default(0);
                $t->integer('pengungsi_balita')->default(0);
                $t->integer('pengungsi_lansia')->default(0);
                $t->integer('pengungsi_disabilitas')->default(0);
                $t->integer('pengungsi_ibu_hamil')->default(0);
                $t->timestamp('dibuat_pada')->useCurrent();
                $t->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
                $t->foreign('id_assessment','fk_admanusiav2_assessment')
                  ->references('id_assessment_utama')->on('assessment_utama')->onDelete('cascade');
            },
            'assessment_dampak_rumah'        => function (Blueprint $t) {
                $t->bigIncrements('id_dampak_rumah');
                $t->unsignedBigInteger('id_assessment')->unique();
                $t->integer('rusak_berat')->default(0);
                $t->integer('rusak_sedang')->default(0);
                $t->integer('rusak_ringan')->default(0);
                $t->integer('terendam')->default(0);
                $t->integer('terancam')->default(0);
                $t->decimal('estimasi_kerugian_juta', 12, 2)->default(0);
                $t->timestamp('dibuat_pada')->useCurrent();
                $t->foreign('id_assessment','fk_adprumah_assessment')
                  ->references('id_assessment_utama')->on('assessment_utama')->onDelete('cascade');
            },
            'assessment_dampak_fasum'        => function (Blueprint $t) {
                $t->bigIncrements('id_dampak_fasum');
                $t->unsignedBigInteger('id_assessment')->unique();
                $t->integer('sanitasi')->default(0);
                $t->integer('pendidikan')->default(0);
                $t->integer('kesehatan')->default(0);
                $t->integer('ibadah')->default(0);
                $t->integer('komunikasi')->default(0);
                $t->integer('listrik')->default(0);
                $t->integer('kantor')->default(0);
                $t->integer('jembatan')->default(0);
                $t->integer('pasar')->default(0);
                $t->integer('spbu')->default(0);
                $t->text('catatan_fasum')->nullable();
                $t->timestamp('dibuat_pada')->useCurrent();
                $t->foreign('id_assessment','fk_adpfasum_assessment')
                  ->references('id_assessment_utama')->on('assessment_utama')->onDelete('cascade');
            },
            'assessment_dampak_vital'        => function (Blueprint $t) {
                $t->bigIncrements('id_dampak_vital');
                $t->unsignedBigInteger('id_assessment')->unique();
                $t->integer('air_bersih')->default(0);
                $t->integer('listrik')->default(0);
                $t->integer('telekomunikasi')->default(0);
                $t->decimal('irigasi', 8, 2)->default(0);
                $t->decimal('jalan', 8, 2)->default(0);
                $t->integer('spbu')->default(0);
                $t->decimal('sawah_ha', 10, 2)->default(0);
                $t->integer('ternak_ekor')->default(0);
                $t->decimal('hutan_ha', 10, 2)->default(0);
                $t->boolean('sumber_air_tercemar')->default(false);
                $t->text('catatan_vital')->nullable();
                $t->timestamp('dibuat_pada')->useCurrent();
                $t->foreign('id_assessment','fk_adpvital_assessment')
                  ->references('id_assessment_utama')->on('assessment_utama')->onDelete('cascade');
            },
        ];

        foreach ($tables as $tableName => $callback) {
            if (!Schema::hasTable($tableName)) {
                Schema::create($tableName, $callback);
            }
        }
    }

    public function down(): void
    {
        // Hapus child sebelum parent
        $tables = [
            'assessment_kebutuhan_numerik',
            'assessment_dampak_vital',
            'assessment_dampak_fasum',
            'assessment_dampak_rumah',
            'assessment_dampak_manusia_v2',
            'assessment_kebutuhan_lanjutan',
            'assessment_narasi_detail',
            'assessment_lokasi_detail',
            'assessment_kebutuhan_numerik_master',
        ];
        foreach ($tables as $t) {
            Schema::dropIfExists($t);
        }
    }
};
