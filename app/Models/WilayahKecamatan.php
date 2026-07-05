<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WilayahKecamatan extends Model
{
    use HasFactory;

    protected $table = 'wilayah_kecamatan';
    protected $primaryKey = 'id_kec';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_kec',
        'id_kab',
        'nama_kec',
    ];

    /**
     * Relasi BelongsTo ke WilayahKabupaten
     */
    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(WilayahKabupaten::class, 'id_kab', 'id_kab');
    }

    /**
     * Relasi ke model WilayahDesa
     */
    public function desa(): HasMany
    {
        return $this->hasMany(WilayahDesa::class, 'id_kec', 'id_kec');
    }
}
