<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterSuratJenis extends Model
{
    protected $table = 'master_surat_jenis';
    protected $primaryKey = 'id_jenis_surat';
    public $timestamps = false;

    protected $fillable = [
        'kode_jenis',
        'nama_jenis',
        'kategori',
        'format_nomor',
        'aktif',
        'deskripsi',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function template(): HasMany
    {
        return $this->hasMany(MasterSuratTemplate::class, 'id_jenis_surat', 'id_jenis_surat');
    }

    public function surat(): HasMany
    {
        return $this->hasMany(DokumenSuratUtama::class, 'id_jenis_surat', 'id_jenis_surat');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
