<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperasiSitrepDampak extends Model
{
    use HasFactory;

    protected $table = 'operasi_sitrep_dampak';
    protected $primaryKey = 'id_sitrep_dampak';
    public $timestamps = false;

    protected $fillable = [
        'id_sitrep',
        'meninggal',
        'hilang',
        'luka_berat',
        'luka_ringan',
        'mengungsi',
    ];

    /**
     * Relasi ke sitrep utama
     */
    public function sitrep(): BelongsTo
    {
        return $this->belongsTo(OperasiSitrep::class, 'id_sitrep', 'id_sitrep');
    }
}
