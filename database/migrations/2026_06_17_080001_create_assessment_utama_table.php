<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_utama', function (Blueprint $table) {
            $table->id('id_assessment_utama');
            $table->uuid('uuid_assessment')->unique();
            $table->unsignedBigInteger('id_insiden')->index();
            $table->enum('jenis_laporan', ['kaji_cepat', 'pendataan_lanjutan']);
            $table->string('cakupan_wilayah_deskripsi');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_latest')->default(false)->index();
            $table->dateTime('waktu_assesment');
            
            // Standard NURISK Timestamps
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();

            $table->foreign('id_insiden', 'fk_assessment_utama_id_insiden')
                  ->references('id_insiden')->on('operasi_insiden')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_utama');
    }
};
