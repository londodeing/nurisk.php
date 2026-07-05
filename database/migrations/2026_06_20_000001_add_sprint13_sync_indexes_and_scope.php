<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $index): bool
    {
        return MigrationHelper::hasIndex($table, $index);
    }

    public function up(): void
    {
        // ================================================================
        // 1. sync_audit_logs — add scope columns
        // ================================================================
        if (!Schema::hasColumn('sync_audit_logs', 'scope_type')) {
            Schema::table('sync_audit_logs', function (Blueprint $t) {
                $t->string('scope_type')->nullable()->after('status');
                $t->bigInteger('scope_id')->unsigned()->nullable()->after('scope_type');
            });
        }
        if (!$this->hasIndex('sync_audit_logs', 'idx_sal_scope')) {
            Schema::table('sync_audit_logs', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id'], 'idx_sal_scope'
            ));
        }
        if (!$this->hasIndex('sync_audit_logs', 'idx_sal_request_id')) {
            Schema::table('sync_audit_logs', fn (Blueprint $t) => $t->index(
                'request_id', 'idx_sal_request_id'
            ));
        }

        // ================================================================
        // 2. sync_cursors — ensure composite scope index exists
        // ================================================================
        if (!$this->hasIndex('sync_cursors', 'idx_sync_cursors_scope_type_entity')) {
            Schema::table('sync_cursors', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id', 'entity_type'], 'idx_sync_cursors_scope_type_entity'
            ));
        }
        if (!$this->hasIndex('sync_cursors', 'idx_sync_cursors_scope_cursor')) {
            Schema::table('sync_cursors', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id', 'cursor_value'], 'idx_sync_cursors_scope_cursor'
            ));
        }

        // ================================================================
        // 3. sync_tombstones — ensure composite scope index exists
        // ================================================================
        if (!$this->hasIndex('sync_tombstones', 'idx_sync_tombstones_scope_type_entity')) {
            Schema::table('sync_tombstones', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id', 'entity_type'], 'idx_sync_tombstones_scope_type_entity'
            ));
        }
        if (!$this->hasIndex('sync_tombstones', 'idx_sync_tombstones_scope_cursor')) {
            Schema::table('sync_tombstones', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id', 'cursor_value'], 'idx_sync_tombstones_scope_cursor'
            ));
        }
        if (!$this->hasIndex('sync_tombstones', 'idx_sync_tombstones_entity_cursor')) {
            Schema::table('sync_tombstones', fn (Blueprint $t) => $t->index(
                ['entity_type', 'cursor_value'], 'idx_sync_tombstones_entity_cursor'
            ));
        }

        // ================================================================
        // 4. mobile_sync_queues — ensure composite scope index exists
        // ================================================================
        if (!$this->hasIndex('mobile_sync_queues', 'idx_mq_scope_type_entity')) {
            Schema::table('mobile_sync_queues', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id'], 'idx_mq_scope_type_entity'
            ));
        }
        if (!$this->hasIndex('mobile_sync_queues', 'idx_mq_device_uuid')) {
            Schema::table('mobile_sync_queues', fn (Blueprint $t) => $t->index(
                'device_uuid', 'idx_mq_device_uuid'
            ));
        }

        // ================================================================
        // 5. sync_conflicts — ensure composite scope index exists
        // ================================================================
        if (!$this->hasIndex('sync_conflicts', 'idx_sync_conflicts_scope_type_entity')) {
            Schema::table('sync_conflicts', fn (Blueprint $t) => $t->index(
                ['scope_type', 'scope_id', 'entity_type'], 'idx_sync_conflicts_scope_type_entity'
            ));
        }
    }

    public function down(): void
    {
        $drop = function (string $table, array $indexes): void {
            foreach ($indexes as $index) {
                if ($this->hasIndex($table, $index)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($index));
                }
            }
        };

        $drop('sync_audit_logs', ['idx_sal_scope', 'idx_sal_request_id']);
        $drop('sync_cursors', ['idx_sync_cursors_scope_type_entity', 'idx_sync_cursors_scope_cursor']);
        $drop('sync_tombstones', ['idx_sync_tombstones_scope_type_entity', 'idx_sync_tombstones_scope_cursor', 'idx_sync_tombstones_entity_cursor']);
        $drop('mobile_sync_queues', ['idx_mq_scope_type_entity', 'idx_mq_device_uuid']);
        $drop('sync_conflicts', ['idx_sync_conflicts_scope_type_entity']);

        Schema::table('sync_audit_logs', fn (Blueprint $t) => $t->dropColumn(['scope_type', 'scope_id']));
    }
};
