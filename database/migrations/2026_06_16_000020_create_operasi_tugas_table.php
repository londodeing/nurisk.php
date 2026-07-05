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
        if (!Schema::hasTable('operasi_tugas')) {
            Schema::create('operasi_tugas', function (Blueprint $table) {
                $table->bigIncrements('id_tugas');
                $table->unsignedBigInteger('id_operasi_klaster');
                $table->unsignedBigInteger('id_posaju')->nullable();
                $table->unsignedBigInteger('ditugaskan_ke')->nullable();
                $table->unsignedBigInteger('id_surat_perintah')->nullable();
                $table->string('judul_tugas', 255);
                $table->string('target_indikator', 255)->nullable();
                $table->enum('status_tugas', ['rencana','berjalan','tertunda','selesai'])->default('rencana')->nullable();
                $table->decimal('progres_persen', 5, 2)->default(0)->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('dihapus_pada')->nullable();

                $table->foreign('id_operasi_klaster')->references('id_operasi_klaster')->on('operasi_klaster')->cascadeOnDelete();
                $table->foreign('id_posaju')->references('id_posaju')->on('operasi_posaju')->nullOnDelete();
                $table->foreign('ditugaskan_ke')->references('id_pengguna')->on('auth_users')->nullOnDelete();
                $table->foreign('id_surat_perintah')->references('id_surat')->on('operasi_surat_keluar')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operasi_tugas');
    }
};
