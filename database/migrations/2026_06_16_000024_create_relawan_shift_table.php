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
        if (!Schema::hasTable('relawan_shift')) {
            Schema::create('relawan_shift', function (Blueprint $table) {
                $table->bigIncrements('id_relawan_shift');
                $table->unsignedBigInteger('id_penugasan_relawan');
                $table->dateTime('waktu_mulai');
                $table->dateTime('waktu_selesai');

                $table->foreign('id_penugasan_relawan')->references('id_penugasan_relawan')->on('relawan_penugasan')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relawan_shift');
    }
};
