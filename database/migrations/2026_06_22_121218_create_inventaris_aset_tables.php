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
        if (!Schema::hasTable('inventaris_kategori')) {
            Schema::create('inventaris_kategori', function (Blueprint $table) {
                $table->increments('id_kategori');
                $table->string('nama_kategori', 100);
                $table->string('ikon', 50)->default('box');
                $table->text('deskripsi')->nullable();
                $table->boolean('aktif')->default(true);
            });

            DB::table('inventaris_kategori')->insert([
                ['id_kategori'=>1,'nama_kategori'=>'Properti & Gedung','ikon'=>'building','deskripsi'=>'Gedung, kantor, masjid, RS, dan bangunan lain','aktif'=>1],
                ['id_kategori'=>2,'nama_kategori'=>'Tanah & Lahan','ikon'=>'map','deskripsi'=>'Lahan, tanah wakaf, kebun, lapangan','aktif'=>1],
                ['id_kategori'=>3,'nama_kategori'=>'Kendaraan','ikon'=>'car','deskripsi'=>'Ambulans, mobil operasional, truk, motor','aktif'=>1],
                ['id_kategori'=>4,'nama_kategori'=>'Alat Kesehatan','ikon'=>'heart','deskripsi'=>'Peralatan medis, alat diagnostik, peralatan bedah','aktif'=>1],
                ['id_kategori'=>5,'nama_kategori'=>'Peralatan Kebencanaan','ikon'=>'alert-triangle','deskripsi'=>'Genset, perahu, pompa, tenda bencana','aktif'=>1],
                ['id_kategori'=>6,'nama_kategori'=>'Elektronik & Komunikasi','ikon'=>'wifi','deskripsi'=>'HT, laptop, server, kamera, drone','aktif'=>1],
                ['id_kategori'=>7,'nama_kategori'=>'Peralatan Dapur & Logistik','ikon'=>'package','deskripsi'=>'Mesin masak, pendingin, peralatan dapur umum','aktif'=>1],
                ['id_kategori'=>8,'nama_kategori'=>'Lainnya','ikon'=>'more-horizontal','deskripsi'=>'Aset yang tidak masuk kategori di atas','aktif'=>1],
            ]);
        }

        if (!Schema::hasTable('inventaris_jenis')) {
            Schema::create('inventaris_jenis', function (Blueprint $table) {
                $table->increments('id_jenis');
                $table->unsignedInteger('id_kategori');
                $table->string('nama_jenis', 150);
                $table->boolean('memiliki_nomor_kendaraan')->default(false)->comment('Wajib isi plat & BPKB');
                $table->boolean('memiliki_sertifikat_tanah')->default(false)->comment('Wajib isi SHM/SHGB');
                $table->boolean('wajib_asuransi')->default(false);
                $table->boolean('wajib_kalibrasi')->default(false)->comment('Untuk alat ukur medis');
                $table->integer('masa_manfaat_tahun')->nullable();
                $table->boolean('aktif')->default(true);

                $table->foreign('id_kategori', 'fk_invjenis_kategori')
                      ->references('id_kategori')->on('inventaris_kategori')
                      ->onDelete('cascade');
            });

            DB::table('inventaris_jenis')->insert([
                ['id_kategori'=>1,'nama_jenis'=>'Gedung Kantor','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>1,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>50],
                ['id_kategori'=>1,'nama_jenis'=>'Rumah Sakit / Klinik','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>1,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>50],
                ['id_kategori'=>1,'nama_jenis'=>'Masjid / Musala','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>0,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>null],
                ['id_kategori'=>1,'nama_jenis'=>'Sekolah / Madrasah / Pesantren','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>0,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>null],
                ['id_kategori'=>2,'nama_jenis'=>'Tanah Wakaf','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>1,'wajib_asuransi'=>0,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>null],
                ['id_kategori'=>2,'nama_jenis'=>'Lahan Pertanian','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>1,'wajib_asuransi'=>0,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>null],
                ['id_kategori'=>3,'nama_jenis'=>'Ambulans','memiliki_nomor_kendaraan'=>1,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>1,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>10],
                ['id_kategori'=>3,'nama_jenis'=>'Mobil Operasional','memiliki_nomor_kendaraan'=>1,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>1,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>10],
                ['id_kategori'=>3,'nama_jenis'=>'Truk Logistik','memiliki_nomor_kendaraan'=>1,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>1,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>10],
                ['id_kategori'=>3,'nama_jenis'=>'Motor Operasional','memiliki_nomor_kendaraan'=>1,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>1,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>7],
                ['id_kategori'=>4,'nama_jenis'=>'Ventilator','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>1,'wajib_kalibrasi'=>1,'masa_manfaat_tahun'=>10],
                ['id_kategori'=>4,'nama_jenis'=>'Peralatan Bedah Set','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>1,'wajib_kalibrasi'=>1,'masa_manfaat_tahun'=>15],
                ['id_kategori'=>4,'nama_jenis'=>'Alat Diagnostik (USG, X-Ray)','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>1,'wajib_kalibrasi'=>1,'masa_manfaat_tahun'=>10],
                ['id_kategori'=>5,'nama_jenis'=>'Genset Portabel','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>0,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>10],
                ['id_kategori'=>5,'nama_jenis'=>'Perahu Karet','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>0,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>5],
                ['id_kategori'=>6,'nama_jenis'=>'Handy Talky (HT)','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>0,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>5],
                ['id_kategori'=>6,'nama_jenis'=>'Drone','memiliki_nomor_kendaraan'=>0,'memiliki_sertifikat_tanah'=>0,'wajib_asuransi'=>0,'wajib_kalibrasi'=>0,'masa_manfaat_tahun'=>5],
            ]);
        }

        if (!Schema::hasTable('inventaris_aset')) {
            Schema::create('inventaris_aset', function (Blueprint $table) {
                $table->id('id_aset');
                $table->unsignedInteger('id_jenis');
                $table->integer('id_unit_pemilik')->comment('FK ke organisasi_unit');
                $table->string('nama_aset', 255);
                $table->string('kode_inventaris', 100)->unique();
                $table->string('nomor_registrasi', 100)->nullable();
                $table->year('tahun_perolehan')->nullable();
                $table->enum('cara_perolehan', ['pembelian', 'hibah', 'wakaf', 'sewa', 'pinjam', 'inventarisasi'])->default('pembelian');
                $table->decimal('nilai_perolehan', 18, 2)->default(0);
                $table->decimal('nilai_sekarang', 18, 2)->default(0);
                $table->enum('kondisi_terkini', ['sangat_baik', 'baik', 'cukup', 'rusak_ringan', 'rusak_berat', 'tidak_layak'])->default('baik');
                $table->enum('status_operasional', ['siap_pakai', 'dalam_penggunaan', 'maintenance', 'rusak', 'hilang', 'dihapus'])->default('siap_pakai');
                $table->text('lokasi_penyimpanan')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('kapasitas', 100)->nullable();
                $table->text('keterangan')->nullable();
                $table->string('foto_utama_path', 255)->nullable();
                $table->boolean('bisa_dikerahkan_bencana')->default(true);
                $table->unsignedBigInteger('id_aset_unit_operasional')->nullable();
                $table->unsignedBigInteger('dibuat_oleh')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
                $table->softDeletes('dihapus_pada');

                $table->foreign('id_jenis', 'fk_invaset_jenis')
                      ->references('id_jenis')->on('inventaris_jenis');
                $table->foreign('id_unit_pemilik', 'fk_invaset_pemilik')
                      ->references('id_unit')->on('organisasi_unit');
                $table->foreign('id_aset_unit_operasional', 'fk_invaset_aset_ops')
                      ->references('id')->on('org_assets')->onDelete('set null');
                $table->foreign('dibuat_oleh', 'fk_invaset_dibuat_oleh')
                      ->references('id_pengguna')->on('auth_users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('inventaris_lokasi_detail')) {
            Schema::create('inventaris_lokasi_detail', function (Blueprint $table) {
                $table->id('id_lokasi');
                $table->unsignedBigInteger('id_aset')->unique();
                $table->char('id_desa', 10)->nullable();
                $table->text('alamat_lengkap');
                $table->decimal('luas_bangunan_m2', 10, 2)->nullable();
                $table->decimal('luas_tanah_m2', 10, 2)->nullable();
                $table->tinyInteger('jumlah_lantai')->default(1);
                $table->year('tahun_dibangun')->nullable();
                $table->text('fasilitas_pendukung')->nullable();
                $table->enum('aksesibilitas', ['sangat_mudah', 'mudah', 'sedang', 'sulit', 'sangat_sulit'])->default('mudah');
                $table->text('keterangan_lokasi')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();

                $table->foreign('id_aset', 'fk_invlokasi_aset')
                      ->references('id_aset')->on('inventaris_aset')->onDelete('cascade');
                $table->foreign('id_desa', 'fk_invlokasi_desa')
                      ->references('id_desa')->on('wilayah_desa')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('inventaris_dokumen')) {
            Schema::create('inventaris_dokumen', function (Blueprint $table) {
                $table->id('id_dokumen');
                $table->unsignedBigInteger('id_aset');
                $table->enum('jenis_dokumen', ['bpkb', 'stnk', 'shm', 'shgb', 'sertifikat_alat', 'asuransi', 'sertifikat_kalibrasi', 'izin_operasional', 'akta_hibah', 'akta_wakaf', 'lainnya']);
                $table->string('nama_dokumen', 255);
                $table->string('nomor_dokumen', 100)->nullable();
                $table->string('instansi_penerbit', 255)->nullable();
                $table->date('tanggal_terbit')->nullable();
                $table->date('berlaku_hingga')->nullable();
                $table->string('file_path', 255)->nullable();
                $table->text('catatan')->nullable();
                $table->unsignedBigInteger('id_pengunggah')->nullable();
                $table->timestamp('diunggah_pada')->useCurrent();

                $table->foreign('id_aset', 'fk_invdok_aset')
                      ->references('id_aset')->on('inventaris_aset')->onDelete('cascade');
                $table->foreign('id_pengunggah', 'fk_invdok_pengunggah')
                      ->references('id_pengguna')->on('auth_users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('inventaris_kondisi_log')) {
            Schema::create('inventaris_kondisi_log', function (Blueprint $table) {
                $table->id('id_log');
                $table->unsignedBigInteger('id_aset');
                $table->enum('kondisi_sebelum', ['sangat_baik', 'baik', 'cukup', 'rusak_ringan', 'rusak_berat', 'tidak_layak'])->nullable();
                $table->enum('kondisi_sesudah', ['sangat_baik', 'baik', 'cukup', 'rusak_ringan', 'rusak_berat', 'tidak_layak']);
                $table->text('keterangan');
                $table->string('foto_path', 255)->nullable();
                $table->unsignedBigInteger('id_petugas');
                $table->timestamp('dicatat_pada')->useCurrent();

                $table->foreign('id_aset', 'fk_invlog_aset')
                      ->references('id_aset')->on('inventaris_aset')->onDelete('cascade');
                $table->foreign('id_petugas', 'fk_invlog_petugas')
                      ->references('id_pengguna')->on('auth_users');
            });
        }

        if (!Schema::hasTable('inventaris_pemeliharaan')) {
            Schema::create('inventaris_pemeliharaan', function (Blueprint $table) {
                $table->id('id_pemeliharaan');
                $table->unsignedBigInteger('id_aset');
                $table->enum('jenis_pemeliharaan', ['rutin', 'perbaikan', 'penggantian_komponen', 'kalibrasi', 'inspeksi', 'perpanjangan_dokumen']);
                $table->text('deskripsi_pekerjaan');
                $table->date('tanggal_dijadwalkan');
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->enum('status', ['dijadwalkan', 'dalam_proses', 'selesai', 'dibatalkan'])->default('dijadwalkan');
                $table->decimal('biaya_aktual', 15, 2)->default(0);
                $table->string('vendor_penyedia', 255)->nullable();
                $table->text('hasil_pemeliharaan')->nullable();
                $table->enum('kondisi_sesudah', ['sangat_baik', 'baik', 'cukup', 'rusak_ringan', 'rusak_berat', 'tidak_layak'])->nullable();
                $table->date('jadwal_berikutnya')->nullable();
                $table->unsignedBigInteger('id_penanggung_jawab')->nullable();
                $table->string('dokumen_path', 255)->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

                $table->foreign('id_aset', 'fk_invpm_aset')
                      ->references('id_aset')->on('inventaris_aset')->onDelete('cascade');
                $table->foreign('id_penanggung_jawab', 'fk_invpm_pj')
                      ->references('id_pengguna')->on('auth_users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('inventaris_deployment_bencana')) {
            Schema::create('inventaris_deployment_bencana', function (Blueprint $table) {
                $table->id('id_deployment');
                $table->unsignedBigInteger('id_aset');
                $table->unsignedBigInteger('id_insiden');
                $table->unsignedBigInteger('id_posaju')->nullable();
                $table->dateTime('waktu_deploy');
                $table->dateTime('waktu_kembali')->nullable();
                $table->text('tujuan_penggunaan');
                $table->enum('kondisi_saat_deploy', ['sangat_baik', 'baik', 'cukup', 'rusak_ringan', 'rusak_berat']);
                $table->enum('kondisi_saat_kembali', ['sangat_baik', 'baik', 'cukup', 'rusak_ringan', 'rusak_berat'])->nullable();
                $table->decimal('biaya_penggunaan', 15, 2)->default(0);
                $table->unsignedBigInteger('id_penanggung_jawab');
                $table->text('catatan')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();

                $table->foreign('id_aset', 'fk_invdep_aset')
                      ->references('id_aset')->on('inventaris_aset')->onDelete('cascade');
                $table->foreign('id_insiden', 'fk_invdep_insiden')
                      ->references('id_insiden')->on('operasi_insiden')->onDelete('cascade');
                $table->foreign('id_posaju', 'fk_invdep_posaju')
                      ->references('id_posaju')->on('operasi_posaju')->onDelete('set null');
                $table->foreign('id_penanggung_jawab', 'fk_invdep_pj')
                      ->references('id_pengguna')->on('auth_users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris_deployment_bencana');
        Schema::dropIfExists('inventaris_pemeliharaan');
        Schema::dropIfExists('inventaris_kondisi_log');
        Schema::dropIfExists('inventaris_dokumen');
        Schema::dropIfExists('inventaris_lokasi_detail');
        Schema::dropIfExists('inventaris_aset');
        Schema::dropIfExists('inventaris_jenis');
        Schema::dropIfExists('inventaris_kategori');
    }
};
