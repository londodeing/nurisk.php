<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logistik_mutasi', function (Blueprint $table) {
            $table->id('id_mutasi');
            $table->char('uuid_mutasi', 36);
            $table->unsignedBigInteger('id_penginput')->nullable();
            $table->unsignedBigInteger('id_stok');
            $table->enum('tipe_mutasi', ['masuk', 'keluar', 'penyesuaian']);
            $table->decimal('jumlah', 15, 2);
            $table->string('asal_tujuan', 255);
            $table->text('keterangan')->nullable();
            $table->timestamp('waktu_mutasi')->useCurrent();
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::unprepared("
                CREATE TRIGGER `tr_execute_logistik_stok_update` AFTER INSERT ON `logistik_mutasi` FOR EACH ROW BEGIN
                IF NEW.tipe_mutasi = 'masuk' THEN
                    UPDATE `logistik_stok` SET `jumlah_tersedia` = `jumlah_tersedia` + NEW.jumlah 
                    WHERE `id_stok` = NEW.id_stok;
                ELSEIF NEW.tipe_mutasi = 'keluar' THEN
                    UPDATE `logistik_stok` SET `jumlah_tersedia` = `jumlah_tersedia` - NEW.jumlah 
                    WHERE `id_stok` = NEW.id_stok;
                ELSEIF NEW.tipe_mutasi = 'penyesuaian' THEN
                    UPDATE `logistik_stok` SET `jumlah_tersedia` = NEW.jumlah 
                    WHERE `id_stok` = NEW.id_stok;
                END IF;
            END
            ");

            DB::unprepared("
                CREATE TRIGGER `tr_logistik_mutasi_integrity_guard` BEFORE INSERT ON `logistik_mutasi` FOR EACH ROW BEGIN
                DECLARE v_stok_tersedia DECIMAL(15,2);
                SELECT `jumlah_tersedia` INTO v_stok_tersedia FROM `logistik_stok` WHERE `id_stok` = NEW.id_stok;
                
                IF NEW.tipe_mutasi = 'keluar' AND v_stok_tersedia < NEW.jumlah THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Integrity Error: Stok fisik tidak mencukupi!';
                END IF;
            END
            ");
        }
    }

    public function down()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::unprepared("DROP TRIGGER IF EXISTS `tr_execute_logistik_stok_update`");
            DB::unprepared("DROP TRIGGER IF EXISTS `tr_logistik_mutasi_integrity_guard`");
        }
        Schema::dropIfExists('logistik_mutasi');
    }
};
