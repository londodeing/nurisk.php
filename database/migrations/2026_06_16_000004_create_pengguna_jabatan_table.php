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
        if (!Schema::hasTable('pengguna_jabatan')) {
            Schema::create('pengguna_jabatan', function (Blueprint $table) {
                $table->bigIncrements('id_pengguna_jabatan');
                $table->unsignedBigInteger('id_pengguna');
                $table->integer('id_jabatan_posisi');
                $table->enum('tipe_lingkup', ['pwnu','pcnu','mwc','ranting','lembaga','banom']);
                $table->integer('id_lingkup');
                $table->dateTime('ditugaskan_pada')->useCurrent();
                $table->dateTime('berakhir_pada')->nullable();
                $table->boolean('status_aktif')->default(true);
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

                $table->foreign('id_pengguna', 'fk_user_pos_user')
                      ->references('id_pengguna')->on('auth_users')->onDelete('cascade');
                $table->foreign('id_jabatan_posisi', 'fk_user_pos_position')
                      ->references('id_jabatan_posisi')->on('master_jabatan')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengguna_jabatan');
    }
};
