<?php

namespace App\Models\Assessment;

use App\Models\AssessmentUtama;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentDampakVital extends Model
{
    protected $table = 'assessment_dampak_vital';
    protected $primaryKey = 'id_dampak_vital';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    
    public $timestamps = true;

    protected $fillable = [
        'id_assessment', 'air_bersih', 'listrik', 'telekomunikasi', 'irigasi', 'jalan',
        'spbu', 'sawah_ha', 'ternak_ekor', 'hutan_ha', 'sumber_air_tercemar', 'catatan_vital'
    ];

    protected $casts = [
        'air_bersih' => 'integer',
        'listrik' => 'integer',
        'telekomunikasi' => 'integer',
        'irigasi' => 'decimal:2',
        'jalan' => 'decimal:2',
        'spbu' => 'integer',
        'sawah_ha' => 'decimal:2',
        'ternak_ekor' => 'integer',
        'hutan_ha' => 'decimal:2',
        'sumber_air_tercemar' => 'boolean',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }
}
