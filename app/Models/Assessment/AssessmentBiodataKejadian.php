<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AssessmentUtama;

class AssessmentBiodataKejadian extends Model
{
    use HasFactory;

    protected $table = 'assessment_biodata_kejadian';
    protected $primaryKey = 'id_biodata';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_assessment',
        'tanggal_mulai_kejadian',
        'jam_mulai_kejadian',
        'kronologi_singkat',
        'penyebab_utama',
        'sumber_informasi_awal',
        'skala_kejadian',
        'luas_terdampak_ha',
        'jumlah_desa_terdampak',
        'jumlah_kecamatan_terdampak',
        'status_masih_berlangsung'
    ];

    protected $casts = [
        'tanggal_mulai_kejadian' => 'date',
        'status_masih_berlangsung' => 'boolean'
    ];

    public function assessment()
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }
}
