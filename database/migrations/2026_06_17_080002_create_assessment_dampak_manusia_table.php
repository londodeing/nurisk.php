<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_dampak_manusia', function (Blueprint $table) {
            $table->id('id_dampak_manusia');
            $table->unsignedBigInteger('id_assessment_utama');
            $table->integer('meninggal')->default(0);
            $table->integer('hilang')->default(0);
            $table->integer('luka_berat')->default(0);
            $table->integer('luka_ringan')->default(0);
            $table->integer('menderita_mengungsi')->default(0);

            // Standard NURISK Timestamps
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_assessment_utama', 'fk_assessment_dampak_manusia_id_assessment_utama')
                  ->references('id_assessment_utama')->on('assessment_utama')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_dampak_manusia');
    }
};
