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
        if ($driver !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        // Add missing columns to match the schema that recreation migrations expect
        $addIfMissing = function (string $table, string $column, string $type, ?string $default = null): void {
            if (!Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $t) use ($table, $column, $type, $default) {
                    if ($type === 'bigInteger') {
                        $t->bigInteger($column)->nullable()->default($default);
                    } elseif ($type === 'uuid') {
                        $t->uuid($column)->nullable();
                    } elseif ($type === 'text') {
                        $t->text($column)->nullable();
                    } elseif ($type === 'dateTime') {
                        $t->dateTime($column)->nullable();
                    } elseif ($type === 'decimal') {
                        $t->decimal($column, 5, 2)->default($default ?? 0)->nullable();
                    } elseif ($type === 'boolean') {
                        $t->boolean($column)->default($default ?? true)->nullable();
                    } elseif ($type === 'string') {
                        $t->string($column, 255)->nullable()->default($default);
                    } elseif ($type === 'bigIncrements') {
                        // skip - primary key
                    } elseif ($type === 'tinyInteger') {
                        $t->tinyInteger($column)->default($default ?? 0)->nullable();
                    }else {
                        $t->string($column, 255)->nullable();
                    }
                });
            }
        };

        // operasi_klaster — recreation columns
        if (Schema::hasTable('operasi_klaster')) {
            $addIfMissing('operasi_klaster', 'waktu_aktivasi', 'dateTime');
            $addIfMissing('operasi_klaster', 'waktu_ditutup', 'dateTime');
            $addIfMissing('operasi_klaster', 'progres_persen', 'decimal', 0);
            $addIfMissing('operasi_klaster', 'dibutuhkan', 'boolean', true);
            $addIfMissing('operasi_klaster', 'indikator_keberhasilan', 'string');
            $addIfMissing('operasi_klaster', 'id_pembuat', 'bigInteger');
            $addIfMissing('operasi_klaster', 'sync_version', 'bigInteger', 1);
            $addIfMissing('operasi_klaster', 'deleted_by', 'bigInteger');
            $addIfMissing('operasi_klaster', 'alasan_hapus', 'text');
            $addIfMissing('operasi_klaster', 'catatan', 'text');
            // Recreation migration renamed id_klaster → id_master_klaster
            if (!Schema::hasColumn('operasi_klaster', 'id_master_klaster') && Schema::hasColumn('operasi_klaster', 'id_klaster')) {
                $addIfMissing('operasi_klaster', 'id_master_klaster', 'bigInteger');
            }
            if (!Schema::hasColumn('operasi_klaster', 'uuid_klaster_operasi')) {
                $addIfMissing('operasi_klaster', 'uuid_klaster_operasi', 'uuid');
            }
            // Original migration has no diperbarui_pada; recreation migration adds it
            if (!Schema::hasColumn('operasi_klaster', 'diperbarui_pada')) {
                $addIfMissing('operasi_klaster', 'diperbarui_pada', 'dateTime');
            }
            // Original migration has no dibuat_pada; recreation migration adds it
            if (!Schema::hasColumn('operasi_klaster', 'dibuat_pada')) {
                $addIfMissing('operasi_klaster', 'dibuat_pada', 'dateTime');
            }
        }

        // operasi_klaster — recreation columns
        if (Schema::hasTable('operasi_klaster')) {
            // Original migration 000017 uses id_operasi_klaster as PK;
            // recreate migration 100002 renames to id_klaster_operasi.
            if (Schema::hasColumn('operasi_klaster', 'id_operasi_klaster') && !Schema::hasColumn('operasi_klaster', 'id_klaster_operasi')) {
                DB::statement('ALTER TABLE operasi_klaster RENAME COLUMN id_operasi_klaster TO id_klaster_operasi');
            }
            $addIfMissing('operasi_klaster', 'dihapus_pada', 'dateTime');
        }

        // operasi_penugasan — recreation columns
        if (Schema::hasTable('operasi_penugasan')) {
            // Original migration 000018 uses id_incident_assignment as PK;
            // recreate migration 100003 renames to id_penugasan.
            if (Schema::hasColumn('operasi_penugasan', 'id_incident_assignment') && !Schema::hasColumn('operasi_penugasan', 'id_penugasan')) {
                DB::statement('ALTER TABLE operasi_penugasan RENAME COLUMN id_incident_assignment TO id_penugasan');
            }
            $addIfMissing('operasi_penugasan', 'uuid_penugasan', 'uuid');
            $addIfMissing('operasi_penugasan', 'id_klaster_operasi', 'bigInteger');
            $addIfMissing('operasi_penugasan', 'sync_version', 'bigInteger', 1);
            $addIfMissing('operasi_penugasan', 'deleted_by', 'bigInteger');
            $addIfMissing('operasi_penugasan', 'alasan_hapus', 'text');
        }

        // assessment_utama — sync_version columns
        if (Schema::hasTable('assessment_utama')) {
            $addIfMissing('assessment_utama', 'sync_version', 'bigInteger', 1);
            $addIfMissing('assessment_utama', 'deleted_by', 'bigInteger');
            $addIfMissing('assessment_utama', 'alasan_hapus', 'text');
        }

        // operasi_sitrep — sync_version columns
        if (Schema::hasTable('operasi_sitrep')) {
            $addIfMissing('operasi_sitrep', 'sync_version', 'bigInteger', 1);
            $addIfMissing('operasi_sitrep', 'deleted_by', 'bigInteger');
            $addIfMissing('operasi_sitrep', 'alasan_hapus', 'text');
        }

        // operasi_mobilisasi — sync_version columns
        if (Schema::hasTable('operasi_mobilisasi')) {
            $addIfMissing('operasi_mobilisasi', 'sync_version', 'bigInteger', 1);
            $addIfMissing('operasi_mobilisasi', 'deleted_by', 'bigInteger');
            $addIfMissing('operasi_mobilisasi', 'alasan_hapus', 'text');
        }

        // sync tables — id_pcnu and scope columns
        $syncTables = ['sync_cursors', 'sync_tombstones', 'mobile_sync_queues', 'sync_conflicts'];
        foreach ($syncTables as $table) {
            $addIfMissing($table, 'id_pcnu', 'bigInteger');
            $addIfMissing($table, 'scope_type', 'string');
            $addIfMissing($table, 'scope_id', 'bigInteger');
        }

        // sync_audit_logs — scope columns
        $addIfMissing('sync_audit_logs', 'scope_type', 'string');
        $addIfMissing('sync_audit_logs', 'scope_id', 'bigInteger');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // no-op
    }
};
