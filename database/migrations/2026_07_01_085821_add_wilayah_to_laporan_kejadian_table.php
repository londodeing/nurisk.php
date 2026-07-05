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
            $table->string('id_kab', 4)->nullable()->after('longitude');
            $table->string('id_kec', 7)->nullable()->after('id_kab');
            $table->string('id_desa', 10)->nullable()->after('id_kec');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_kejadian', function (Blueprint $table) {
            $table->dropColumn(['id_kab', 'id_kec', 'id_desa']);
        });
    }
};
