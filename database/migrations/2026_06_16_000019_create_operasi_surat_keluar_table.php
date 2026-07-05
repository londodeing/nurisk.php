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

                $table->foreign('id_insiden')->references('id_insiden')->on('operasi_insiden')->nullOnDelete();
                $table->foreign('id_pengguna_ttd')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operasi_surat_keluar');
    }
};
