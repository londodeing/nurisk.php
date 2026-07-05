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
        if (!Schema::hasTable('operasi_klaster')) {
            Schema::create('operasi_klaster', function (Blueprint $table) {
                $table->bigIncrements('id_operasi_klaster');
                $table->unsignedBigInteger('id_insiden');
                $table->integer('id_klaster');
                $table->enum('status_klaster', ['nonaktif','aktif','selesai'])->default('aktif')->nullable();
                $table->enum('prioritas', ['rendah','sedang','tinggi','kritis'])->default('sedang')->nullable();
                $table->boolean('dibutuhkan')->default(true)->nullable();
                $table->timestamp('waktu_aktivasi')->useCurrent();
                $table->timestamp('waktu_nonaktif')->nullable();
                $table->timestamp('waktu_ditutup')->nullable();
                $table->unsignedBigInteger('dibuat_oleh')->nullable();
                $table->text('target_cakupan')->nullable();
                $table->string('indikator_keberhasilan', 255)->nullable();
                $table->decimal('progres_persen', 5, 2)->default(0)->nullable();

                $table->foreign('id_insiden')->references('id_insiden')->on('operasi_insiden')->cascadeOnDelete();
                $table->foreign('dibuat_oleh')->references('id_pengguna')->on('auth_users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operasi_klaster');
    }
};
