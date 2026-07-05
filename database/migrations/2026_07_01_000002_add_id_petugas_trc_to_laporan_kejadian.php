<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_kejadian', function (Blueprint $table) {
            $table->unsignedBigInteger('id_petugas_trc')->nullable()->after('id_pcnu');
            $table->foreign('id_petugas_trc')->references('id_pengguna')->on('auth_users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kejadian', function (Blueprint $table) {
            $table->dropForeign(['id_petugas_trc']);
            $table->dropColumn('id_petugas_trc');
        });
    }
};
