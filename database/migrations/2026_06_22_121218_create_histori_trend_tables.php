<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('histori_bencana_wilayah')) {
            Schema::create('histori_bencana_wilayah', function (Blueprint $table) {
                $table->id('id_histori');
                $table->char('id_kab', 4)->nullable();
                $table->integer('id_jenis_bencana');
                $table->string('nama_kejadian', 255);
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai')->nullable();
                $table->year('tahun');
                $table->tinyInteger('bulan')->comment('1-12');
                $table->integer('korban_meninggal')->default(0);
                $table->integer('korban_hilang')->default(0);
                $table->integer('korban_luka_berat')->default(0);
                $table->integer('korban_terdampak')->default(0);
                $table->integer('pengungsi')->default(0);
                $table->integer('rumah_rusak_total')->default(0);
                $table->decimal('kerugian_estimasi_juta', 15, 2)->default(0);
                $table->enum('sumber_data', ['bnpb', 'bmkg', 'bpbd_jateng', 'bpbd_kab', 'nurisk_ops', 'media', 'manual'])->default('manual');
                $table->string('link_sumber', 500)->nullable();
                $table->text('deskripsi')->nullable();
                $table->unsignedBigInteger('id_insiden_nurisk')->nullable();
                $table->boolean('is_terverifikasi')->default(false);
                $table->unsignedBigInteger('id_verifikator')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

                $table->index(['id_kab', 'id_jenis_bencana'], 'idx_histori_kab_jenis');
                $table->index(['tahun', 'bulan'], 'idx_histori_tahun_bulan');

                $table->foreign('id_kab', 'fk_hist_kab')
                      ->references('id_kab')->on('wilayah_kabupaten')->onDelete('set null');
                $table->foreign('id_jenis_bencana', 'fk_hist_jenis')
                      ->references('id_jenis')->on('bencana_master_jenis')->onDelete('cascade');
                $table->foreign('id_insiden_nurisk', 'fk_hist_insiden')
                      ->references('id_insiden')->on('operasi_insiden')->onDelete('set null');
                $table->foreign('id_verifikator', 'fk_hist_verifikator')
                      ->references('id_pengguna')->on('auth_users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('histori_analisis_musiman')) {
            Schema::create('histori_analisis_musiman', function (Blueprint $table) {
                $table->increments('id_analisis');
                $table->char('id_kab', 4);
                $table->integer('id_jenis_bencana');
                $table->tinyInteger('bulan')->comment('1-12');
                $table->integer('frekuensi_total')->default(0);
                $table->year('tahun_data_mulai');
                $table->year('tahun_data_akhir');
                $table->integer('jumlah_tahun_data');
                $table->decimal('rata_rata_per_tahun', 6, 3)->default(0);
                $table->decimal('indeks_musiman', 6, 3)->default(1.000);
                $table->tinyInteger('peak_bulan')->nullable();
                $table->timestamp('dihitung_pada')->useCurrent();

                $table->unique(['id_kab', 'id_jenis_bencana', 'bulan'], 'uk_analisis_musiman');

                $table->foreign('id_kab', 'fk_amus_kab')
                      ->references('id_kab')->on('wilayah_kabupaten')->onDelete('cascade');
                $table->foreign('id_jenis_bencana', 'fk_amus_jenis')
                      ->references('id_jenis')->on('bencana_master_jenis')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('histori_probabilitas_wilayah')) {
            Schema::create('histori_probabilitas_wilayah', function (Blueprint $table) {
                $table->id('id_prob');
                $table->char('id_kab', 4);
                $table->integer('id_jenis_bencana');
                $table->enum('periode_prediksi', ['30_hari', '90_hari', '6_bulan', '1_tahun']);
                $table->decimal('probabilitas', 5, 4);
                $table->decimal('confidence_level', 5, 2)->nullable();
                $table->string('metode_kalkulasi', 50)->default('frekuensi_historis');
                $table->integer('data_points_digunakan')->default(0);
                $table->json('faktor_risiko_utama')->nullable();
                $table->enum('level_risiko', ['sangat_rendah', 'rendah', 'sedang', 'tinggi', 'sangat_tinggi']);
                $table->text('rekomendasi_siaga')->nullable();
                $table->timestamp('dihitung_pada')->useCurrent();
                $table->timestamp('berlaku_hingga')->nullable();

                $table->index(['id_kab', 'id_jenis_bencana'], 'idx_prob_kab_jenis');

                $table->foreign('id_kab', 'fk_prob_kab')
                      ->references('id_kab')->on('wilayah_kabupaten')->onDelete('cascade');
                $table->foreign('id_jenis_bencana', 'fk_prob_jenis')
                      ->references('id_jenis')->on('bencana_master_jenis')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('histori_indikator_risiko_wilayah')) {
            Schema::create('histori_indikator_risiko_wilayah', function (Blueprint $table) {
                $table->id('id_indikator');
                $table->char('id_kab', 4);
                $table->string('nama_indikator', 150);
                $table->decimal('nilai', 15, 4);
                $table->string('satuan', 50);
                $table->string('sumber', 255)->nullable();
                $table->string('periode_data', 50)->nullable();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['id_kab', 'nama_indikator'], 'uk_indikator_wilayah');

                $table->foreign('id_kab', 'fk_irisiko_kab')
                      ->references('id_kab')->on('wilayah_kabupaten')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('histori_peta_risiko_bencana')) {
            Schema::create('histori_peta_risiko_bencana', function (Blueprint $table) {
                $table->id('id_peta');
                $table->char('id_kab', 4);
                $table->integer('id_jenis_bencana');
                $table->year('tahun');
                $table->enum('level_risiko', ['sangat_rendah', 'rendah', 'sedang', 'tinggi', 'sangat_tinggi']);
                $table->decimal('skor_risiko', 5, 2);
                $table->decimal('skor_bahaya', 5, 2)->default(0);
                $table->decimal('skor_kerentanan', 5, 2)->default(0);
                $table->decimal('skor_kapasitas', 5, 2)->default(0);
                $table->decimal('indeks_risiko_akhir', 5, 2)->nullable();
                $table->enum('sumber_penilaian', ['bnpb_inarisk', 'bpbd', 'nurisk_kalkulasi', 'manual'])->default('nurisk_kalkulasi');
                $table->text('catatan')->nullable();
                $table->timestamp('dihitung_pada')->useCurrent();

                $table->unique(['id_kab', 'id_jenis_bencana', 'tahun'], 'uk_peta_risiko');

                $table->foreign('id_kab', 'fk_peta_kab')
                      ->references('id_kab')->on('wilayah_kabupaten')->onDelete('cascade');
                $table->foreign('id_jenis_bencana', 'fk_peta_jenis')
                      ->references('id_jenis')->on('bencana_master_jenis')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histori_peta_risiko_bencana');
        Schema::dropIfExists('histori_indikator_risiko_wilayah');
        Schema::dropIfExists('histori_probabilitas_wilayah');
        Schema::dropIfExists('histori_analisis_musiman');
        Schema::dropIfExists('histori_bencana_wilayah');
    }
};
