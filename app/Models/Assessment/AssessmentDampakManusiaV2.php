<?php

namespace App\Models\Assessment;

use App\Models\AssessmentUtama;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentDampakManusiaV2 extends Model
{
    protected $table = 'assessment_dampak_manusia_v2';
    protected $primaryKey = 'id_dampak_v2';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    
    public $timestamps = true;

    protected $fillable = [
        'id_assessment', 'meninggal', 'hilang', 'luka_berat', 'luka_ringan',
        'terdampak_jiwa', 'terdampak_kk', 'pengungsi_jiwa', 'pengungsi_kk',
        'pengungsi_balita', 'pengungsi_lansia', 'pengungsi_disabilitas', 'pengungsi_ibu_hamil',
    ];

    protected $casts = [
        'meninggal' => 'integer',
        'hilang' => 'integer',
        'luka_berat' => 'integer',
        'luka_ringan' => 'integer',
        'terdampak_jiwa' => 'integer',
        'terdampak_kk' => 'integer',
        'pengungsi_jiwa' => 'integer',
        'pengungsi_kk' => 'integer',
        'pengungsi_balita' => 'integer',
        'pengungsi_lansia' => 'integer',
        'pengungsi_disabilitas' => 'integer',
        'pengungsi_ibu_hamil' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }

    // Accessor untuk kompatibilitas dengan nama field lama di form
    public function getDampakManusiaAttribute(): int
    {
        return $this->terdampak_jiwa;
    }

    public function getTotalKorbanAttribute(): int
    {
        return $this->meninggal + $this->hilang + $this->luka_berat + $this->luka_ringan;
    }

    public function getKelompokRentanAttribute(): int
    {
        return $this->pengungsi_balita + $this->pengungsi_lansia
             + $this->pengungsi_disabilitas + $this->pengungsi_ibu_hamil;
    }
}
