<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logistik_gudang', function (Blueprint $table) {
            $table->id('id_gudang');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('nama_gudang', 150);
            $table->integer('id_pcnu')->nullable();
            $table->text('alamat_fisik');
            $table->decimal('kapasitas_kubikasi', 10, 2)->default(0.00);
            $table->unsignedBigInteger('pj_gudang');
            $table->boolean('status_aktif')->default(1);
        });
    }

    public function down()
    {
        Schema::dropIfExists('logistik_gudang');
    }
};
