<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('laporan_kejadian')) {
            Schema::create('laporan_kejadian', function (Blueprint $table) {
                $table->bigIncrements('id_laporan_kejadian');
                $table->string('kode_kejadian', 25)->unique();
                $table->unsignedBigInteger('id_pengguna')->nullable();
                $table->integer('id_jenis_bencana');
                $table->string('nama_pelapor', 150);
                $table->string('hp_pelapor', 20);
                $table->text('keterangan_situasi');
                $table->text('titik_kenal')->nullable();
                $table->dateTime('waktu_kejadian');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->string('photo_path', 255)->nullable();
                $table->enum('is_valid', ['menunggu','ya','tidak'])->default('menunggu');
                $table->text('catatan_validasi')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

                $table->foreign('id_jenis_bencana', 'fk_lap_jenis')
                      ->references('id_jenis')->on('bencana_master_jenis');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kejadian');
    }
};
