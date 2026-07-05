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
            DB::statement('PRAGMA foreign_keys = OFF');
            Schema::dropIfExists('operasi_penugasan');
            $this->createTable();
            DB::statement('PRAGMA foreign_keys = ON');
            return;
        }

        Schema::disableForeignKeyConstraints();

        try {
            if (Schema::hasTable('relawan_penugasan')) {
                Schema::table('relawan_penugasan', function (Blueprint $table) {
                    $table->dropForeign('relawan_penugasan_id_penugasan_insiden_foreign');
                });
            }
        } catch (\Throwable) {}

        Schema::dropIfExists('operasi_penugasan');

        $this->createTable();

        if (Schema::hasTable('relawan_penugasan')) {
            Schema::table('relawan_penugasan', function (Blueprint $table) {
                $table->foreign('id_penugasan_insiden')->references('id_penugasan')->on('operasi_penugasan')->nullOnDelete();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    private function createTable(): void
    {
        Schema::create('operasi_penugasan', function (Blueprint $table) {
            $table->id('id_penugasan');
            $table->uuid('uuid_penugasan')->unique();
            $table->unsignedBigInteger('id_insiden');
            $table->unsignedBigInteger('id_pengguna');
            $table->unsignedBigInteger('id_klaster_operasi')->nullable();

            $table->string('peran_otoritas', 100);
            $table->string('status_penugasan', 50)->default('aktif');

            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();

            $table->unsignedBigInteger('ditugaskan_oleh');
            $table->text('catatan')->nullable();

            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();

            $table->foreign('id_insiden')->references('id_insiden')->on('operasi_insiden')->cascadeOnDelete();
            $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
            $table->foreign('id_klaster_operasi')->references('id_klaster_operasi')->on('operasi_klaster')->nullOnDelete();
            $table->foreign('ditugaskan_oleh')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_penugasan');
    }
};
