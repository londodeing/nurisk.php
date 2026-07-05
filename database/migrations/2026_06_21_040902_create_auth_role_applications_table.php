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
        Schema::create('auth_role_applications', function (Blueprint $table) {
            $table->id('id_application');
            $table->unsignedBigInteger('id_pengguna');
            $table->integer('id_peran_diminta');
            $table->string('status_aplikasi', 50)->default('pending'); // pending, approved, rejected
            $table->timestamp('waktu_pengajuan')->useCurrent();
            $table->timestamp('waktu_diproses')->nullable();
            $table->unsignedBigInteger('id_approver')->nullable();
            $table->text('catatan')->nullable();

            $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
            $table->foreign('id_peran_diminta')->references('id_peran')->on('auth_roles')->cascadeOnDelete();
            $table->foreign('id_approver')->references('id_pengguna')->on('auth_users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_role_applications');
    }
};
