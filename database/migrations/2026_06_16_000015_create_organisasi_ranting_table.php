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
        if (!Schema::hasTable('organisasi_ranting')) {
            Schema::create('organisasi_ranting', function (Blueprint $table) {
                $table->integer('id_ranting')->autoIncrement();
                $table->integer('id_mwc');
                $table->string('nama_ranting', 150);
                $table->integer('id_unit')->nullable();

                $table->foreign('id_mwc')->references('id_mwc')->on('organisasi_mwc')->cascadeOnDelete();
                $table->foreign('id_unit')->references('id_unit')->on('organisasi_unit')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisasi_ranting');
    }
};
