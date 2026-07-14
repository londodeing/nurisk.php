<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasi_posaju', function (Blueprint $table) {
            $table->foreign('id_pleno_pendirian')->references('id_pleno')->on('operasi_pleno')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operasi_posaju', function (Blueprint $table) {
            $table->dropForeign(['id_pleno_pendirian']);
        });
    }
};
