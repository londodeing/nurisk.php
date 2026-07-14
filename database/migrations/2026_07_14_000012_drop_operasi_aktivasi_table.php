<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('operasi_aktivasi');
    }

    public function down(): void
    {
        Schema::create('operasi_aktivasi', function (Blueprint $table) {
            $table->id('id_aktivasi');
            $table->unsignedBigInteger('id_insiden')->nullable();
            $table->unsignedBigInteger('id_komandan')->nullable();
            $table->unsignedBigInteger('id_surat_tugas')->nullable();
            $table->enum('status_darurat', ['siaga','tanggap_darurat','pemulihan','selesai'])->default('siaga');
            $table->timestamp('waktu_mulai')->useCurrent();
            $table->timestamp('waktu_selesai')->nullable();
        });
    }
};