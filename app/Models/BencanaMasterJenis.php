<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BencanaMasterJenis extends Model
{
    use HasFactory;

    protected $table = 'bencana_master_jenis';
    protected $primaryKey = 'id_jenis';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'nama_bencana',
        'slug',
        'kategori',
        'deskripsi',
        'ikon_map',
    ];

    /**
     * Relasi ke model OperasiInsiden
     */
    public function insiden(): HasMany
    {
        return $this->hasMany(OperasiInsiden::class, 'id_jenis_bencana', 'id_jenis');
    }
}
