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
        if (!Schema::hasTable('auth_pengguna_profil')) {
            Schema::create('auth_pengguna_profil', function (Blueprint $table) {
                $table->unsignedBigInteger('id_pengguna')->primary();
                $table->string('nik', 20)->nullable();
                $table->string('nama_lengkap', 255)->nullable();
                $table->string('email', 255)->nullable();
                $table->string('id_desa_domisili', 10)->nullable();

                $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_pengguna_profil');
    }
};
