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
        Schema::table('auth_pengguna_profil', function (Blueprint $table) {
            $table->string('tempat_lahir', 150)->nullable();
            $table->string('profesi', 150)->nullable();
            $table->text('pengalaman_kebencanaan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth_pengguna_profil', function (Blueprint $table) {
            $table->dropColumn(['tempat_lahir', 'profesi', 'pengalaman_kebencanaan']);
        });
    }
};
