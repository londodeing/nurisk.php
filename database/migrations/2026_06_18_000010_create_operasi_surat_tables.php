<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('master_surat_jenis')) {
            Schema::create('master_surat_jenis', function (Blueprint $table) {
                $table->integer('id_jenis_surat')->autoIncrement();
                $table->string('kode_jenis', 20)->unique();
                $table->string('nama_jenis', 150);
                $table->enum('kategori', ['UMUM','OPERASI','LOGISTIK','ASET','ORGANISASI']);
                $table->string('format_nomor', 255)->nullable();
                $table->boolean('aktif')->default(true);
                $table->text('deskripsi')->nullable();
            });
        }

        if (!Schema::hasTable('master_jabatan_penandatangan')) {
            Schema::create('master_jabatan_penandatangan', function (Blueprint $table) {
                $table->integer('id_jabatan')->autoIncrement();
                $table->string('kode_jabatan', 50)->nullable();
                $table->string('nama_jabatan', 150)->nullable();
                $table->integer('urutan_hierarki')->nullable();
                $table->boolean('aktif')->default(true);
            });
        }

        if (!Schema::hasTable('master_surat_template')) {
            Schema::create('master_surat_template', function (Blueprint $table) {
                $table->integer('id_template')->autoIncrement();
                $table->integer('id_jenis_surat');
                $table->string('nama_template', 150)->nullable();
                $table->longText('isi_template')->nullable();
                $table->boolean('aktif')->default(true);
            });
        }

        if (!Schema::hasTable('operasi_surat_keluar')) {
            Schema::create('operasi_surat_keluar', function (Blueprint $table) {
                $table->bigIncrements('id_surat');
                $table->unsignedBigInteger('id_insiden')->nullable();
                $table->integer('id_jenis_surat');
                $table->string('nomor_surat_resmi', 100)->unique();
                $table->string('perihal', 255);
                $table->date('tgl_terbit');
                $table->unsignedBigInteger('id_pengguna_ttd');
                $table->integer('id_jabatan_ttd')->nullable();
                $table->longText('isi_surat_snapshot')->nullable();
                $table->string('file_pdf_path', 255)->nullable();
                $table->enum('status_surat', ['draft','review_paraf','siap_tanda_tangan','ditandatangani','ditolak','arsip'])->default('draft');
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('dihapus_pada')->nullable();

                $table->foreign('id_jenis_surat', 'fk_surat_jenis')
                      ->references('id_jenis_surat')->on('master_surat_jenis');
                $table->foreign('id_jabatan_ttd', 'fk_surat_ke_jabatan_ttd')
                      ->references('id_jabatan')->on('master_jabatan_penandatangan')->onDelete('set null');
                $table->foreign('id_pengguna_ttd', 'fk_surat_ke_pengguna_ttd')
                      ->references('id_pengguna')->on('auth_users');
                $table->foreign('id_insiden', 'fk_surat_to_incident')
                      ->references('id_insiden')->on('operasi_insiden');
            });
        }

        if (!Schema::hasTable('dokumen_surat_paraf')) {
            Schema::create('dokumen_surat_paraf', function (Blueprint $table) {
                $table->bigIncrements('id_paraf');
                $table->unsignedBigInteger('id_surat');
                $table->unsignedBigInteger('id_pengguna');
                $table->integer('urutan')->default(1);
                $table->enum('status_paraf', ['menunggu','disetujui','ditolak'])->default('menunggu');
                $table->text('catatan')->nullable();
                $table->timestamp('waktu_paraf')->nullable();

                $table->foreign('id_surat', 'fk_paraf_surat')
                      ->references('id_surat')->on('operasi_surat_keluar')->onDelete('cascade');
                $table->foreign('id_pengguna', 'fk_paraf_ke_pengguna')
                      ->references('id_pengguna')->on('auth_users')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('dokumen_surat_tembusan')) {
            Schema::create('dokumen_surat_tembusan', function (Blueprint $table) {
                $table->bigIncrements('id_tembusan');
                $table->unsignedBigInteger('id_surat');
                $table->string('nama_pihak', 150);

                $table->foreign('id_surat', 'fk_tembusan_surat')
                      ->references('id_surat')->on('operasi_surat_keluar')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_surat_tembusan');
        Schema::dropIfExists('dokumen_surat_paraf');
        Schema::dropIfExists('operasi_surat_keluar');
        Schema::dropIfExists('master_surat_template');
        Schema::dropIfExists('master_jabatan_penandatangan');
        Schema::dropIfExists('master_surat_jenis');
    }
};
