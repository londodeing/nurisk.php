<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'assessment_utama',
        'operasi_sitrep',
        'operasi_klaster',
        'operasi_penugasan',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // If column doesn't exist, add it
                if (!Schema::hasColumn($tableName, 'sync_version')) {
                    $table->bigInteger('sync_version')->default(1);
                }
                if (!Schema::hasColumn($tableName, 'deleted_by')) {
                    $table->unsignedBigInteger('deleted_by')->nullable();
                    $table->text('alasan_hapus')->nullable();

                    $table->foreign('deleted_by', "fk_{$tableName}_deleted_by")
                        ->references('id_pengguna')
                        ->on('auth_users')
                        ->onDelete('set null');
                }
            });
        }

        // Recreate triggers dropped by SQLite table recreation
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS tr_single_latest_assessment');
            DB::unprepared('
                CREATE TRIGGER tr_single_latest_assessment
                BEFORE INSERT ON assessment_utama
                FOR EACH ROW
                WHEN NEW.is_latest = 1
                BEGIN
                    UPDATE assessment_utama 
                    SET is_latest = 0 
                    WHERE id_insiden = NEW.id_insiden AND is_latest = 1;
                END;
            ');
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'sync_version')) {
                    $table->dropColumn('sync_version');
                }
                if (Schema::hasColumn($tableName, 'deleted_by')) {
                    try { $table->dropForeign("fk_{$tableName}_deleted_by"); } catch (\Throwable) {}
                    $table->dropColumn(['deleted_by', 'alasan_hapus']);
                }
            });
        }
    }
};
