<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasi_penugasan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_posaju')->nullable()->after('id_klaster_operasi');
            $table->foreign('id_posaju')->references('id_posaju')->on('operasi_posaju')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operasi_penugasan', function (Blueprint $table) {
            $table->dropForeign(['id_posaju']);
            $table->dropColumn('id_posaju');
        });
    }
};
