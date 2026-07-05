<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $existingIndexes = function (string $table): array {
            try {
                $rows = DB::select('SHOW INDEX FROM ' . $table);
                return array_map(fn ($i) => $i->Key_name, $rows);
            } catch (\Throwable) {
                return [];
            }
        };

        $insidenIndexes = $existingIndexes('operasi_insiden');
        if (!in_array('idx_insiden_pcnu_status', $insidenIndexes)) {
            Schema::table('operasi_insiden', function (Blueprint $table) {
                $table->index(['id_pcnu', 'status_insiden'], 'idx_insiden_pcnu_status');
            });
        }

        $usersIndexes = $existingIndexes('auth_users');
        if (!in_array('idx_users_status_akun', $usersIndexes)) {
            Schema::table('auth_users', function (Blueprint $table) {
                $table->index('status_akun', 'idx_users_status_akun');
            });
        }

        if (Schema::hasTable('mobile_devices')) {
            $devicesIndexes = $existingIndexes('mobile_devices');
            if (!in_array('idx_mobile_devices_pengguna', $devicesIndexes)) {
                Schema::table('mobile_devices', function (Blueprint $table) {
                    $table->index('id_pengguna', 'idx_mobile_devices_pengguna');
                });
            }
        }
    }

    public function down(): void
    {
        $dropIndexIfExists = function (string $table, string $index): void {
            try {
                $rows = DB::select('SHOW INDEX FROM ' . $table);
                $indexes = array_map(fn ($i) => $i->Key_name, $rows);
            } catch (\Throwable) {
                $indexes = [];
            }
            if (in_array($index, $indexes)) {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex($index));
            }
        };

        $dropIndexIfExists('operasi_insiden', 'idx_insiden_pcnu_status');
        $dropIndexIfExists('auth_users', 'idx_users_status_akun');
        if (Schema::hasTable('mobile_devices')) {
            $dropIndexIfExists('mobile_devices', 'idx_mobile_devices_pengguna');
        }
    }
};
