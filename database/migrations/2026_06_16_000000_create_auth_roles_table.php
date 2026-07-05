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
        if (!Schema::hasTable('auth_roles')) {
            Schema::create('auth_roles', function (Blueprint $table) {
                $table->integer('id_peran')->autoIncrement();
                $table->string('nama_peran', 50);
                $table->text('deskripsi')->nullable();
                $table->integer('level_otoritas');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_roles');
    }
};
