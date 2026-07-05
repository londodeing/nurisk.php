<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_dampak_lingkungan', function (Blueprint $t) {
            if (!Schema::hasColumn('assessment_dampak_lingkungan', 'ternak_terdampak_ekor')) {
                $t->integer('ternak_terdampak_ekor')->default(0);
            }
        });

        $vitalRecords = DB::table('assessment_dampak_vital')
            ->where(function ($q) {
                $q->where('sawah_ha', '>', 0)
                  ->orWhere('hutan_ha', '>', 0)
                  ->orWhere('ternak_ekor', '>', 0);
            })
            ->get();

        foreach ($vitalRecords as $vital) {
            $existing = DB::table('assessment_dampak_lingkungan')
                ->where('id_assessment', $vital->id_assessment)
                ->first();

            if ($existing) {
                DB::table('assessment_dampak_lingkungan')
                    ->where('id_assessment', $vital->id_assessment)
                    ->update([
                        'lahan_pertanian_rusak_ha' => max((float)$existing->lahan_pertanian_rusak_ha, (float)$vital->sawah_ha),
                        'hutan_terdampak_ha'       => max((float)$existing->hutan_terdampak_ha, (float)$vital->hutan_ha),
                        'ternak_terdampak_ekor'    => max((int)$existing->ternak_terdampak_ekor, (int)$vital->ternak_ekor),
                    ]);
            } else {
                DB::table('assessment_dampak_lingkungan')->insert([
                    'id_assessment'              => $vital->id_assessment,
                    'lahan_pertanian_rusak_ha'   => (float)$vital->sawah_ha,
                    'hutan_terdampak_ha'         => (float)$vital->hutan_ha,
                    'ternak_terdampak_ekor'      => (int)$vital->ternak_ekor,
                ]);
            }
        }

        $v1Records = DB::table('assessment_dampak_manusia')
            ->leftJoin('assessment_dampak_manusia_v2', 'assessment_dampak_manusia.id_assessment_utama', '=', 'assessment_dampak_manusia_v2.id_assessment')
            ->whereNull('assessment_dampak_manusia_v2.id_dampak_v2')
            ->select('assessment_dampak_manusia.*')
            ->get();

        foreach ($v1Records as $v1) {
            DB::table('assessment_dampak_manusia_v2')->insert([
                'id_assessment'   => $v1->id_assessment_utama,
                'meninggal'       => (int)$v1->meninggal,
                'hilang'          => (int)$v1->hilang,
                'luka_berat'      => (int)$v1->luka_berat,
                'luka_ringan'     => (int)$v1->luka_ringan,
                'terdampak_jiwa'  => (int)$v1->menderita_mengungsi,
                'pengungsi_jiwa'  => 0,
                'pengungsi_kk'    => 0,
                'dibuat_pada'     => now(),
                'diperbarui_pada' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('assessment_dampak_lingkungan', function (Blueprint $t) {
            if (Schema::hasColumn('assessment_dampak_lingkungan', 'ternak_terdampak_ekor')) {
                $t->dropColumn('ternak_terdampak_ekor');
            }
        });
    }
};
