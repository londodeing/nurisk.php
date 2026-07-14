<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        Schema::table('operasi_pleno_keputusan', function (Blueprint $table) {
            DB::statement("ALTER TABLE operasi_pleno_keputusan MODIFY COLUMN kategori_objek ENUM('insiden','posaju','klaster','personil','logistik','anggaran','status_insiden','aktivasi_posko','aktivasi_klaster','mobilisasi_relawan','eskalasi_wilayah','lainnya') NOT NULL");
            DB::statement("ALTER TABLE operasi_pleno_keputusan MODIFY COLUMN status_pelaksanaan ENUM('rencana','berjalan','selesai','dieksekusi','menunggu') NOT NULL DEFAULT 'rencana'");
        });
    }

    public function down(): void
    {
        Schema::table('operasi_pleno_keputusan', function (Blueprint $table) {
            DB::statement("ALTER TABLE operasi_pleno_keputusan MODIFY COLUMN kategori_objek ENUM('insiden','posaju','klaster','personil','logistik','anggaran') NOT NULL");
            DB::statement("ALTER TABLE operasi_pleno_keputusan MODIFY COLUMN status_pelaksanaan ENUM('rencana','berjalan','selesai') NOT NULL DEFAULT 'rencana'");
        });
    }
};
