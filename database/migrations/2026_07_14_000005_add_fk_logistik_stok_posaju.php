<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logistik_stok', function (Blueprint $table) {
            $table->foreign('id_posaju')->references('id_posaju')->on('operasi_posaju')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('logistik_stok', function (Blueprint $table) {
            $table->dropForeign(['id_posaju']);
        });
    }
};
