<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JabatanPosisi extends Model
{
    use HasFactory;

    protected $table = 'master_jabatan';
    protected $primaryKey = 'id_jabatan_posisi';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'nama_jabatan',
        'slug',
        'deskripsi',
    ];

    protected $casts = [
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
    ];

    /**
     * Relasi ke model PenggunaJabatan
     */
    public function penggunaJabatan(): HasMany
    {
        return $this->hasMany(PenggunaJabatan::class, 'id_jabatan_posisi', 'id_jabatan_posisi');
    }

    /**
     * Accessor untuk memformat nama jabatan
     */
    public function getDisplayNamaAttribute(): string
    {
        return ucwords($this->nama_jabatan);
    }
}
