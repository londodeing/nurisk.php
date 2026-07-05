<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logistik_stok', function (Blueprint $table) {
            $table->id('id_stok');
            $table->unsignedBigInteger('id_posaju');
            $table->unsignedBigInteger('id_gudang')->nullable();
            $table->integer('id_katalog')->nullable();
            $table->decimal('jumlah_tersedia', 15, 2)->default(0.00);
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::unprepared("
                CREATE TRIGGER `tr_validate_stock_ownership` BEFORE INSERT ON `logistik_stok` FOR EACH ROW BEGIN
                    DECLARE v_pcnu_gudang INT;
                    DECLARE v_pcnu_incident INT;

                SELECT `id_pcnu` INTO v_pcnu_gudang FROM `logistik_gudang` WHERE `id_gudang` = NEW.id_gudang;
                
                SELECT `i`.`id_pcnu` INTO v_pcnu_incident
                FROM `operasi_posaju` op
                JOIN `operasi_insiden` i ON op.id_insiden = i.id_insiden
                WHERE op.id_posaju = NEW.id_posaju;

                IF v_pcnu_gudang IS NOT NULL AND v_pcnu_gudang <> v_pcnu_incident THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'Pelanggaran Otoritas: Gudang ini milik PCNU lain dan tidak diizinkan mensuplai insiden ini.';
                END IF;
            END
            ");
        }
    }

    public function down()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::unprepared("DROP TRIGGER IF EXISTS `tr_validate_stock_ownership`");
        }
        Schema::dropIfExists('logistik_stok');
    }
};
