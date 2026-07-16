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
        Schema::table('assessment_kebutuhan_lanjutan', function (Blueprint $table) {
            $table->dropColumn('kebutuhan_dana');
        });

        Schema::table('assessment_dampak_rumah', function (Blueprint $table) {
            $table->dropColumn('estimasi_kerugian_juta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_dampak_rumah', function (Blueprint $table) {
            $table->decimal('estimasi_kerugian_juta', 12, 2)->default(0);
        });

        Schema::table('assessment_kebutuhan_lanjutan', function (Blueprint $table) {
            $table->text('kebutuhan_dana')->nullable();
        });
    }
};
