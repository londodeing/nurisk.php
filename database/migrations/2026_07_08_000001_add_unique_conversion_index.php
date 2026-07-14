<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dupes = DB::table('media_conversions')
            ->select('media_id', 'conversion_type', DB::raw('MIN(id) as keep_id'))
            ->groupBy('media_id', 'conversion_type')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($dupes as $row) {
            DB::table('media_conversions')
                ->where('media_id', $row->media_id)
                ->where('conversion_type', $row->conversion_type)
                ->where('id', '!=', $row->keep_id)
                ->delete();
        }

        Schema::table('media_conversions', function (Blueprint $table) {
            $table->unique(['media_id', 'conversion_type']);
        });
    }

    public function down(): void
    {
        Schema::table('media_conversions', function (Blueprint $table) {
            $table->dropUnique(['media_id', 'conversion_type']);
        });
    }
};
