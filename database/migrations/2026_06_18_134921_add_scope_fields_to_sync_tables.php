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
        if (!Schema::hasColumn('sync_cursors', 'scope_type')) {
            Schema::table('sync_cursors', function (Blueprint $t) {
                $t->string('scope_type')->nullable()->after('action');
                $t->bigInteger('scope_id')->unsigned()->nullable()->after('scope_type');
            });
        }
        try {
            Schema::table('sync_cursors', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id', 'entity_type', 'cursor_value'], 'idx_sync_cursors_scope'
            ));
        } catch (\Throwable) {}
        try {
            Schema::table('sync_cursors', fn (Blueprint $t) => $t->index(
                ['entity_type', 'cursor_value'], 'idx_sync_cursors_entity_cursor'
            ));
        } catch (\Throwable) {}

        // --- sync_tombstones ---
        if (!Schema::hasColumn('sync_tombstones', 'scope_type')) {
            Schema::table('sync_tombstones', function (Blueprint $t) {
                $t->string('scope_type')->nullable()->after('id_pcnu');
                $t->bigInteger('scope_id')->unsigned()->nullable()->after('scope_type');
            });
        }
        try {
            Schema::table('sync_tombstones', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id', 'entity_type', 'cursor_value'], 'idx_sync_tombstones_scope'
            ));
        } catch (\Throwable) {}
        try {
            Schema::table('sync_tombstones', fn (Blueprint $t) => $t->index(
                ['entity_type', 'cursor_value'], 'idx_sync_tombstones_entity_cursor'
            ));
        } catch (\Throwable) {}

        // --- mobile_sync_queues ---
        if (!Schema::hasColumn('mobile_sync_queues', 'scope_type')) {
            Schema::table('mobile_sync_queues', function (Blueprint $t) {
                $t->string('scope_type')->nullable()->after('id_pcnu');
                $t->bigInteger('scope_id')->unsigned()->nullable()->after('scope_type');
            });
        }
        try {
            Schema::table('mobile_sync_queues', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id', 'device_uuid'], 'idx_mq_scope'
            ));
        } catch (\Throwable) {}

        // --- sync_conflicts ---
        if (!Schema::hasColumn('sync_conflicts', 'scope_type')) {
            Schema::table('sync_conflicts', function (Blueprint $t) {
                $t->string('scope_type')->nullable()->after('id_pcnu');
                $t->bigInteger('scope_id')->unsigned()->nullable()->after('scope_type');
            });
        }
        try {
            Schema::table('sync_conflicts', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id', 'entity_type'], 'idx_sync_conflicts_scope'
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

        $drop('sync_cursors', ['idx_sync_cursors_scope', 'idx_sync_cursors_entity_cursor']);
        $drop('sync_tombstones', ['idx_sync_tombstones_scope', 'idx_sync_tombstones_entity_cursor']);
        $drop('mobile_sync_queues', ['idx_mq_scope']);
        $drop('sync_conflicts', ['idx_sync_conflicts_scope']);

        Schema::table('sync_cursors', fn (Blueprint $t) => $t->dropColumn(['scope_type', 'scope_id']));
        Schema::table('sync_tombstones', fn (Blueprint $t) => $t->dropColumn(['scope_type', 'scope_id']));
        Schema::table('mobile_sync_queues', fn (Blueprint $t) => $t->dropColumn(['scope_type', 'scope_id']));
        Schema::table('sync_conflicts', fn (Blueprint $t) => $t->dropColumn(['scope_type', 'scope_id']));
    }
};