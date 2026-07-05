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
        if (!Schema::hasTable('master_jabatan')) {
            Schema::create('master_jabatan', function (Blueprint $table) {
                $table->integer('id_jabatan_posisi')->autoIncrement();
                $table->string('nama_jabatan', 150);
                $table->string('slug', 150)->unique();
                $table->text('deskripsi')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_jabatan');
    }
};
