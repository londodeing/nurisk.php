<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentDampakLingkungan extends Model
{
    use HasFactory;

    protected $table = 'assessment_dampak_lingkungan';
    protected $primaryKey = 'id_dampak_ling';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_assessment',
        'lahan_pertanian_rusak_ha', 'hutan_terdampak_ha', 'lahan_tercemar_ha',
        'sumber_air_tercemar', 'pencemaran_tanah', 'erosi_sedimentasi',
        'kerusakan_ekosistem_pesisir', 'kerusakan_daerah_aliran_sungai',
        'ternak_unggas_ekor', 'ternak_kaki_empat_ekor',
        'perikanan_kolam_ha', 'perikanan_nelayan_unit',
        'catatan_lingkungan',
    ];
}
