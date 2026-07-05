<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logistik_barang_katalog', function (Blueprint $table) {
            $table->integer('id_katalog')->autoIncrement();
            $table->integer('id_kategori');
            $table->integer('id_satuan');
            $table->string('nama_barang_standar', 150);
        });
    }

    public function down()
    {
        Schema::dropIfExists('logistik_barang_katalog');
    }
};
