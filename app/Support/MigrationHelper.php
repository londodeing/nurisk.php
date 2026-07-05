<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationHelper
{
    public static function hasIndex(string $table, string $index): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $indexes = DB::select('SHOW INDEX FROM ' . $table);
            return in_array($index, array_map(fn ($i) => $i->Key_name, $indexes));
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");
            return in_array($index, array_map(fn ($i) => $i->name, $indexes));
        }

        return false;
    }
}
