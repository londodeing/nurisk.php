<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_utama', function (Blueprint $table) {
            $table->enum('status_review', ['draft', 'submitted', 'in_review', 'approved', 'rejected'])
                ->default('draft')
                ->after('is_latest');
            $table->text('catatan_review')->nullable()->after('status_review');
            $table->unsignedBigInteger('id_reviewer')->nullable()->after('catatan_review');
            $table->timestamp('waktu_review')->nullable()->after('id_reviewer');

            $table->foreign('id_reviewer')->references('id_pengguna')->on('auth_users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_utama', function (Blueprint $table) {
            $table->dropForeign(['id_reviewer']);
            $table->dropColumn(['status_review', 'catatan_review', 'id_reviewer', 'waktu_review']);
        });
    }
};
