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
        if (!Schema::hasTable('bencana_master_jenis')) {
            Schema::create('bencana_master_jenis', function (Blueprint $table) {
                $table->integer('id_jenis')->autoIncrement();
                $table->string('nama_bencana', 100)->unique();
                $table->string('slug', 100)->unique();
                $table->enum('kategori', ['alam', 'non_alam', 'sosial'])->default('alam');
                $table->text('deskripsi')->nullable();
                $table->string('ikon_map', 255)->default('default.png');
                $table->timestamp('dibuat_pada')->useCurrent();
                $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bencana_master_jenis');
    }
};
