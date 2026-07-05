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
        if (!Schema::hasTable('relawan_penugasan')) {
            Schema::create('relawan_penugasan', function (Blueprint $table) {
                $table->bigIncrements('id_penugasan_relawan');
                $table->unsignedBigInteger('id_pendaftaran');
                $table->unsignedBigInteger('id_penugasan_insiden')->nullable();
                $table->unsignedBigInteger('id_posaju')->nullable();
                $table->string('peran_lapangan', 150)->nullable();
                $table->date('tgl_mulai_aktif')->nullable();
                $table->date('tgl_selesai_aktif')->nullable();
                $table->boolean('status_aktif')->default(true);
                $table->unsignedBigInteger('id_surat_tugas')->nullable();
                $table->timestamp('dihapus_pada')->nullable();

                $table->foreign('id_pendaftaran')->references('id_pendaftaran')->on('relawan_pendaftaran')->cascadeOnDelete();
                $table->foreign('id_penugasan_insiden')->references('id_incident_assignment')->on('operasi_penugasan')->nullOnDelete();
                $table->foreign('id_posaju')->references('id_posaju')->on('operasi_posaju')->nullOnDelete();
                $table->foreign('id_surat_tugas')->references('id_surat')->on('operasi_surat_keluar')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relawan_penugasan');
    }
};
