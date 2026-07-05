<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasi_sitrep_dampak', function (Blueprint $table) {
            $table->id('id_sitrep_dampak');
            $table->unsignedBigInteger('id_sitrep')->unique(); // 1 to 1 relation with sitrep
            
            $table->integer('meninggal')->default(0);
            $table->integer('hilang')->default(0);
            $table->integer('luka_berat')->default(0);
            $table->integer('luka_ringan')->default(0);
            $table->integer('mengungsi')->default(0);

            $table->foreign('id_sitrep', 'fk_sitrep_dampak_id_sitrep')
                  ->references('id_sitrep')->on('operasi_sitrep')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_sitrep_dampak');
    }
};
