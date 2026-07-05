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
        if (!Schema::hasTable('auth_keahlian_master')) {
            Schema::create('auth_keahlian_master', function (Blueprint $table) {
                $table->integer('id_keahlian')->autoIncrement();
                $table->string('nama_keahlian', 255);
                $table->string('deskripsi', 255)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_keahlian_master');
    }
};
