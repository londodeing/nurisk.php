<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentDampakEkonomi extends Model
{
    use HasFactory;

    protected $table = 'assessment_dampak_ekonomi';
    protected $primaryKey = 'id_dampak_eko';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $guarded = ['id_dampak_eko'];
}
