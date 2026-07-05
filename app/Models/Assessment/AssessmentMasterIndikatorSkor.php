<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentMasterIndikatorSkor extends Model
{
    use HasFactory;

    protected $table = 'assessment_master_indikator_skor';
    protected $primaryKey = 'id_indikator';
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $guarded = ['id_indikator'];
}
