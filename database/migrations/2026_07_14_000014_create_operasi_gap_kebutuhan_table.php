<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasi_gap_kebutuhan', function (Blueprint $table) {
            $table->id('id_gap_kebutuhan');
            $table->uuid('uuid_gap_kebutuhan')->unique();

            $table->unsignedBigInteger('id_insiden');
            $table->unsignedBigInteger('id_klaster_operasi');
            $table->unsignedBigInteger('id_feedback_klaster')->nullable();
            $table->unsignedBigInteger('id_penugasan')->nullable();

            $table->string('jenis_gap', 100); // stok, personel, logistik, infrastructure
            $table->text('deskripsi_gap');
            $table->decimal('selisih_jumlah', 15, 2);
            $table->string('satuan', 50);
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi', 'kritis'])->default('sedang');
            $table->enum('status_gap', ['dibuka', 'diprioritaskan', 'dikerjakan', 'terselesaikan', 'ditutup'])->default('dibuka');

            $table->unsignedBigInteger('id_penugasan')->nullable();
            $table->text('catatan_penanganan')->nullable();
            $table->dateTime('waktu_terselesaikan')->nullable();

            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();

            $table->foreign('id_insiden')->references('id_insiden')->on('operasi_insiden')->cascadeOnDelete();
            $table->foreign('id_klaster_operasi')->references('id_klaster_operasi')->on('operasi_klaster')->cascadeOnDelete();
            $table->foreign('id_feedback_klaster')->references('id_feedback_klaster')->on('operasi_feedback_klaster')->nullOnDelete();
            $table->foreign('id_penugasan')->references('id_penugasan')->on('operasi_penugasan')->nullOnDelete();

            $table->index('id_insiden');
            $table->index('id_klaster_operasi');
            $table->index('status_gap');
            $table->index('prioritas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_gap_kebutuhan');
    }
};