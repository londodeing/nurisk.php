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
        if (!Schema::hasTable('wilayah_kecamatan')) {
            Schema::create('wilayah_kecamatan', function (Blueprint $table) {
                $table->string('id_kec', 6)->primary();
                $table->string('id_kab', 4);
                $table->string('nama_kec', 150);

                $table->foreign('id_kab')->references('id_kab')->on('wilayah_kabupaten')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah_kecamatan');
    }
};
