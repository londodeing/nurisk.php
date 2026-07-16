<?php

namespace App\Models\Assessment;

use App\Models\AssessmentUtama;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentDampakRumah extends Model
{
    protected $table = 'assessment_dampak_rumah';
    protected $primaryKey = 'id_dampak_rumah';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    
    public $timestamps = true;

    protected $fillable = [
        'id_assessment', 'rusak_berat', 'rusak_sedang', 'rusak_ringan', 'terendam', 'terancam'
    ];

    protected $casts = [
        'rusak_berat' => 'integer',
        'rusak_sedang' => 'integer',
        'rusak_ringan' => 'integer',
        'terendam' => 'integer',
        'terancam' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }
}
