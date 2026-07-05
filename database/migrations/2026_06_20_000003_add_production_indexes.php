<?php

use App\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ================================================================
        // 1. operasi_insiden — ORDER BY dibuat_pada DESC is used everywhere
        // ================================================================
        Schema::table('operasi_insiden', function (Blueprint $t) {
            if (!MigrationHelper::hasIndex('operasi_insiden', 'idx_insiden_dibuat_pada')) {
                $t->index('dibuat_pada', 'idx_insiden_dibuat_pada');
            }
            if (!MigrationHelper::hasIndex('operasi_insiden', 'idx_insiden_pcnu_dibuat')) {
                $t->index(['id_pcnu', 'dibuat_pada'], 'idx_insiden_pcnu_dibuat');
            }
            if (!MigrationHelper::hasIndex('operasi_insiden', 'idx_insiden_status_dibuat')) {
                $t->index(['status_insiden', 'dibuat_pada'], 'idx_insiden_status_dibuat');
            }
            if (!MigrationHelper::hasIndex('operasi_insiden', 'idx_insiden_prioritas_dibuat')) {
                $t->index(['prioritas', 'dibuat_pada'], 'idx_insiden_prioritas_dibuat');
            }
        });

        // ================================================================
        // 2. assessment_utama — list by id_insiden with sort/filter
        // ================================================================
        Schema::table('assessment_utama', function (Blueprint $t) {
            if (!MigrationHelper::hasIndex('assessment_utama', 'idx_assessment_insiden_waktu')) {
                $t->index(['id_insiden', 'waktu_assesment'], 'idx_assessment_insiden_waktu');
            }
            if (!MigrationHelper::hasIndex('assessment_utama', 'idx_assessment_insiden_diperbarui')) {
                $t->index(['id_insiden', 'diperbarui_pada'], 'idx_assessment_insiden_diperbarui');
            }
        });

        // ================================================================
        // 3. operasi_pleno — list by id_insiden ORDER BY dibuat_pada
        // ================================================================
        Schema::table('operasi_pleno', function (Blueprint $t) {
            if (!MigrationHelper::hasIndex('operasi_pleno', 'idx_pleno_insiden_dibuat')) {
                $t->index(['id_insiden', 'dibuat_pada'], 'idx_pleno_insiden_dibuat');
            }
        });

        // ================================================================
        // 4. operasi_surat_keluar — listing with sort/filter
        // ================================================================
        Schema::table('operasi_surat_keluar', function (Blueprint $t) {
            if (!MigrationHelper::hasIndex('operasi_surat_keluar', 'idx_surat_dibuat_pada')) {
                $t->index('dibuat_pada', 'idx_surat_dibuat_pada');
            }
            if (!MigrationHelper::hasIndex('operasi_surat_keluar', 'idx_surat_status_dibuat')) {
                $t->index(['status_surat', 'dibuat_pada'], 'idx_surat_status_dibuat');
            }
        });

        // ================================================================
        // 5. sync_audit_logs — lastSync query and scoped metrics
        // ================================================================
        Schema::table('sync_audit_logs', function (Blueprint $t) {
            if (!MigrationHelper::hasIndex('sync_audit_logs', 'idx_sal_dibuat_pada')) {
                $t->index('dibuat_pada', 'idx_sal_dibuat_pada');
            }
            if (!MigrationHelper::hasIndex('sync_audit_logs', 'idx_sal_scope_dibuat')) {
                $t->index(['scope_type', 'scope_id', 'dibuat_pada'], 'idx_sal_scope_dibuat');
            }
        });
    }

    public function down(): void
    {
        $drop = function (string $table, array $indexes): void {
            foreach ($indexes as $index) {
                if (MigrationHelper::hasIndex($table, $index)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($index));
                }
            }
        };

        $drop('operasi_insiden', [
            'idx_insiden_dibuat_pada',
            'idx_insiden_pcnu_dibuat',
            'idx_insiden_status_dibuat',
            'idx_insiden_prioritas_dibuat',
        ]);

        $drop('assessment_utama', [
            'idx_assessment_insiden_waktu',
            'idx_assessment_insiden_diperbarui',
        ]);

        $drop('operasi_pleno', [
            'idx_pleno_insiden_dibuat',
        ]);

        $drop('operasi_surat_keluar', [
            'idx_surat_dibuat_pada',
            'idx_surat_status_dibuat',
        ]);

        $drop('sync_audit_logs', [
            'idx_sal_dibuat_pada',
            'idx_sal_scope_dibuat',
        ]);
    }
};
