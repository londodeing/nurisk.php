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
        if (!Schema::hasTable('operasi_posaju')) {
            Schema::create('operasi_posaju', function (Blueprint $table) {
                $table->bigIncrements('id_posaju');
                $table->unsignedBigInteger('id_insiden')->nullable();
                $table->unsignedBigInteger('id_periode_operasi')->nullable();
                $table->unsignedBigInteger('id_pleno_pendirian')->nullable();
                $table->string('nama_posaju', 150);
                $table->unsignedBigInteger('id_surat_pendirian')->nullable();
                $table->text('alamat_lokasi')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->unsignedBigInteger('pj_posaju');
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->enum('status_alur', ['direncanakan','aktif','diperpanjang','ditutup'])->default('direncanakan');
                $table->dateTime('waktu_diaktifkan')->nullable();
                $table->dateTime('diperpanjang_hingga')->nullable();
                $table->dateTime('waktu_ditutup')->nullable();
                $table->text('alasan_penutupan')->nullable();
                $table->timestamp('dihapus_pada')->nullable();

                $table->foreign('id_insiden')->references('id_insiden')->on('operasi_insiden')->nullOnDelete();
                $table->foreign('pj_posaju')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operasi_posaju');
    }
};
