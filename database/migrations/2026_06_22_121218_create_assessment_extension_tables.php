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
        if (!Schema::hasTable('assessment_biodata_kejadian')) {
            Schema::create('assessment_biodata_kejadian', function (Blueprint $table) {
                $table->id('id_biodata');
                $table->unsignedBigInteger('id_assessment');
                $table->date('tanggal_mulai_kejadian');
                $table->time('jam_mulai_kejadian')->nullable();
                $table->text('kronologi_singkat');
                $table->string('penyebab_utama', 255)->nullable();
                $table->string('sumber_informasi_awal', 255)->nullable();
                $table->enum('skala_kejadian', ['lokal', 'kecamatan', 'kabupaten', 'provinsi', 'nasional'])->default('lokal');
                $table->decimal('luas_terdampak_ha', 12, 2)->default(0);
                $table->integer('jumlah_desa_terdampak')->default(0);
                $table->integer('jumlah_kecamatan_terdampak')->default(0);
                $table->boolean('status_masih_berlangsung')->default(true);
                $table->timestamp('dibuat_pada')->useCurrent();

                $table->foreign('id_assessment', 'fk_biodata_assessment')
                      ->references('id_assessment_utama')->on('assessment_utama')
                      ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('assessment_narasi_kejadian')) {
            Schema::create('assessment_narasi_kejadian', function (Blueprint $table) {
                $table->id('id_narasi');
                $table->unsignedBigInteger('id_assessment');
                $table->enum('fase', ['pra_bencana', 'saat_bencana', 'pasca_bencana']);
                $table->dateTime('waktu_fase')->nullable();
                $table->string('judul_narasi', 255);
                $table->longText('isi_narasi');
                $table->string('sumber_data', 255)->nullable();
                $table->unsignedBigInteger('id_penulis')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();

                $table->foreign('id_assessment', 'fk_narasi_assessment')
                      ->references('id_assessment_utama')->on('assessment_utama')
                      ->onDelete('cascade');
                $table->foreign('id_penulis', 'fk_narasi_penulis')
                      ->references('id_pengguna')->on('auth_users')
                      ->onDelete('set null');
            });
        }

        if (!Schema::hasTable('assessment_dampak_manusia_lanjutan')) {
            Schema::create('assessment_dampak_manusia_lanjutan', function (Blueprint $table) {
                $table->id('id_dampak_lanjutan');
                $table->unsignedBigInteger('id_assessment')->unique();
                $table->integer('luka_berat')->default(0);
                $table->integer('luka_ringan')->default(0);
                $table->integer('pengungsi_dalam_shelter')->default(0);
                $table->integer('pengungsi_mandiri')->default(0);
                $table->integer('balita_terdampak')->default(0);
                $table->integer('anak_terdampak')->default(0);
                $table->integer('lansia_terdampak')->default(0);
                $table->integer('ibu_hamil_terdampak')->default(0);
                $table->integer('disabilitas_terdampak')->default(0);
                $table->integer('jumlah_kk_terdampak')->default(0);
                $table->integer('jumlah_kk_mengungsi')->default(0);
                $table->text('catatan_dampak_manusia')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();

                $table->foreign('id_assessment', 'fk_dml_assessment')
                      ->references('id_assessment_utama')->on('assessment_utama')
                      ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('assessment_dampak_infrastruktur')) {
            Schema::create('assessment_dampak_infrastruktur', function (Blueprint $table) {
                $table->id('id_dampak_infra');
                $table->unsignedBigInteger('id_assessment')->unique();
                $table->integer('rumah_rusak_berat')->default(0);
                $table->integer('rumah_rusak_sedang')->default(0);
                $table->integer('rumah_rusak_ringan')->default(0);
                $table->integer('rumah_terendam')->default(0);
                $table->decimal('jalan_rusak_km', 8, 2)->default(0);
                $table->integer('jembatan_putus')->default(0);
                $table->integer('jembatan_rusak')->default(0);
                $table->integer('fasilitas_kesehatan_rusak')->default(0)->comment('RS, Puskesmas, Klinik');
                $table->integer('fasilitas_pendidikan_rusak')->default(0)->comment('SD, SMP, SMA, Pesantren');
                $table->integer('tempat_ibadah_rusak')->default(0)->comment('Masjid, Musala, dll');
                $table->integer('kantor_pemerintah_rusak')->default(0);
                $table->boolean('sarana_air_bersih_rusak')->default(false);
                $table->integer('jaringan_listrik_padam_kk')->default(0);
                $table->boolean('jaringan_komunikasi_putus')->default(false);
                $table->text('catatan_infrastruktur')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();

                $table->foreign('id_assessment', 'fk_dinfra_assessment')
                      ->references('id_assessment_utama')->on('assessment_utama')
                      ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('assessment_dampak_lingkungan')) {
            Schema::create('assessment_dampak_lingkungan', function (Blueprint $table) {
                $table->id('id_dampak_ling');
                $table->unsignedBigInteger('id_assessment')->unique();
                $table->decimal('lahan_pertanian_rusak_ha', 10, 2)->default(0);
                $table->decimal('hutan_terdampak_ha', 10, 2)->default(0);
                $table->decimal('lahan_tercemar_ha', 10, 2)->default(0);
                $table->boolean('sumber_air_tercemar')->default(false);
                $table->boolean('pencemaran_tanah')->default(false);
                $table->boolean('erosi_sedimentasi')->default(false);
                $table->boolean('kerusakan_ekosistem_pesisir')->default(false);
                $table->boolean('kerusakan_daerah_aliran_sungai')->default(false);
                $table->enum('tingkat_kerusakan_lingkungan', ['tidak_ada', 'ringan', 'sedang', 'berat', 'sangat_berat'])->default('tidak_ada');
                $table->boolean('butuh_rehabilitasi_lahan')->default(false);
                $table->text('catatan_lingkungan')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();

                $table->foreign('id_assessment', 'fk_dling_assessment')
                      ->references('id_assessment_utama')->on('assessment_utama')
                      ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('assessment_dampak_ekonomi')) {
            Schema::create('assessment_dampak_ekonomi', function (Blueprint $table) {
                $table->id('id_dampak_eko');
                $table->unsignedBigInteger('id_assessment')->unique();
                $table->decimal('kerugian_perumahan', 18, 2)->default(0);
                $table->decimal('kerugian_pertanian', 18, 2)->default(0);
                $table->decimal('kerugian_peternakan', 18, 2)->default(0);
                $table->decimal('kerugian_perikanan', 18, 2)->default(0);
                $table->decimal('kerugian_umkm', 18, 2)->default(0);
                $table->decimal('kerugian_infrastruktur', 18, 2)->default(0);
                $table->decimal('kerugian_lainnya', 18, 2)->default(0);
                $table->decimal('estimasi_kerugian_total', 18, 2)->default(0)->comment('Sum semua kerugian');
                $table->integer('mata_pencaharian_hilang')->default(0);
                $table->integer('usaha_terdampak')->default(0);
                $table->string('metodologi_estimasi', 255)->default('estimasi_lapangan');
                $table->text('catatan_ekonomi')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();

                $table->foreign('id_assessment', 'fk_deko_assessment')
                      ->references('id_assessment_utama')->on('assessment_utama')
                      ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('assessment_master_indikator_skor')) {
            Schema::create('assessment_master_indikator_skor', function (Blueprint $table) {
                $table->increments('id_indikator');
                $table->enum('domain', ['manusia', 'infrastruktur', 'lingkungan', 'ekonomi', 'sosial', 'kapasitas']);
                $table->string('kode_indikator', 50)->unique();
                $table->string('nama_indikator', 200);
                $table->text('deskripsi')->nullable();
                $table->tinyInteger('bobot')->default(1)->comment('Bobot relatif 1-10');
                $table->string('satuan', 50)->nullable();
                $table->decimal('skala_min', 10, 2)->default(0);
                $table->decimal('skala_max', 10, 2)->default(100);
                $table->text('panduan_skor_1')->nullable()->comment('Kondisi skor 1 (sangat rendah/baik)');
                $table->text('panduan_skor_3')->nullable()->comment('Kondisi skor 3 (sedang)');
                $table->text('panduan_skor_5')->nullable()->comment('Kondisi skor 5 (sangat tinggi/buruk)');
                $table->boolean('aktif')->default(true);
                $table->integer('urutan')->default(0);
            });

            // Insert Seeder Data
            DB::table('assessment_master_indikator_skor')->insert([
                ['domain'=>'manusia','kode_indikator'=>'M01','nama_indikator'=>'Korban Meninggal','bobot'=>10,'satuan'=>'orang','panduan_skor_1'=>'<5 orang','panduan_skor_3'=>'5-20 orang','panduan_skor_5'=>'>100 orang'],
                ['domain'=>'manusia','kode_indikator'=>'M02','nama_indikator'=>'Pengungsi','bobot'=>8,'satuan'=>'orang','panduan_skor_1'=>'<50 orang','panduan_skor_3'=>'50-500 orang','panduan_skor_5'=>'>5000 orang'],
                ['domain'=>'manusia','kode_indikator'=>'M03','nama_indikator'=>'Kelompok Rentan','bobot'=>7,'satuan'=>'persen dari total','panduan_skor_1'=>'<10%','panduan_skor_3'=>'10-30%','panduan_skor_5'=>'>50%'],
                ['domain'=>'infrastruktur','kode_indikator'=>'I01','nama_indikator'=>'Rumah Rusak Total (berat+sedang)','bobot'=>9,'satuan'=>'unit','panduan_skor_1'=>'<10 unit','panduan_skor_3'=>'10-100 unit','panduan_skor_5'=>'>500 unit'],
                ['domain'=>'infrastruktur','kode_indikator'=>'I02','nama_indikator'=>'Jalan Rusak','bobot'=>5,'satuan'=>'km','panduan_skor_1'=>'<1 km','panduan_skor_3'=>'1-10 km','panduan_skor_5'=>'>50 km'],
                ['domain'=>'infrastruktur','kode_indikator'=>'I03','nama_indikator'=>'Fasilitas Publik Rusak','bobot'=>7,'satuan'=>'unit','panduan_skor_1'=>'0 unit','panduan_skor_3'=>'1-5 unit','panduan_skor_5'=>'>20 unit'],
                ['domain'=>'lingkungan','kode_indikator'=>'L01','nama_indikator'=>'Lahan Pertanian Rusak','bobot'=>6,'satuan'=>'hektar','panduan_skor_1'=>'<5 ha','panduan_skor_3'=>'5-50 ha','panduan_skor_5'=>'>500 ha'],
                ['domain'=>'lingkungan','kode_indikator'=>'L02','nama_indikator'=>'Pencemaran Sumber Air','bobot'=>8,'satuan'=>'boolean','panduan_skor_1'=>'Tidak ada','panduan_skor_3'=>'Ada lokasi tertentu','panduan_skor_5'=>'Meluas seluruh wilayah'],
                ['domain'=>'ekonomi','kode_indikator'=>'E01','nama_indikator'=>'Estimasi Kerugian Total','bobot'=>9,'satuan'=>'miliar rupiah','panduan_skor_1'=>'<0.1 M','panduan_skor_3'=>'0.1-1 M','panduan_skor_5'=>'>10 M'],
                ['domain'=>'ekonomi','kode_indikator'=>'E02','nama_indikator'=>'Mata Pencaharian Hilang','bobot'=>7,'satuan'=>'orang','panduan_skor_1'=>'<50 orang','panduan_skor_3'=>'50-500 orang','panduan_skor_5'=>'>5000 orang'],
                ['domain'=>'sosial','kode_indikator'=>'S01','nama_indikator'=>'Kondisi Psikososial Masyarakat','bobot'=>7,'satuan'=>'skala','panduan_skor_1'=>'Stabil','panduan_skor_3'=>'Cemas tapi terkendali','panduan_skor_5'=>'Panik/trauma massal'],
                ['domain'=>'sosial','kode_indikator'=>'S02','nama_indikator'=>'Aksesibilitas Wilayah','bobot'=>8,'satuan'=>'skala','panduan_skor_1'=>'Dapat dijangkau normal','panduan_skor_3'=>'Perlu kendaraan khusus','panduan_skor_5'=>'Terisolasi total']
            ]);
        }

        if (!Schema::hasTable('assessment_skor_item')) {
            Schema::create('assessment_skor_item', function (Blueprint $table) {
                $table->id('id_skor');
                $table->unsignedBigInteger('id_assessment');
                $table->unsignedInteger('id_indikator');
                $table->decimal('nilai_terukur', 15, 4)->nullable();
                $table->tinyInteger('skor_1_5')->default(1)->comment('1=sangat rendah, 5=sangat tinggi');
                $table->text('catatan')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();

                $table->unique(['id_assessment', 'id_indikator'], 'uk_assessment_indikator');

                $table->foreign('id_assessment', 'fk_skor_assessment')
                      ->references('id_assessment_utama')->on('assessment_utama')
                      ->onDelete('cascade');
                $table->foreign('id_indikator', 'fk_skor_indikator')
                      ->references('id_indikator')->on('assessment_master_indikator_skor')
                      ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('assessment_ringkasan_skor')) {
            Schema::create('assessment_ringkasan_skor', function (Blueprint $table) {
                $table->id('id_ringkasan');
                $table->unsignedBigInteger('id_assessment')->unique();
                $table->decimal('skor_manusia', 5, 2)->default(0)->comment('0-100');
                $table->decimal('skor_infrastruktur', 5, 2)->default(0);
                $table->decimal('skor_lingkungan', 5, 2)->default(0);
                $table->decimal('skor_ekonomi', 5, 2)->default(0);
                $table->decimal('skor_sosial', 5, 2)->default(0);
                $table->decimal('skor_total', 5, 2)->default(0)->comment('Weighted average');
                $table->enum('tingkat_keparahan', ['minor', 'sedang', 'signifikan', 'berat', 'katastrofik'])->default('minor');
                $table->enum('rekomendasi_respon', ['monitoring', 'siaga', 'tanggap_cepat', 'mobilisasi_besar', 'eskalasi_nasional'])->default('monitoring');
                $table->timestamp('dihitung_pada')->useCurrent()->useCurrentOnUpdate();

                $table->foreign('id_assessment', 'fk_ringkasan_assessment')
                      ->references('id_assessment_utama')->on('assessment_utama')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_ringkasan_skor');
        Schema::dropIfExists('assessment_skor_item');
        Schema::dropIfExists('assessment_master_indikator_skor');
        Schema::dropIfExists('assessment_dampak_ekonomi');
        Schema::dropIfExists('assessment_dampak_lingkungan');
        Schema::dropIfExists('assessment_dampak_infrastruktur');
        Schema::dropIfExists('assessment_dampak_manusia_lanjutan');
        Schema::dropIfExists('assessment_narasi_kejadian');
        Schema::dropIfExists('assessment_biodata_kejadian');
    }
};
