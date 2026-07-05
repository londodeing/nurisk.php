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
        Schema::create('operasi_penugasan_history', function (Blueprint $table) {
            $table->id('id_history');
            $table->unsignedBigInteger('id_penugasan');
            $table->string('status_sebelumnya', 50)->nullable();
            $table->string('status_baru', 50);
            $table->timestamp('waktu_perubahan')->useCurrent();
            $table->unsignedBigInteger('diubah_oleh');

            $table->foreign('id_penugasan')->references('id_penugasan')->on('operasi_penugasan')->cascadeOnDelete();
            $table->foreign('diubah_oleh')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operasi_penugasan_history');
    }
};
