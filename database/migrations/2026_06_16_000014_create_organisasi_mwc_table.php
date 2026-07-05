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
        if (!Schema::hasTable('organisasi_mwc')) {
            Schema::create('organisasi_mwc', function (Blueprint $table) {
                $table->integer('id_mwc')->autoIncrement();
                $table->integer('id_pcnu');
                $table->string('nama_mwc', 150);
                $table->integer('id_unit')->nullable();

                $table->foreign('id_pcnu')->references('id_pcnu')->on('organisasi_pcnu')->cascadeOnDelete();
                $table->foreign('id_unit')->references('id_unit')->on('organisasi_unit')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisasi_mwc');
    }
};
