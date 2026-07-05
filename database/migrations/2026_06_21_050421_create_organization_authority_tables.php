<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. organisasi_authority
        Schema::create('organisasi_authority', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 50); // operational, financial, governance, information
            $table->string('code', 100)->unique(); // e.g. activate_incident
            $table->string('description', 255)->nullable();
            $table->boolean('can_delegate')->default(false);
            $table->boolean('need_dual_approval')->default(false);
            $table->timestamps();
        });

        // 2. organisasi_jabatan_authority
        Schema::create('organisasi_jabatan_authority', function (Blueprint $table) {
            $table->id();
            $table->integer('jabatan_master_id');
            $table->unsignedBigInteger('authority_id');
            $table->timestamps();

            $table->foreign('jabatan_master_id')->references('id_jabatan_posisi')->on('master_jabatan')->onDelete('cascade');
            $table->foreign('authority_id')->references('id')->on('organisasi_authority')->onDelete('cascade');
            
            $table->unique(['jabatan_master_id', 'authority_id'], 'jabatan_authority_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisasi_jabatan_authority');
        Schema::dropIfExists('organisasi_authority');
    }
};
