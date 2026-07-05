<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasi_sitrep', function (Blueprint $table) {
            $table->integer('jumlah_personel')->default(0)->after('id_pembuat');
            $table->integer('jumlah_klaster_aktif')->default(0)->after('jumlah_personel');
        });
    }

    public function down(): void
    {
        Schema::table('operasi_sitrep', function (Blueprint $table) {
            $table->dropColumn(['jumlah_personel', 'jumlah_klaster_aktif']);
        });
    }
};
