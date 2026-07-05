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
        Schema::table('operasi_pleno_keputusan', function (Blueprint $table) {
            $table->text('payload_eksekusi')->nullable()->after('deskripsi_keputusan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operasi_pleno_keputusan', function (Blueprint $table) {
            $table->dropColumn('payload_eksekusi');
        });
    }
};
