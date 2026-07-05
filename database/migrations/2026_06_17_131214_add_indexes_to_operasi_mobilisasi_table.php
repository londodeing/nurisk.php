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
            $table->index('id_insiden');
            $table->index('id_pengguna');
            $table->index('status_mobilisasi');
            $table->index('sync_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operasi_mobilisasi', function (Blueprint $table) {
            $table->dropIndex(['id_insiden']);
            $table->dropIndex(['id_pengguna']);
            $table->dropIndex(['status_mobilisasi']);
            $table->dropIndex(['sync_version']);
        });
    }
};
