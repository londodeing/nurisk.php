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
        if (!Schema::hasTable('operasi_penugasan')) {
            Schema::create('operasi_penugasan', function (Blueprint $table) {
                $table->bigIncrements('id_incident_assignment');
                $table->unsignedBigInteger('id_insiden');
                $table->unsignedBigInteger('id_pengguna');
                $table->enum('peran_otoritas', ['komandan_insiden','trc','relawan','medis','logistik','operator']);
                $table->unsignedBigInteger('ditugaskan_oleh');
                $table->string('asal_lingkup', 100)->nullable();
                $table->string('tujuan_lingkup', 100)->nullable();
                $table->dateTime('waktu_mulai');
                $table->dateTime('waktu_selesai')->nullable();
                $table->string('status_penugasan', 50)->default('aktif');
                $table->text('catatan')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
                $table->timestamp('dihapus_pada')->nullable();

                $table->foreign('id_insiden')->references('id_insiden')->on('operasi_insiden')->cascadeOnDelete();
                $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
                $table->foreign('ditugaskan_oleh')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operasi_penugasan');
    }
};
