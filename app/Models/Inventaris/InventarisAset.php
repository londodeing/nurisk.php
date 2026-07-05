<?php

namespace App\Models\Inventaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\AuthUser;

class InventarisAset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventaris_aset';
    protected $primaryKey = 'id_aset';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $guarded = ['id_aset'];

    protected $casts = [
        'nilai_perolehan' => 'decimal:2',
        'nilai_sekarang' => 'decimal:2',
        'bisa_dikerahkan_bencana' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function jenis()
    {
        return $this->belongsTo(InventarisJenis::class, 'id_jenis', 'id_jenis');
    }

    public function pemilik()
    {
        // Adjust to your actual OrganisasiUnit model path
        return $this->belongsTo(\App\Models\Organisasi\OrganisasiUnit::class, 'id_unit_pemilik', 'id_unit');
    }

    public function lokasiDetail()
    {
        return $this->hasOne(InventarisLokasiDetail::class, 'id_aset', 'id_aset');
    }

    public function dokumen()
    {
        return $this->hasMany(InventarisDokumen::class, 'id_aset', 'id_aset');
    }

    public function kondisiLog()
    {
        return $this->hasMany(InventarisKondisiLog::class, 'id_aset', 'id_aset')->latest('dicatat_pada');
    }

    public function pemeliharaan()
    {
        return $this->hasMany(InventarisPemeliharaan::class, 'id_aset', 'id_aset');
    }

    public function deploymentBencana()
    {
        return $this->hasMany(InventarisDeploymentBencana::class, 'id_aset', 'id_aset');
    }

    public function deploymentAktif()
    {
        return $this->hasOne(InventarisDeploymentBencana::class, 'id_aset', 'id_aset')->whereNull('waktu_kembali');
    }

    public function getIsAktifDiLapanganAttribute(): bool
    {
        return !is_null($this->deploymentAktif);
    }

    public function getDokumenKadaluwarsaAttribute()
    {
        return $this->dokumen()->whereNotNull('berlaku_hingga')->where('berlaku_hingga', '<', now())->get();
    }
}
