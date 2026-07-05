<?php

namespace App\Models\Assessment;

use App\Models\AssessmentUtama;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentKebutuhanNumerik extends Model
{
    protected $table = 'assessment_kebutuhan_numerik';
    protected $primaryKey = 'id_kebutuhan_num';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    
    public $timestamps = true;

    protected $fillable = [
        'id_assessment', 'id_item', 'jumlah_dibutuhkan',
        'jumlah_tersedia', 'satuan', 'prioritas', 'keterangan'
    ];

    protected $casts = [
        'jumlah_dibutuhkan' => 'decimal:2',
        'jumlah_tersedia'   => 'decimal:2',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AssessmentKebutuhanNumerikMaster::class, 'id_item', 'id_item');
    }

    public function getGapAttribute(): float
    {
        return max(0, (float)$this->jumlah_dibutuhkan - (float)$this->jumlah_tersedia);
    }
}
