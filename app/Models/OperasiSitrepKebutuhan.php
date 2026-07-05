<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperasiSitrepKebutuhan extends Model
{
    use HasFactory;

    protected $table = 'operasi_sitrep_kebutuhan';
    protected $primaryKey = 'id_sitrep_kebutuhan';
    public $timestamps = false;

    protected $fillable = [
        'id_sitrep',
        'nama_kebutuhan',
        'jumlah',
        'satuan',
    ];

    /**
     * Relasi ke sitrep utama
     */
    public function sitrep(): BelongsTo
    {
        return $this->belongsTo(OperasiSitrep::class, 'id_sitrep', 'id_sitrep');
    }
}
