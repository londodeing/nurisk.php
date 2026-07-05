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
        'tingkat_kerusakan_lingkungan', 'butuh_rehabilitasi_lahan',
        'catatan_lingkungan', 'ternak_terdampak_ekor',
    ];
}
