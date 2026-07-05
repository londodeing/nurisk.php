<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentDampakManusia extends Model
{
    use HasFactory;

    protected $table = 'assessment_dampak_manusia';
    public $timestamps = false;
    protected $primaryKey = 'id_dampak_manusia';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_assessment_utama',
        'meninggal',
        'hilang',
        'luka_berat',
        'luka_ringan',
        'menderita_mengungsi',
    ];

    public function assessmentUtama()
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment_utama', 'id_assessment_utama');
    }
}
