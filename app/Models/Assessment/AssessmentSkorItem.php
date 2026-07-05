<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentSkorItem extends Model
{
    use HasFactory;

    protected $table = 'assessment_skor_item';
    protected $primaryKey = 'id_skor';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $guarded = ['id_skor'];

    public function indikator()
    {
        return $this->belongsTo(\App\Models\Assessment\AssessmentMasterIndikatorSkor::class, 'id_indikator', 'id_indikator');
    }
}
