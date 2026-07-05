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
        if (!Schema::hasTable('wilayah_kabupaten')) {
            Schema::create('wilayah_kabupaten', function (Blueprint $table) {
                $table->string('id_kab', 4)->primary();
                $table->string('nama_kab', 150);
                $table->enum('tipe', ['Kabupaten','Kota']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah_kabupaten');
    }
};
