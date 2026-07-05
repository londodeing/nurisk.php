<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasIndex = function (string $table, string $index): bool {
            try {
                $rows = DB::select('SHOW INDEX FROM ' . $table);
                return in_array($index, array_map(fn ($i) => $i->Key_name, $rows));
            } catch (\Throwable) {
                return false;
            }
        };

        // --- sync_cursors ---
        Schema::table('sync_cursors', function (Blueprint $t) {
            $t->bigInteger('id_pcnu')->unsigned()->nullable()->after('action');
        });
        try {
            Schema::table('sync_cursors', fn (Blueprint $t) => $t->index(
                ['id_pcnu', 'entity_type', 'cursor_value'], 'idx_sync_cursors_scope'
            ));
        } catch (\Throwable) {}
        try {
            Schema::table('sync_cursors', fn (Blueprint $t) => $t->index(
                ['entity_type', 'cursor_value'], 'idx_sync_cursors_entity_cursor'
            ));
        } catch (\Throwable) {}

        // --- sync_tombstones ---
        Schema::table('sync_tombstones', function (Blueprint $t) {
            $t->bigInteger('id_pcnu')->unsigned()->nullable()->after('cursor_value');
        });
        try {
            Schema::table('sync_tombstones', fn (Blueprint $t) => $t->index(
                ['id_pcnu', 'entity_type', 'cursor_value'], 'idx_sync_tombstones_scope'
            ));
        } catch (\Throwable) {}
        try {
            Schema::table('sync_tombstones', fn (Blueprint $t) => $t->index(
                ['entity_type', 'cursor_value'], 'idx_sync_tombstones_entity_cursor'
            ));
        } catch (\Throwable) {}

        // --- mobile_sync_queues ---
        if (!Schema::hasColumn('mobile_sync_queues', 'id_pcnu')) {
            Schema::table('mobile_sync_queues', function (Blueprint $t) {
                $t->bigInteger('id_pcnu')->unsigned()->nullable()->after('device_uuid');
            });
        }
        try {
            Schema::table('mobile_sync_queues', fn (Blueprint $t) => $t->index('id_pcnu', 'idx_mq_pcnu'));
        } catch (\Throwable) {}
        try {
            Schema::table('mobile_sync_queues', fn (Blueprint $t) => $t->index(
                ['device_uuid', 'id_pcnu'], 'idx_mq_device_pcnu'
            ));
        } catch (\Throwable) {}

        // --- sync_conflicts ---
        if (!Schema::hasColumn('sync_conflicts', 'id_pcnu')) {
            Schema::table('sync_conflicts', function (Blueprint $t) {
                $t->bigInteger('id_pcnu')->unsigned()->nullable()->after('uuid_entity');
            });
        }
        try {
            Schema::table('sync_conflicts', fn (Blueprint $t) => $t->index(
                ['id_pcnu', 'entity_type'], 'idx_sync_conflicts_scope'
            ));
        } catch (\Throwable) {}
    }

    public function down(): void
    {
        $drop = function (string $table, array $indexes): void {
            foreach ($indexes as $index) {
                try {
                    $rows = DB::select('SHOW INDEX FROM ' . $table);
                    $keys = array_map(fn ($i) => $i->Key_name, $rows);
                } catch (\Throwable) {
                    $keys = [];
                }
                if (in_array($index, $keys)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($index));
                }
            }
        };

        try { $drop('sync_cursors', ['idx_sync_cursors_scope', 'idx_sync_cursors_entity_cursor']); } catch (\Throwable) {}
        try { $drop('sync_tombstones', ['idx_sync_tombstones_scope', 'idx_sync_tombstones_entity_cursor']); } catch (\Throwable) {}
        try { $drop('mobile_sync_queues', ['idx_mq_pcnu', 'idx_mq_device_pcnu']); } catch (\Throwable) {}
        try { $drop('sync_conflicts', ['idx_sync_conflicts_scope']); } catch (\Throwable) {}

        try { Schema::table('sync_cursors', fn (Blueprint $t) => $t->dropColumn('id_pcnu')); } catch (\Throwable) {}
        try { Schema::table('sync_tombstones', fn (Blueprint $t) => $t->dropColumn('id_pcnu')); } catch (\Throwable) {}
        try { Schema::table('mobile_sync_queues', fn (Blueprint $t) => $t->dropColumn('id_pcnu')); } catch (\Throwable) {}
        try { Schema::table('sync_conflicts', fn (Blueprint $t) => $t->dropColumn('id_pcnu')); } catch (\Throwable) {}
    }
};
