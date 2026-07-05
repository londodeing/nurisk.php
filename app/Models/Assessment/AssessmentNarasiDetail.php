<?php

namespace App\Models\Assessment;

use App\Models\AssessmentUtama;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentNarasiDetail extends Model
{
    protected $table = 'assessment_narasi_detail';
    protected $primaryKey = 'id_narasi_detail';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    
    public $timestamps = true;

    protected $fillable = [
        'id_assessment', 'sebaran_dampak', 'kondisi_umum', 'upaya_penanganan',
        'kendala_lapangan', 'kendala_tambahan', 'rekomendasi_aksi'
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }
}
