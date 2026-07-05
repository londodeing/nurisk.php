<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WilayahKabupaten extends Model
{
    use HasFactory;

    protected $table = 'wilayah_kabupaten';
    protected $primaryKey = 'id_kab';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_kab',
        'nama_kab',
        'tipe',
    ];

    /**
     * Relasi ke model WilayahKecamatan
     */
    public function kecamatan(): HasMany
    {
        return $this->hasMany(WilayahKecamatan::class, 'id_kab', 'id_kab');
    }
}
