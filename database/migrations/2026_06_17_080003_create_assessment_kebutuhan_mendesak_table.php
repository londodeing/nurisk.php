<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_kebutuhan_mendesak', function (Blueprint $table) {
            $table->id('id_kebutuhan_mendesak');
            $table->unsignedBigInteger('id_assessment_utama');
            $table->string('nama_kebutuhan');
            $table->integer('jumlah');
            $table->string('satuan');
            $table->text('catatan')->nullable();

            // Standard NURISK Timestamps
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_assessment_utama', 'fk_assessment_kebutuhan_mendesak_id_assessment_utama')
                  ->references('id_assessment_utama')->on('assessment_utama')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_kebutuhan_mendesak');
    }
};
