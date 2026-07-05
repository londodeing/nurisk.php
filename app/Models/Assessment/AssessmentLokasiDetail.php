<?php

namespace App\Models\Assessment;

use App\Models\AssessmentUtama;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentLokasiDetail extends Model
{
    protected $table = 'assessment_lokasi_detail';
    protected $primaryKey = 'id_lokasi_detail';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    
    public $timestamps = true;

    protected $fillable = [
        'id_assessment', 'id_kec', 'id_desa', 'alamat_spesifik', 'region_terdampak'
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment', 'id_assessment_utama');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'id_kec', 'id_kec');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'id_desa', 'id_desa');
    }
}
