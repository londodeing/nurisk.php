<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('operasi_posaju_komandan')) {
            Schema::create('operasi_posaju_komandan', function (Blueprint $table) {
                $table->bigIncrements('id_komandan');
                $table->unsignedBigInteger('id_posaju');
                $table->unsignedBigInteger('id_pengguna');
                $table->unsignedBigInteger('id_pleno_keputusan');
                $table->dateTime('waktu_mulai_tugas');
                $table->dateTime('waktu_selesai_tugas')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('dihapus_pada')->nullable();

                $table->foreign('id_posaju')->references('id_posaju')->on('operasi_posaju')->cascadeOnDelete();
                $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
                $table->foreign('id_pleno_keputusan')->references('id_keputusan')->on('operasi_pleno_keputusan')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_posaju_komandan');
    }
};
