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
        if (!Schema::hasTable('auth_pengguna_keahlian')) {
            Schema::create('auth_pengguna_keahlian', function (Blueprint $table) {
                $table->unsignedBigInteger('id_pengguna');
                $table->integer('id_keahlian');
                $table->primary(['id_pengguna', 'id_keahlian']);

                $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
                $table->foreign('id_keahlian')->references('id_keahlian')->on('auth_keahlian_master')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_pengguna_keahlian');
    }
};
