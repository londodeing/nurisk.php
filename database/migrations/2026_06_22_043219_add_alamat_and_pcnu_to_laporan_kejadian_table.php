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
        Schema::table('laporan_kejadian', function (Blueprint $table) {
            $table->text('alamat_lengkap')->nullable()->after('longitude');
            $table->integer('id_pcnu')->nullable()->after('id_jenis_bencana');
            
            $table->foreign('id_pcnu')->references('id_pcnu')->on('organisasi_pcnu')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_kejadian', function (Blueprint $table) {
            $table->dropForeign(['id_pcnu']);
            $table->dropColumn(['alamat_lengkap', 'id_pcnu']);
        });
    }
};
