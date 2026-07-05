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
        Schema::table('auth_users', function (Blueprint $table) {
            // Because modifying ENUMs can be problematic across DB drivers,
            // we change it to a string column to support the new lifecycle states:
            // registered, profile_incomplete, pending_verification, active, suspended, archived.
            $table->string('status_akun', 50)->default('registered')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth_users', function (Blueprint $table) {
            $table->string('status_akun', 50)->default('aktif')->change();
        });
    }
};
