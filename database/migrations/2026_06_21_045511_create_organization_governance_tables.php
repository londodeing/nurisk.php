<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. organisasi_jabatan (Pivot Unit - Jabatan Master)
        Schema::create('organisasi_jabatan', function (Blueprint $table) {
            $table->id();
            $table->integer('organisasi_id'); // mengacu ke organisasi_unit.id_unit
            $table->integer('jabatan_master_id'); // mengacu ke master_jabatan.id_jabatan_posisi
            $table->timestamps();

            $table->foreign('organisasi_id')->references('id_unit')->on('organisasi_unit')->onDelete('cascade');
            $table->foreign('jabatan_master_id')->references('id_jabatan_posisi')->on('master_jabatan')->onDelete('cascade');
        });

        // 2. organisasi_sk
        Schema::create('organisasi_sk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sk', 100)->unique();
            $table->date('tanggal_terbit')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir')->nullable();
            $table->string('dokumen_file')->nullable();
            $table->string('status', 50)->default('draft'); // draft, active, expired, revoked
            $table->timestamps();
        });

        // 3. organisasi_sk_pengurus
        Schema::create('organisasi_sk_pengurus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sk_id');
            $table->unsignedBigInteger('auth_user_id');
            $table->unsignedBigInteger('jabatan_id');
            $table->timestamps();

            $table->foreign('sk_id')->references('id')->on('organisasi_sk')->onDelete('cascade');
            $table->foreign('auth_user_id')->references('id_pengguna')->on('auth_users')->onDelete('cascade');
            $table->foreign('jabatan_id')->references('id')->on('organisasi_jabatan')->onDelete('cascade');
        });

        // 4. organisasi_mandat
        Schema::create('organisasi_mandat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sk_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('jabatan_id');
            $table->integer('organisasi_id');
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir')->nullable();
            $table->string('status', 50)->default('active'); // active, expired, revoked, suspended
            $table->timestamps();

            $table->foreign('sk_id')->references('id')->on('organisasi_sk')->onDelete('set null');
            $table->foreign('user_id')->references('id_pengguna')->on('auth_users')->onDelete('cascade');
            $table->foreign('jabatan_id')->references('id')->on('organisasi_jabatan')->onDelete('cascade');
            $table->foreign('organisasi_id')->references('id_unit')->on('organisasi_unit')->onDelete('cascade');
        });

        // 5. organisasi_delegasi
        Schema::create('organisasi_delegasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mandat_asal_id');
            $table->unsignedBigInteger('mandat_pengganti_id');
            $table->dateTime('mulai');
            $table->dateTime('selesai');
            $table->text('alasan')->nullable();
            $table->timestamps();

            $table->foreign('mandat_asal_id')->references('id')->on('organisasi_mandat')->onDelete('cascade');
            $table->foreign('mandat_pengganti_id')->references('id')->on('organisasi_mandat')->onDelete('cascade');
        });

        // 6. organisasi_audit_log
        Schema::create('organisasi_audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id');
            $table->string('jabatan_snapshot');
            $table->string('sk_snapshot')->nullable();
            $table->unsignedBigInteger('mandat_id')->nullable();
            $table->unsignedBigInteger('delegasi_id')->nullable();
            $table->string('aksi');
            $table->string('target_table')->nullable();
            $table->string('target_id')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();

            $table->foreign('actor_id')->references('id_pengguna')->on('auth_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisasi_audit_log');
        Schema::dropIfExists('organisasi_delegasi');
        Schema::dropIfExists('organisasi_mandat');
        Schema::dropIfExists('organisasi_sk_pengurus');
        Schema::dropIfExists('organisasi_sk');
        Schema::dropIfExists('organisasi_jabatan');
    }
};
