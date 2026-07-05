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
        if (!Schema::hasTable('auth_users')) {
            Schema::create('auth_users', function (Blueprint $table) {
                $table->bigIncrements('id_pengguna');
                $table->integer('id_peran');
                $table->integer('id_unit')->nullable();
                $table->string('no_hp', 20)->nullable();
                $table->string('kata_sandi', 255)->nullable();
                $table->enum('status_akun', ['menunggu','aktif','nonaktif','suspend'])->default('aktif');
                $table->boolean('is_tersedia')->default(true);
                $table->timestamp('terakhir_masuk')->nullable();
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
                $table->enum('default_scope_type', ['pwnu','pcnu','mwc','ranting','lembaga','banom'])->nullable();
                $table->unsignedBigInteger('default_scope_id')->nullable();

                $table->foreign('id_peran')->references('id_peran')->on('auth_roles')->cascadeOnDelete();
                $table->foreign('id_unit')->references('id_unit')->on('organisasi_unit')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_users');
    }
};
