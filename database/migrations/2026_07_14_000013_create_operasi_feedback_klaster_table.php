<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasi_feedback_klaster', function (Blueprint $table) {
            $table->id('id_feedback_klaster');
            $table->uuid('uuid_feedback_klaster')->unique();

            $table->unsignedBigInteger('id_insiden');
            $table->unsignedBigInteger('id_klaster_operasi');
            $table->unsignedBigInteger('id_pengguna');

            $table->enum('kecukupan', ['kurang', 'cukup', 'berlebih']);
            $table->enum('kualitas', ['baik', 'sedang', 'buruk']);
            $table->boolean('tepat_waktu');
            $table->boolean('tepat_sasaran');
            $table->text('kendala')->nullable();
            $table->text('rekomendasi')->nullable();

            $table->enum('status_feedback', ['draft', 'final'])->default('draft');
            $table->timestamp('dikunci_pada')->nullable();

            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();

            $table->foreign('id_insiden')->references('id_insiden')->on('operasi_insiden')->cascadeOnDelete();
            $table->foreign('id_klaster_operasi')->references('id_klaster_operasi')->on('operasi_klaster')->cascadeOnDelete();
            $table->foreign('id_pengguna')->references('id_pengguna')->on('auth_users')->cascadeOnDelete();

            $table->index('id_insiden');
            $table->index('id_klaster_operasi');
            $table->index('status_feedback');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_feedback_klaster');
    }
};