<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasi_feedback_distribusi', function (Blueprint $table) {
            $table->id('id_feedback');

            $table->unsignedBigInteger('id_distribusi')->unique();
            $table->unsignedBigInteger('id_pengguna');

            $table->enum('kecukupan', ['kurang', 'cukup', 'berlebih']);
            $table->enum('kualitas', ['baik', 'sedang', 'buruk']);
            $table->boolean('tepat_waktu');
            $table->boolean('tepat_sasaran');
            $table->text('kendala')->nullable();
            $table->text('rekomendasi')->nullable();

            $table->enum('status_feedback', ['draft', 'final'])->default('draft');

            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('dikunci_pada')->nullable();

            $table->foreign('id_distribusi')->references('id_distribusi')->on('operasi_distribusi')->cascadeOnDelete();
            $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_feedback_distribusi');
    }
};
