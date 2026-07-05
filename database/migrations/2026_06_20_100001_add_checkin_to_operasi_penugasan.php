<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasi_penugasan', function (Blueprint $table) {
            $table->timestamp('waktu_checkin')->nullable()->after('waktu_selesai');
            $table->timestamp('waktu_checkout')->nullable()->after('waktu_checkin');
            $table->string('lokasi_checkin')->nullable()->after('waktu_checkout');
            $table->string('lokasi_checkout')->nullable()->after('lokasi_checkin');
        });
    }

    public function down(): void
    {
        Schema::table('operasi_penugasan', function (Blueprint $table) {
            $table->dropColumn(['waktu_checkin', 'waktu_checkout', 'lokasi_checkin', 'lokasi_checkout']);
        });
    }
};
