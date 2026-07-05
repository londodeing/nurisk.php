<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasi_sitrep_kebutuhan', function (Blueprint $table) {
            $table->id('id_sitrep_kebutuhan');
            $table->unsignedBigInteger('id_sitrep')->index();
            
            $table->string('nama_kebutuhan');
            $table->integer('jumlah')->default(0);
            $table->string('satuan');

            $table->foreign('id_sitrep', 'fk_sitrep_kebutuhan_id_sitrep')
                  ->references('id_sitrep')->on('operasi_sitrep')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_sitrep_kebutuhan');
    }
};
