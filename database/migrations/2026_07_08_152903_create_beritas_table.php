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
        Schema::create('berita', function (Blueprint $table) {
            $table->id('id_berita');
            $table->string('judul', 255);
            $table->string('slug', 255)->unique();
            $table->text('ringkasan')->nullable();
            $table->longText('konten');
            $table->string('gambar', 255)->nullable();
            $table->string('sumber', 100)->nullable();
            $table->boolean('unggulan')->default(false);
            $table->timestamp('published_at')->nullable();
            
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diubah_pada')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('dihapus_pada')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};
