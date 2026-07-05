<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected static $sqliteMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            if (!Schema::hasTable('auth_roles')) {
                $this->setupMinimalSchema();
            }
        }
    }

    public function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $guard = null)
    {
        // Selalu otentikasi menggunakan Sanctum agar semua API route lolos otentikasi
        \Laravel\Sanctum\Sanctum::actingAs($user, ['*']);
        
        // Tetap otentikasi di guard aslinya (web) agar UI route lolos
        return parent::actingAs($user, $guard);
    }
    protected function refreshTestDatabase()
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // Already handled in setUp()
            $this->beginDatabaseTransaction();
            return;
        }

        if (! \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated) {
            $this->artisan('migrate:fresh', $this->migrateFreshUsing());
            \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    protected function setupMinimalSchema(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');
        try {
            DB::statement('PRAGMA journal_mode = WAL');
        } catch (\Throwable $e) {
            // Ignore — may fail inside transactions (DatabaseTransactions trait)
        }

        $migrations = glob(database_path('migrations/*.php'));
        sort($migrations);

        $skip = [
            'add_missing_indexes_for_m10',
            'add_missing_indexes_for_production_hardening',
            'add_indexes_to_governance_tables',
            'add_aggregates_to_operasi_sitrep',
            'recreate_operasi_klaster_table',
            'recreate_operasi_penugasan_table',
            'sqlite_compat_fix',
            'create_relawan_penugasan_table',
            'create_operasi_tugas_table',
            'create_operasi_penugasan_history_table',
        ];

        foreach ($migrations as $file) {
            $base = basename($file);
            $skipThis = false;
            foreach ($skip as $s) {
                if (str_contains($base, $s)) {
                    $skipThis = true;
                    break;
                }
            }
            if ($skipThis) {
                continue;
            }

            try {
                $m = require $file;
                if (is_object($m) && method_exists($m, 'up')) {
                    $m->up();
                }
            } catch (\Throwable $e) {
                fwrite(STDERR, "SKIP {$base}: " . $e->getMessage() . PHP_EOL);
            }
        }

        DB::statement('PRAGMA foreign_keys = ON');

        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $this->applySqliteSchemaFixes();
        }
    }

    protected function applySqliteSchemaFixes(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        // Recreate operasi_sitrep
        Schema::dropIfExists('operasi_sitrep');
        Schema::create('operasi_sitrep', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id('id_sitrep');
            $table->uuid('uuid_sitrep')->unique();
            $table->unsignedBigInteger('id_insiden');
            $table->unsignedBigInteger('id_posaju')->nullable();
            $table->unsignedBigInteger('id_assessment_basis')->nullable();
            $table->string('nomor_sitrep')->nullable();
            $table->string('periode_sitrep')->nullable();
            $table->dateTime('waktu_sitrep')->nullable();
            $table->unsignedBigInteger('id_pembuat');
            $table->text('catatan')->nullable();
            $table->string('periode_waktu', 50)->nullable();
            $table->text('situasi_umum')->nullable();
            $table->text('upaya_dilakukan')->nullable();
            $table->text('kendala_hambatan')->nullable();
            $table->text('kebutuhan_mendesak')->nullable();
            $table->text('rencana_tindak_lanjut')->nullable();
            $table->string('status_laporan', 50)->default('draft');
            $table->unsignedBigInteger('diverifikasi_oleh')->nullable();
            $table->dateTime('waktu_verifikasi')->nullable();
            $table->integer('jumlah_korban_jiwa')->default(0);
            $table->integer('jumlah_luka_luka')->default(0);
            $table->integer('jumlah_mengungsi')->default(0);
            $table->integer('jumlah_personel')->default(0);
            $table->integer('jumlah_klaster_aktif')->default(0);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();
            $table->bigInteger('sync_version')->default(1)->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->text('alasan_hapus')->nullable();
        });

        Schema::dropIfExists('relawan_pendaftaran');
        Schema::dropIfExists('relawan_kebutuhan');

        Schema::create('relawan_kebutuhan', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->bigIncrements('id_relawan_kebutuhan');
            $table->unsignedBigInteger('id_insiden');
            $table->unsignedBigInteger('id_operasi_klaster')->nullable();
            $table->unsignedBigInteger('id_posaju')->nullable();
            $table->integer('id_keahlian_utama')->nullable();
            $table->string('judul_posisi', 255)->nullable();
            $table->text('deskripsi_tugas')->nullable();
            $table->text('persyaratan')->nullable();
            $table->enum('status_rekrutmen', ['dibuka', 'terpenuhi', 'dibatalkan', 'ditutup'])->default('dibuka');
            $table->date('tgl_mulai_tugas')->nullable();
            $table->date('tgl_selesai_tugas')->nullable();
            $table->integer('jumlah_dibutuhkan')->default(1);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('dihapus_pada')->nullable();
        });

        Schema::create('relawan_pendaftaran', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->bigIncrements('id_pendaftaran');
            $table->unsignedBigInteger('id_relawan_kebutuhan');
            $table->unsignedBigInteger('id_pengguna');
            $table->text('motivasi_singkat')->nullable();
            $table->enum('status_pendaftaran', ['dibuka', 'seleksi', 'diterima', 'ditugaskan', 'selesai', 'ditolak'])->default('dibuka');
            $table->unsignedBigInteger('id_verifikator')->nullable();
            $table->unsignedBigInteger('id_penyaring')->nullable();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('waktu_daftar')->useCurrent();
            $table->timestamp('waktu_verifikasi')->nullable();
            $table->dateTime('waktu_penyaringan')->nullable();
            $table->timestamp('waktu_penugasan_dimulai')->nullable();
            $table->timestamp('waktu_penugasan_selesai')->nullable();
            $table->timestamp('dihapus_pada')->nullable();
        });

        // Recreate operasi_klaster
        Schema::dropIfExists('operasi_klaster');
        Schema::create('operasi_klaster', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id('id_klaster_operasi');
            $table->uuid('uuid_klaster_operasi')->unique();
            $table->unsignedBigInteger('id_insiden');
            $table->unsignedBigInteger('id_master_klaster');
            $table->string('status_klaster', 50)->default('aktif');
            $table->string('prioritas', 50)->default('sedang')->nullable();
            $table->text('target_cakupan')->nullable();
            $table->text('catatan')->nullable();
            $table->dateTime('waktu_aktivasi')->useCurrent();
            $table->dateTime('waktu_ditutup')->nullable();
            $table->decimal('progres_persen', 5, 2)->default(0)->nullable();
            $table->boolean('dibutuhkan')->default(true)->nullable();
            $table->string('indikator_keberhasilan', 255)->nullable();
            $table->unsignedBigInteger('id_pembuat')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();
            $table->bigInteger('sync_version')->default(1)->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->text('alasan_hapus')->nullable();
        });

        // Recreate operasi_penugasan
        Schema::dropIfExists('operasi_penugasan');
        Schema::create('operasi_penugasan', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id('id_penugasan');
            $table->uuid('uuid_penugasan')->unique();
            $table->unsignedBigInteger('id_insiden');
            $table->unsignedBigInteger('id_klaster_operasi')->nullable();
            $table->unsignedBigInteger('id_pengguna');
            $table->string('peran_otoritas', 100);
            $table->string('status_penugasan', 50)->default('aktif');
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('ditugaskan_oleh');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();
            $table->bigInteger('sync_version')->default(1)->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->text('alasan_hapus')->nullable();
            $table->dateTime('waktu_checkin')->nullable();
            $table->dateTime('waktu_checkout')->nullable();
            $table->string('lokasi_checkin', 255)->nullable();
            $table->string('lokasi_checkout', 255)->nullable();
        });

        // Recreate operasi_tugas
        Schema::dropIfExists('operasi_tugas');
        Schema::create('operasi_tugas', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->bigIncrements('id_tugas');
            $table->unsignedBigInteger('id_operasi_klaster');
            $table->unsignedBigInteger('id_posaju')->nullable();
            $table->unsignedBigInteger('ditugaskan_ke')->nullable();
            $table->unsignedBigInteger('id_surat_perintah')->nullable();
            $table->string('judul_tugas', 255);
            $table->string('target_indikator', 255)->nullable();
            $table->enum('status_tugas', ['rencana','berjalan','tertunda','selesai'])->default('rencana')->nullable();
            $table->decimal('progres_persen', 5, 2)->default(0)->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('dihapus_pada')->nullable();
        });

        // Recreate relawan_penugasan
        Schema::dropIfExists('relawan_penugasan');
        Schema::create('relawan_penugasan', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->bigIncrements('id_penugasan_relawan');
            $table->unsignedBigInteger('id_pendaftaran');
            $table->unsignedBigInteger('id_penugasan_insiden')->nullable();
            $table->unsignedBigInteger('id_posaju')->nullable();
            $table->string('peran_lapangan', 150)->nullable();
            $table->date('tgl_mulai_aktif')->nullable();
            $table->date('tgl_selesai_aktif')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->unsignedBigInteger('id_surat_tugas')->nullable();
            $table->timestamp('dihapus_pada')->nullable();
        });

        // Recreate operasi_penugasan_history
        Schema::dropIfExists('operasi_penugasan_history');
        Schema::create('operasi_penugasan_history', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id('id_history');
            $table->unsignedBigInteger('id_penugasan');
            $table->string('status_sebelumnya', 50)->nullable();
            $table->string('status_baru', 50);
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('diubah_oleh')->nullable();
            $table->timestamp('waktu_perubahan')->useCurrent();
        });

        // Add missing columns from skipped migrations
        if (\Illuminate\Support\Facades\Schema::hasTable('assessment_utama') && !\Illuminate\Support\Facades\Schema::hasColumn('assessment_utama', 'id_petugas_assessment')) {
            \Illuminate\Support\Facades\Schema::table('assessment_utama', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->unsignedBigInteger('id_petugas_assessment')->nullable();
            });
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('operasi_sitrep') && !\Illuminate\Support\Facades\Schema::hasColumn('operasi_sitrep', 'jumlah_personel')) {
            \Illuminate\Support\Facades\Schema::table('operasi_sitrep', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->integer('jumlah_personel')->default(0);
                $table->integer('jumlah_klaster_aktif')->default(0);
            });
        }

        DB::statement('PRAGMA foreign_keys = ON');
    }
}
