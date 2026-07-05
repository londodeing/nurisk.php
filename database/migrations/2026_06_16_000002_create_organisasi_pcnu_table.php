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
        if (!Schema::hasTable('organisasi_pcnu')) {
            Schema::create('organisasi_pcnu', function (Blueprint $table) {
                $table->integer('id_pcnu')->autoIncrement();
                $table->integer('id_unit');
                $table->string('nama_pcnu', 100);

                $table->foreign('id_unit')->references('id_unit')->on('organisasi_unit')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisasi_pcnu');
    }
};
