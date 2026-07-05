<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AssessmentUtama;

class AssessmentRingkasanSkor extends Model
{
    use HasFactory;

    protected $table = 'assessment_ringkasan_skor';
    protected $primaryKey = 'id_ringkasan';
    const CREATED_AT = null;
    const UPDATED_AT = 'dihitung_pada';

    protected $guarded = ['id_ringkasan'];

    public function assessment()
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }

    public function hitungSkorTotal(): float
    {
        return round(($this->skor_manusia * 0.35
            + $this->skor_infrastruktur * 0.25
            + $this->skor_lingkungan * 0.15
            + $this->skor_ekonomi * 0.15
            + $this->skor_sosial * 0.10), 2);
    }

    public function tentukantingkatKeparahan(): string
    {
        return match(true) {
            $this->skor_total < 20 => 'minor',
            $this->skor_total < 40 => 'sedang',
            $this->skor_total < 60 => 'signifikan',
            $this->skor_total < 80 => 'berat',
            default                => 'katastrofik',
        };
    }
}
