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
        if (!Schema::hasTable('relawan_pendaftaran')) {
            Schema::create('relawan_pendaftaran', function (Blueprint $table) {
                $table->bigIncrements('id_pendaftaran');
                $table->unsignedBigInteger('id_relawan_kebutuhan');
                $table->unsignedBigInteger('id_pengguna');
                $table->text('motivasi_singkat')->nullable();
                $table->enum('status_pendaftaran', ['dibuka', 'seleksi', 'diterima', 'ditugaskan', 'selesai', 'ditolak'])->default('dibuka');
                $table->unsignedBigInteger('id_verifikator')->nullable();
                $table->unsignedBigInteger('id_penyaring')->nullable();
                $table->text('catatan_verifikator')->nullable();
                $table->timestamp('waktu_daftar')->useCurrent();
                $table->timestamp('waktu_verifikasi')->nullable();
                $table->dateTime('waktu_penyaringan')->nullable();
                $table->timestamp('waktu_penugasan_dimulai')->nullable();
                $table->timestamp('waktu_penugasan_selesai')->nullable();
                $table->timestamp('dihapus_pada')->nullable();

                $table->unique(['id_pengguna', 'id_relawan_kebutuhan']);

                $table->foreign('id_relawan_kebutuhan')->references('id_relawan_kebutuhan')->on('relawan_kebutuhan')->cascadeOnDelete();
                $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
                $table->foreign('id_verifikator')->references('id_pengguna')->on('auth_users')->nullOnDelete();
                $table->foreign('id_penyaring')->references('id_pengguna')->on('auth_users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relawan_pendaftaran');
    }
};
