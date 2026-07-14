<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasi_distribusi', function (Blueprint $table) {
            $table->id('id_distribusi');
            $table->uuid('uuid_distribusi')->unique();

            $table->unsignedBigInteger('id_posaju');
            $table->unsignedBigInteger('id_klaster_operasi');
            $table->unsignedBigInteger('id_penugasan')->nullable();
            $table->unsignedInteger('id_barang_katalog')->nullable();

            $table->string('nama_barang', 255);
            $table->decimal('jumlah', 15, 2);
            $table->string('satuan', 50);
            $table->text('lokasi_tujuan')->nullable();
            $table->string('penerima', 255)->nullable();
            $table->dateTime('waktu_distribusi');

            $table->enum('status_distribusi', ['direncanakan', 'didistribusikan', 'diterima', 'direview'])
                ->default('direncanakan');

            $table->unsignedBigInteger('dibuat_oleh')->nullable();

            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();

            $table->foreign('id_posaju')->references('id_posaju')->on('operasi_posaju')->cascadeOnDelete();
            $table->foreign('id_klaster_operasi')->references('id_klaster_operasi')->on('operasi_klaster')->cascadeOnDelete();
            $table->foreign('id_penugasan')->references('id_penugasan')->on('operasi_penugasan')->nullOnDelete();
            $table->foreign('dibuat_oleh')->references('id_pengguna')->on('auth_users')->nullOnDelete();

            $table->index('id_posaju');
            $table->index('id_klaster_operasi');
            $table->index('status_distribusi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_distribusi');
    }
};
