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
        if (!Schema::hasTable('organisasi_unit')) {
            Schema::create('organisasi_unit', function (Blueprint $table) {
                $table->integer('id_unit')->autoIncrement();
                $table->integer('parent_id')->nullable();
                $table->string('nama_unit', 150);
                $table->enum('tipe_unit', ['pwnu','pcnu','mwc','ranting','lembaga','banom']);
                $table->char('id_wilayah', 10)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisasi_unit');
    }
};
