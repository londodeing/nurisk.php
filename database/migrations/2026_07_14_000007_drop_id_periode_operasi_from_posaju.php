<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasi_posaju', function (Blueprint $table) {
            $table->dropColumn('id_periode_operasi');
        });
    }

    public function down(): void
    {
        Schema::table('operasi_posaju', function (Blueprint $table) {
            $table->unsignedBigInteger('id_periode_operasi')->nullable()->after('id_insiden');
        });
    }
};
