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
        if (!Schema::hasTable('wilayah_desa')) {
            Schema::create('wilayah_desa', function (Blueprint $table) {
                $table->string('id_desa', 10)->primary();
                $table->string('id_kec', 6);
                $table->string('nama_desa', 150);

                $table->foreign('id_kec')->references('id_kec')->on('wilayah_kecamatan')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah_desa');
    }
};
