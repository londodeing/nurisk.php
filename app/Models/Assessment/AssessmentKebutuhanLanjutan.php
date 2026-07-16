<?php

namespace App\Models\Assessment;

use App\Models\AssessmentUtama;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentKebutuhanLanjutan extends Model
{
    protected $table = 'assessment_kebutuhan_lanjutan';
    protected $primaryKey = 'id_kebutuhan_lanjutan';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    
    public $timestamps = true;

    protected $fillable = [
        'id_assessment', 'kebutuhan_relawan', 'kebutuhan_logistik',
        'kebutuhan_peralatan', 'kebutuhan_medis', 'kebutuhan_pangan', 'kebutuhan_lainnya'
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }
}
