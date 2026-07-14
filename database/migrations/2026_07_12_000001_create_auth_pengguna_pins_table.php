<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_pengguna_pins', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pengguna')->primary();
            $table->string('pin_hash', 255);
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_pengguna')
                  ->references('id_pengguna')->on('auth_users')
                  ->cascadeOnDelete();
        });

        Schema::create('auth_pin_attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_pengguna');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('was_successful')->default(false);
            $table->timestamp('attempted_at')->useCurrent();

            $table->foreign('id_pengguna')
                  ->references('id_pengguna')->on('auth_users')
                  ->cascadeOnDelete();

            $table->index(['id_pengguna', 'attempted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_pin_attempts');
        Schema::dropIfExists('auth_pengguna_pins');
    }
};
