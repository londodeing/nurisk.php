<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentDampakInfrastruktur extends Model
{
    use HasFactory;

    protected $table = 'assessment_dampak_infrastruktur';
    protected $primaryKey = 'id_dampak_infra';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_assessment',
        'rumah_rusak_berat', 'rumah_rusak_sedang', 'rumah_rusak_ringan', 'rumah_terendam',
        'jalan_rusak_km', 'jembatan_putus', 'jembatan_rusak',
        'fasilitas_kesehatan_rusak', 'fasilitas_pendidikan_rusak',
        'tempat_ibadah_rusak', 'kantor_pemerintah_rusak',
        'sarana_air_bersih_rusak', 'jaringan_listrik_padam_kk', 'jaringan_komunikasi_putus',
        'catatan_infrastruktur',
    ];
}
