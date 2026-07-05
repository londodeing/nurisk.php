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
        if (!Schema::hasTable('relawan_kebutuhan')) {
            Schema::create('relawan_kebutuhan', function (Blueprint $table) {
                $table->bigIncrements('id_relawan_kebutuhan');
                $table->unsignedBigInteger('id_insiden');
                $table->unsignedBigInteger('id_operasi_klaster')->nullable();
                $table->unsignedBigInteger('id_posaju')->nullable();
                $table->integer('id_keahlian_utama')->nullable();
                $table->string('judul_posisi', 255)->nullable();
                $table->text('deskripsi_tugas')->nullable();
                $table->text('persyaratan')->nullable();
                $table->enum('status_rekrutmen', ['dibuka', 'terpenuhi', 'dibatalkan', 'ditutup'])->default('dibuka');
                $table->date('tgl_mulai_tugas')->nullable();
                $table->date('tgl_selesai_tugas')->nullable();
                $table->integer('jumlah_dibutuhkan')->default(1);
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('dihapus_pada')->nullable();

                $table->foreign('id_insiden')->references('id_insiden')->on('operasi_insiden')->cascadeOnDelete();
                $table->foreign('id_operasi_klaster')->references('id_operasi_klaster')->on('operasi_klaster')->nullOnDelete();
                $table->foreign('id_posaju')->references('id_posaju')->on('operasi_posaju')->nullOnDelete();
                $table->foreign('id_keahlian_utama')->references('id_keahlian')->on('auth_keahlian_master')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relawan_kebutuhan');
    }
};
