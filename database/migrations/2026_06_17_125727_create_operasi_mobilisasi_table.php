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
        Schema::create('operasi_mobilisasi', function (Blueprint $table) {
            $table->id('id_mobilisasi');
            $table->uuid('uuid_mobilisasi')->unique();
            $table->unsignedBigInteger('id_insiden');
            $table->unsignedBigInteger('id_pengguna');
            $table->string('jenis_mobilisasi', 100);
            $table->string('status_mobilisasi', 50)->default('draft');
            $table->string('lokasi_asal')->nullable();
            $table->string('lokasi_tujuan')->nullable();
            $table->dateTime('waktu_berangkat')->nullable();
            $table->dateTime('waktu_tiba')->nullable();
            $table->text('catatan')->nullable();
            $table->bigInteger('sync_version')->default(1);
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->text('alasan_hapus')->nullable();

            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();

            $table->foreign('id_insiden')->references('id_insiden')->on('operasi_insiden')->cascadeOnDelete();
            $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
            $table->foreign('created_by')->references('id_pengguna')->on('auth_users')->nullOnDelete();
            $table->foreign('updated_by')->references('id_pengguna')->on('auth_users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id_pengguna')->on('auth_users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operasi_mobilisasi');
    }
};
