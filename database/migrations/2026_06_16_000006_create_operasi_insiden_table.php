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
        if (!Schema::hasTable('operasi_insiden')) {
            Schema::create('operasi_insiden', function (Blueprint $table) {
                $table->bigIncrements('id_insiden');
                $table->uuid('uuid_insiden')->unique();
                $table->string('kode_kejadian', 25)->unique();
                $table->unsignedBigInteger('id_laporan_asal')->nullable();
                $table->integer('id_jenis_bencana');
                $table->integer('id_pcnu');
                $table->integer('id_mwc')->nullable();
                $table->enum('status_insiden', ['draft','terverifikasi','respon','pemulihan','selesai','dibatalkan'])->default('draft');
                $table->enum('status_operasi', ['monitoring','siaga','tanggap_darurat','pemulihan','selesai'])->default('monitoring');
                $table->boolean('is_locked')->default(false);
                $table->enum('prioritas', ['rendah','sedang','tinggi','kritis'])->default('sedang');
                $table->string('no_spk_assesment', 50)->nullable();
                $table->date('tgl_spk_assesment')->nullable();
                $table->unsignedBigInteger('id_pemberi_spk')->nullable();
                $table->unsignedBigInteger('id_penerima_spk')->nullable();
                $table->dateTime('waktu_mulai');
                $table->dateTime('waktu_selesai')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
                $table->dateTime('waktu_verifikasi')->nullable();
                $table->dateTime('waktu_respon_dimulai')->nullable();
                $table->dateTime('waktu_pemulihan_dimulai')->nullable();
                $table->dateTime('waktu_ditutup')->nullable();
                $table->timestamp('dihapus_pada')->nullable();

                $table->foreign('id_jenis_bencana', 'fk_inc_jenis')
                      ->references('id_jenis')->on('bencana_master_jenis');
                $table->foreign('id_pcnu', 'fk_inc_pcnu')
                      ->references('id_pcnu')->on('organisasi_pcnu');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operasi_insiden');
    }
};
