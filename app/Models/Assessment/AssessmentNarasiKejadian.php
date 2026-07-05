<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentNarasiKejadian extends Model
{
    use HasFactory;

    protected $table = 'assessment_narasi_kejadian';
    protected $primaryKey = 'id_narasi';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $guarded = ['id_narasi'];
}
