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
        Schema::table('operasi_mobilisasi', function (Blueprint $table) {
            $table->index('id_insiden', 'idx_mobilisasi_insiden');
            $table->index('status_mobilisasi', 'idx_mobilisasi_status');
        });
        Schema::table('operasi_penugasan', function (Blueprint $table) {
            $table->index('id_insiden', 'idx_penugasan_insiden');
        });
        Schema::table('assessment_utama', function (Blueprint $table) {
            $table->unsignedBigInteger('id_petugas_assessment')->nullable()->after('id_insiden');
            $table->index('id_insiden', 'idx_assessment_insiden');
        });
        Schema::table('operasi_sitrep', function (Blueprint $table) {
            $table->index('id_insiden', 'idx_sitrep_insiden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operasi_mobilisasi', function (Blueprint $table) {
            $table->dropIndex('idx_mobilisasi_insiden');
            $table->dropIndex('idx_mobilisasi_status');
        });
        Schema::table('operasi_penugasan', function (Blueprint $table) {
            $table->dropIndex('idx_penugasan_insiden');
        });
        Schema::table('assessment_utama', function (Blueprint $table) {
            $table->dropColumn('id_petugas_assessment');
            $table->dropIndex('idx_assessment_insiden');
        });
        Schema::table('operasi_sitrep', function (Blueprint $table) {
            $table->dropIndex('idx_sitrep_insiden');
        });
    }
};
