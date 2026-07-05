<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('dokumen_surat_paraf')) {
            Schema::table('dokumen_surat_paraf', function (Blueprint $table) {
                $table->string('approval_user_status', 50)->nullable()->after('waktu_paraf');
                $table->string('approval_role_snapshot', 100)->nullable()->after('approval_user_status');
            });
        }

        if (Schema::hasTable('operasi_pleno_peserta')) {
            Schema::table('operasi_pleno_peserta', function (Blueprint $table) {
                $table->string('approval_user_status', 50)->nullable()->after('catatan_peserta');
                $table->string('approval_role_snapshot', 100)->nullable()->after('approval_user_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dokumen_surat_paraf')) {
            Schema::table('dokumen_surat_paraf', function (Blueprint $table) {
                $table->dropColumn(['approval_user_status', 'approval_role_snapshot']);
            });
        }

        if (Schema::hasTable('operasi_pleno_peserta')) {
            Schema::table('operasi_pleno_peserta', function (Blueprint $table) {
                $table->dropColumn(['approval_user_status', 'approval_role_snapshot']);
            });
        }
    }
};
