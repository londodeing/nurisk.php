<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operasi_pleno', function (Blueprint $table) {
            $table->index('id_insiden', 'idx_pleno_insiden');
            $table->index('status_pleno', 'idx_pleno_status');
            $table->index('dibuat_pada', 'idx_pleno_dibuat');
            $table->index(['id_insiden', 'status_pleno'], 'idx_pleno_insiden_status');
        });

        Schema::table('operasi_eskalasi', function (Blueprint $table) {
            $table->index('id_pleno', 'idx_eskalasi_pleno');
            $table->index('level_baru', 'idx_eskalasi_level');
            $table->index('waktu_eskalasi', 'idx_eskalasi_waktu');
        });

        Schema::table('operasi_surat_keluar', function (Blueprint $table) {
            $table->index('id_insiden', 'idx_surat_insiden');
            $table->index('status_surat', 'idx_surat_status');
            $table->index('tgl_terbit', 'idx_surat_tgl_terbit');
            $table->index('nomor_surat_resmi', 'idx_surat_nomor');
        });

        if (Schema::hasTable('operasi_jurnal')) {
            Schema::table('operasi_jurnal', function (Blueprint $table) {
                $table->index('waktu_event', 'idx_jurnal_waktu');
                $table->index('id_insiden', 'idx_jurnal_insiden');
                $table->index('id_pengguna', 'idx_jurnal_pengguna');
                $table->index('kategori_event', 'idx_jurnal_kategori');
                $table->index('tabel_referensi', 'idx_jurnal_tabel_ref');
                $table->index(['tabel_referensi', 'id_referensi'], 'idx_jurnal_referensi');
            });
        }
    }

    public function down(): void
    {
        Schema::table('operasi_pleno', function (Blueprint $table) {
            $table->dropIndex('idx_pleno_insiden');
            $table->dropIndex('idx_pleno_status');
            $table->dropIndex('idx_pleno_dibuat');
            $table->dropIndex('idx_pleno_insiden_status');
        });

        Schema::table('operasi_eskalasi', function (Blueprint $table) {
            $table->dropIndex('idx_eskalasi_pleno');
            $table->dropIndex('idx_eskalasi_level');
            $table->dropIndex('idx_eskalasi_waktu');
        });

        Schema::table('operasi_surat_keluar', function (Blueprint $table) {
            $table->dropIndex('idx_surat_insiden');
            $table->dropIndex('idx_surat_status');
            $table->dropIndex('idx_surat_tgl_terbit');
            $table->dropIndex('idx_surat_nomor');
        });

        if (Schema::hasTable('operasi_jurnal')) {
            Schema::table('operasi_jurnal', function (Blueprint $table) {
                $table->dropIndex('idx_jurnal_waktu');
                $table->dropIndex('idx_jurnal_insiden');
                $table->dropIndex('idx_jurnal_pengguna');
                $table->dropIndex('idx_jurnal_kategori');
                $table->dropIndex('idx_jurnal_tabel_ref');
                $table->dropIndex('idx_jurnal_referensi');
            });
        }
    }
};
