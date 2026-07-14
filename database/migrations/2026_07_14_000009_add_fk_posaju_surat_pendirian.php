<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasi_posaju', function (Blueprint $table) {
            $table->foreign('id_surat_pendirian')->references('id_surat')->on('operasi_surat_keluar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operasi_posaju', function (Blueprint $table) {
            $table->dropForeign(['id_surat_pendirian']);
        });
    }
};
