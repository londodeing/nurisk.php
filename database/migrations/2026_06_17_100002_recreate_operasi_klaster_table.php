<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            if (!Schema::hasTable('operasi_klaster')) {
                $this->createTable();
            }
            return;
        }

        Schema::disableForeignKeyConstraints();

        try {
            if (Schema::hasTable('operasi_tugas')) {
                Schema::table('operasi_tugas', function (Blueprint $table) {
                    $table->dropForeign('operasi_tugas_id_operasi_klaster_foreign');
                });
            }
        } catch (\Throwable) {}
        try {
            if (Schema::hasTable('relawan_kebutuhan')) {
                Schema::table('relawan_kebutuhan', function (Blueprint $table) {
                    $table->dropForeign('relawan_kebutuhan_id_operasi_klaster_foreign');
                });
            }
        } catch (\Throwable) {}

        Schema::dropIfExists('operasi_klaster');

        $this->createTable();

        if (Schema::hasTable('operasi_tugas')) {
            Schema::table('operasi_tugas', function (Blueprint $table) {
                $table->foreign('id_operasi_klaster')->references('id_klaster_operasi')->on('operasi_klaster')->cascadeOnDelete();
            });
        }
        if (Schema::hasTable('relawan_kebutuhan')) {
            Schema::table('relawan_kebutuhan', function (Blueprint $table) {
                $table->foreign('id_operasi_klaster')->references('id_klaster_operasi')->on('operasi_klaster')->nullOnDelete();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    private function createTable(): void
    {
        Schema::create('operasi_klaster', function (Blueprint $table) {
            $table->id('id_klaster_operasi');
            $table->uuid('uuid_klaster_operasi')->unique();
            $table->unsignedBigInteger('id_insiden');
            $table->unsignedBigInteger('id_master_klaster');
            $table->string('status_klaster', 50)->default('aktif');
            $table->string('prioritas', 50)->default('sedang');
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

            $table->foreign('id_insiden')->references('id_insiden')->on('operasi_insiden')->cascadeOnDelete();
            $table->foreign('id_master_klaster')->references('id_master_klaster')->on('master_klaster')->restrictOnDelete();
            $table->foreign('id_pembuat')->references('id_pengguna')->on('auth_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_klaster');
    }
};
