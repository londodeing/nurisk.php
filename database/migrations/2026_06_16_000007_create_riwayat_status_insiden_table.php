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
        if (!Schema::hasTable('riwayat_status_insiden')) {
            Schema::create('riwayat_status_insiden', function (Blueprint $table) {
                $table->bigIncrements('id_history');
                $table->unsignedBigInteger('id_insiden');
                $table->enum('status_sebelumnya', ['draft','terverifikasi','respon','pemulihan','selesai','dibatalkan'])->nullable();
                $table->enum('status_terbaru', ['draft','terverifikasi','respon','pemulihan','selesai','dibatalkan']);
                $table->unsignedBigInteger('id_pengguna');
                $table->text('alasan')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('dihapus_pada')->nullable();

                $table->foreign('id_insiden', 'fk_hist_incident')
                      ->references('id_insiden')->on('operasi_insiden')->onDelete('restrict');
                $table->foreign('id_pengguna', 'fk_hist_user')
                      ->references('id_pengguna')->on('auth_users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_insiden');
    }
};
