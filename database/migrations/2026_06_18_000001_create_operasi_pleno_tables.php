<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('operasi_pleno')) {
            Schema::create('operasi_pleno', function (Blueprint $table) {
                $table->bigIncrements('id_pleno');
                $table->unsignedBigInteger('id_insiden');
                $table->string('nomor_pleno', 100);
                $table->dateTime('waktu_pleno');
                $table->enum('jenis_pleno', ['aktivasi_operasi', 'evaluasi_rutin', 'perpanjangan_operasi', 'penutupan_operasi', 'eskalasi_wilayah', 'khusus'])->default('evaluasi_rutin');
                $table->unsignedBigInteger('pimpinan_pleno');
                $table->unsignedBigInteger('notulis_pleno');
                $table->unsignedBigInteger('disetujui_oleh')->nullable();
                $table->timestamp('waktu_disetujui')->nullable();
                $table->string('lokasi_pleno', 255)->default('Posko Utama');
                $table->text('hasil_umum')->nullable();
                $table->string('file_notulensi_path', 255)->nullable();
                $table->enum('status_pleno', ['draft', 'ditinjau', 'disetujui', 'ditandatangani', 'final', 'dibatalkan'])->default('draft');
                $table->unsignedBigInteger('id_penandatangan')->nullable();
                $table->dateTime('waktu_ditandatangani')->nullable();
                $table->string('hash_dokumen', 64)->nullable();
                $table->enum('metode_tanda_tangan', ['pki_e-sign', 'verifikasi_manual'])->nullable();
                $table->dateTime('waktu_dikunci')->nullable();
                $table->dateTime('waktu_difinalisasi')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('dihapus_pada')->nullable();

                $table->foreign('id_insiden', 'fk_pleno_incident')->references('id_insiden')->on('operasi_insiden')->onDelete('restrict');
                $table->foreign('pimpinan_pleno', 'fk_pleno_pimpinan')->references('id_pengguna')->on('auth_users');
                $table->foreign('notulis_pleno', 'fk_pleno_notulis')->references('id_pengguna')->on('auth_users');
                $table->foreign('disetujui_oleh', 'fk_pleno_approver_v3')->references('id_pengguna')->on('auth_users');
                $table->foreign('id_penandatangan', 'fk_pleno_signed_by')->references('id_pengguna')->on('auth_users');
            });
        }

        if (!Schema::hasTable('operasi_pleno_keputusan')) {
            Schema::create('operasi_pleno_keputusan', function (Blueprint $table) {
                $table->bigIncrements('id_keputusan');
                $table->unsignedBigInteger('id_pleno');
                $table->enum('kategori_objek', ['insiden', 'posaju', 'klaster', 'personil', 'logistik', 'anggaran', 'status_insiden', 'aktivasi_posko', 'aktivasi_klaster', 'mobilisasi_relawan', 'eskalasi_wilayah', 'lainnya']);
                $table->enum('jenis_keputusan', ['penunjukan_personil', 'aktivasi_pos', 'perubahan_status_insiden', 'alokasi_sumberdaya', 'lainnya']);
                $table->enum('tipe_target_keputusan', ['pos_aju', 'personil', 'logistik', 'insiden', 'klaster', 'perpanjangan_operasi']);
                $table->string('referensi_tabel', 50)->nullable();
                $table->unsignedBigInteger('referensi_id')->nullable();
                $table->text('deskripsi_keputusan');
                $table->enum('status_pelaksanaan', ['rencana', 'berjalan', 'selesai'])->default('rencana');

                $table->foreign('id_pleno', 'fk_keputusan_pleno')->references('id_pleno')->on('operasi_pleno');
            });
        }

        if (!Schema::hasTable('operasi_pleno_peserta')) {
            Schema::create('operasi_pleno_peserta', function (Blueprint $table) {
                $table->bigIncrements('id_pleno_peserta');
                $table->unsignedBigInteger('id_pleno');
                $table->unsignedBigInteger('id_pengguna');
                $table->string('peran_dalam_rapat', 100)->default('Peserta');
                $table->enum('status_kehadiran', ['hadir', 'izin', 'tanpa_keterangan'])->default('hadir');
                $table->boolean('hak_suara')->default(false);
                $table->enum('status_persetujuan', ['setuju', 'tolak', 'abstain'])->default('setuju');
                $table->text('catatan_peserta')->nullable();

                $table->foreign('id_pleno', 'fk_opp_pleno')->references('id_pleno')->on('operasi_pleno')->onDelete('cascade');
                $table->foreign('id_pengguna', 'fk_opp_pengguna_final')->references('id_pengguna')->on('auth_users')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('operasi_eskalasi')) {
            Schema::create('operasi_eskalasi', function (Blueprint $table) {
                $table->bigIncrements('id_eskalasi');
                $table->unsignedBigInteger('id_insiden');
                $table->unsignedBigInteger('id_pleno');
                $table->enum('level_sebelumnya', ['lokal', 'pcnu', 'pwnu', 'nasional']);
                $table->enum('level_baru', ['lokal', 'pcnu', 'pwnu', 'nasional']);
                $table->text('alasan_eskalasi');
                $table->timestamp('waktu_eskalasi')->useCurrent();

                $table->foreign('id_insiden', 'fk_eskalasi_inc')->references('id_insiden')->on('operasi_insiden')->onDelete('restrict');
                $table->foreign('id_pleno', 'fk_eskalasi_pleno')->references('id_pleno')->on('operasi_pleno');
            });
        }

        if (!Schema::hasTable('operasi_aktivasi')) {
            Schema::create('operasi_aktivasi', function (Blueprint $table) {
                $table->bigIncrements('id_aktivasi');
                $table->unsignedBigInteger('id_insiden')->nullable();
                $table->unsignedBigInteger('id_komandan');
                $table->unsignedBigInteger('id_surat_tugas');
                $table->enum('status_darurat', ['siaga', 'tanggap_darurat', 'pemulihan', 'selesai'])->default('siaga');
                $table->timestamp('waktu_mulai')->useCurrent();
                $table->timestamp('waktu_selesai')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_aktivasi');
        Schema::dropIfExists('operasi_eskalasi');
        Schema::dropIfExists('operasi_pleno_peserta');
        Schema::dropIfExists('operasi_pleno_keputusan');
        Schema::dropIfExists('operasi_pleno');
    }
};
