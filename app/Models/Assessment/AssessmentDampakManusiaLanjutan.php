<?php

namespace App\Models\Assessment;

use App\Models\AssessmentUtama;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentDampakManusiaLanjutan extends Model
{
    protected $table = 'assessment_dampak_manusia_lanjutan';
    protected $primaryKey = 'id_dampak_lanjutan';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    public $timestamps = true;

    protected $fillable = [
        'id_assessment',
        'luka_berat',
        'luka_ringan',
        'pengungsi_dalam_shelter',
        'pengungsi_mandiri',
        'balita_terdampak',
        'anak_terdampak',
        'lansia_terdampak',
        'ibu_hamil_terdampak',
        'disabilitas_terdampak',
        'jumlah_kk_terdampak',
        'jumlah_kk_mengungsi',
        'catatan_dampak_manusia',
    ];

    protected $casts = [
        'luka_berat' => 'integer',
        'luka_ringan' => 'integer',
        'pengungsi_dalam_shelter' => 'integer',
        'pengungsi_mandiri' => 'integer',
        'balita_terdampak' => 'integer',
        'anak_terdampak' => 'integer',
        'lansia_terdampak' => 'integer',
        'ibu_hamil_terdampak' => 'integer',
        'disabilitas_terdampak' => 'integer',
        'jumlah_kk_terdampak' => 'integer',
        'jumlah_kk_mengungsi' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }
}
