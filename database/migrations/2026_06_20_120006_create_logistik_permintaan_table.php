<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logistik_permintaan', function (Blueprint $table) {
            $table->id('id_permintaan');
            $table->unsignedBigInteger('id_operasi_klaster')->nullable();
            $table->unsignedBigInteger('id_penugasan');
            $table->unsignedBigInteger('id_posaju_tujuan');
            $table->enum('prioritas', ['biasa', 'mendesak', 'darurat'])->default('biasa');
            $table->enum('status_permintaan', ['draft', 'diajukan', 'disetujui', 'ditolak', 'dikirim', 'selesai'])->default('diajukan');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('dihapus_pada')->nullable();
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::unprepared("
                CREATE TRIGGER `tr_validate_logistik_request_scope` BEFORE INSERT ON `logistik_permintaan` FOR EACH ROW BEGIN
                DECLARE v_incident_from_task BIGINT(20);
                DECLARE v_incident_from_posaju BIGINT(20);

                -- A. Ambil ID Insiden dari klaster penugasan tugas yang bersangkutan
                SELECT `ok`.`id_insiden` INTO v_incident_from_task
                FROM `operasi_tugas` `ot`
                JOIN `operasi_klaster` `ok` ON `ot`.`id_operasi_klaster` = `ok`.`id_operasi_klaster`
                WHERE `ot`.`id_tugas` = NEW.id_penugasan;

                -- B. Ambil ID Insiden dari Pos Aju tujuan
                SELECT `id_insiden` INTO v_incident_from_posaju
                FROM `operasi_posaju`
                WHERE `id_posaju` = NEW.id_posaju_tujuan;

                -- C. Validasi: Pos Aju tujuan harus berada di bawah koordinasi insiden yang sama dengan tugas terkait
                IF v_incident_from_task <> v_incident_from_posaju THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'Pelanggaran Integritas: Posaju tujuan tidak sesuai dengan Insiden penugasan ini.';
                END IF;
            END
            ");
        }
    }

    public function down()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::unprepared("DROP TRIGGER IF EXISTS `tr_validate_logistik_request_scope`");
        }
        Schema::dropIfExists('logistik_permintaan');
    }
};
