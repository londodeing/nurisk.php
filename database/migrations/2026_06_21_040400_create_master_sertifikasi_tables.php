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
        Schema::create('master_sertifikasi', function (Blueprint $table) {
            $table->id('id_sertifikasi');
            $table->string('nama_sertifikasi', 150);
            $table->string('lembaga_penerbit', 150)->nullable();
        });

        Schema::create('relawan_sertifikasi', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pengguna');
            $table->unsignedBigInteger('id_sertifikasi');
            $table->date('tanggal_terbit')->nullable();
            $table->date('tanggal_kedaluwarsa')->nullable();

            $table->primary(['id_pengguna', 'id_sertifikasi']);
            $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
            $table->foreign('id_sertifikasi')->references('id_sertifikasi')->on('master_sertifikasi')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relawan_sertifikasi');
        Schema::dropIfExists('master_sertifikasi');
    }
};
