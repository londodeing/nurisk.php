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
        Schema::create('operasi_jurnal', function (Blueprint $table) {
            $table->bigIncrements('id_jurnal');
            $table->unsignedBigInteger('id_insiden');
            $table->unsignedBigInteger('id_pengguna')->nullable();
            $table->string('kategori_event', 50);
            $table->string('judul_event', 255);
            $table->text('deskripsi_event')->nullable();
            $table->unsignedBigInteger('id_referensi')->nullable();
            $table->string('tabel_referensi', 100)->nullable();
            $table->timestamp('dibuat_pada')->nullable();

            $table->index('id_insiden', 'idx_jurnal_insiden');
            $table->index('kategori_event', 'idx_jurnal_kategori');
            $table->index('dibuat_pada', 'idx_jurnal_waktu');

            $table->foreign('id_insiden', 'fk_jurnal_insiden')
                ->references('id_insiden')
                ->on('operasi_insiden')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operasi_jurnal');
    }
};
