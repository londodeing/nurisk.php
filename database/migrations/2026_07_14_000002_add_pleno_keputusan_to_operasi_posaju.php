<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasi_posaju', function (Blueprint $table) {
            if (!Schema::hasColumn('operasi_posaju', 'id_pleno_keputusan')) {
                $table->unsignedBigInteger('id_pleno_keputusan')->nullable()->after('id_pleno_pendirian');
                $table->foreign('id_pleno_keputusan')->references('id_keputusan')->on('operasi_pleno_keputusan')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('operasi_posaju', function (Blueprint $table) {
            $table->dropForeign(['id_pleno_keputusan']);
            $table->dropColumn('id_pleno_keputusan');
        });
    }
};
