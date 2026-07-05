<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WilayahDesa extends Model
{
    use HasFactory;

    protected $table = 'wilayah_desa';
    protected $primaryKey = 'id_desa';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_desa',
        'id_kec',
        'nama_desa',
    ];

    /**
     * Relasi BelongsTo ke WilayahKecamatan
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'id_kec', 'id_kec');
    }
}
