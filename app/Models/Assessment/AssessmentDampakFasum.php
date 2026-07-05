<?php

namespace App\Models\Assessment;

use App\Models\AssessmentUtama;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentDampakFasum extends Model
{
    protected $table = 'assessment_dampak_fasum';
    protected $primaryKey = 'id_dampak_fasum';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    
    public $timestamps = true;

    protected $fillable = [
        'id_assessment', 'sanitasi', 'pendidikan', 'kesehatan', 'ibadah', 'komunikasi',
        'listrik', 'kantor', 'jembatan', 'pasar', 'spbu', 'catatan_fasum'
    ];

    protected $casts = [
        'sanitasi' => 'integer',
        'pendidikan' => 'integer',
        'kesehatan' => 'integer',
        'ibadah' => 'integer',
        'komunikasi' => 'integer',
        'listrik' => 'integer',
        'kantor' => 'integer',
        'jembatan' => 'integer',
        'pasar' => 'integer',
        'spbu' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }
}
