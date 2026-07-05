<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasi_sitrep', function (Blueprint $table) {
            $table->id('id_sitrep');
            $table->uuid('uuid_sitrep')->unique();
            $table->unsignedBigInteger('id_insiden')->index();
            $table->unsignedBigInteger('id_assessment_basis')->index();
            $table->string('nomor_sitrep')->nullable();
            $table->string('periode_sitrep')->nullable();
            $table->dateTime('waktu_sitrep');
            $table->unsignedBigInteger('id_pembuat')->index();
            $table->text('catatan')->nullable();
            
            // Standard NURISK Timestamps
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();

            $table->foreign('id_insiden', 'fk_sitrep_id_insiden')
                  ->references('id_insiden')->on('operasi_insiden')
                  ->onDelete('cascade');
                  
            $table->foreign('id_assessment_basis', 'fk_sitrep_id_assessment_basis')
                  ->references('id_assessment_utama')->on('assessment_utama')
                  ->onDelete('restrict');
                  
            $table->foreign('id_pembuat', 'fk_sitrep_id_pembuat')
                  ->references('id_pengguna')->on('auth_users')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_sitrep');
    }
};
