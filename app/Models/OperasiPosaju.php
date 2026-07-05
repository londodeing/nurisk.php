<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperasiPosaju extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'operasi_posaju';
    protected $primaryKey = 'id_posaju';
    
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    const DELETED_AT = 'dihapus_pada';

    // protected $with = ["insiden"]; // removed — eager-load explicitly where needed
    protected $fillable = [
        'id_insiden',
        'id_periode_operasi',
        'id_pleno_pendirian',
        'nama_posaju',
        'id_surat_pendirian',
        'alamat_lokasi',
        'latitude',
        'longitude',
        'pj_posaju',
        'status_alur',
        'waktu_diaktifkan',
        'diperpanjang_hingga',
        'waktu_ditutup',
        'alasan_penutupan',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'waktu_diaktifkan' => 'datetime',
        'diperpanjang_hingga' => 'datetime',
        'waktu_ditutup' => 'datetime',
    ];

    /**
     * Relasi ke Insiden
     */
    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    /**
     * Relasi ke Penanggung Jawab
     */
    public function pj(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'pj_posaju', 'id_pengguna');
    }

    /**
     * Relasi ke Surat Keluar
     */
    public function suratPendirian(): BelongsTo
    {
        return $this->belongsTo(OperasiSuratKeluar::class, 'id_surat_pendirian', 'id_surat');
    }

    /**
     * Kebutuhan Relawan
     */
    public function kebutuhanRelawan(): HasMany
    {
        return $this->hasMany(RelawanKebutuhan::class, 'id_posaju', 'id_posaju');
    }

    /**
     * Stok logistik di pos aju ini
     */
    public function stok(): HasMany
    {
        return $this->hasMany(LogistikStok::class, 'id_posaju', 'id_posaju');
    }

    /**
     * Personel yang ditugaskan ke pos aju ini
     */
    public function personel(): HasMany
    {
        return $this->hasMany(RelawanPenugasan::class, 'id_posaju', 'id_posaju');
    }

    // --- Helpers ---

    public function isAktif(): bool
    {
        return $this->status_alur === 'aktif';
    }

    public function labelStatus(): string
    {
        return match ($this->status_alur) {
            'direncanakan' => 'Direncanakan',
            'aktif'        => 'Aktif',
            'ditutup'      => 'Ditutup',
            default        => ucfirst(str_replace('_', ' ', $this->status_alur ?? '')),
        };
    }

    public function scopeAktif($query)
    {
        return $query->where('status_alur', 'aktif');
    }
}
