<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperasiPleno extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_pleno';
    protected $primaryKey = 'id_pleno';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'id_insiden',
        'nomor_pleno',
        'waktu_pleno',
        'jenis_pleno',
        'pimpinan_pleno',
        'notulis_pleno',
        'lokasi_pleno',
        'hasil_umum',
        'status_pleno',
        'disetujui_oleh',
        'waktu_disetujui',
        'waktu_difinalisasi',
    ];

    protected $casts = [
        'waktu_pleno' => 'datetime',
        'waktu_disetujui' => 'datetime',
        'waktu_ditandatangani' => 'datetime',
        'waktu_dikunci' => 'datetime',
        'waktu_difinalisasi' => 'datetime',
        'dibuat_pada' => 'datetime',
        'dihapus_pada' => 'datetime',
    ];

    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    public function keputusan(): HasMany
    {
        return $this->hasMany(OperasiPlenoKeputusan::class, 'id_pleno', 'id_pleno');
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(OperasiPlenoPeserta::class, 'id_pleno', 'id_pleno');
    }

    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'pimpinan_pleno', 'id_pengguna');
    }

    public function notulis(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'notulis_pleno', 'id_pengguna');
    }

    public function eskalasi(): HasOne
    {
        return $this->hasOne(OperasiEskalasi::class, 'id_pleno', 'id_pleno');
    }

    public function scopeAktif($query)
    {
        return $query->whereNotIn('status_pleno', ['final', 'dibatalkan']);
    }

    public function scopeByInsiden($query, int $idInsiden)
    {
        return $query->where('id_insiden', $idInsiden);
    }

    public function isFinal(): bool
    {
        return in_array($this->status_pleno, ['final', 'ditandatangani']);
    }

    public function isDraft(): bool
    {
        return $this->status_pleno === 'draft';
    }

    public function labelStatus(): string
    {
        return match ($this->status_pleno) {
            'draft' => 'Draft',
            'ditinjau' => 'Ditinjau',
            'disetujui' => 'Disetujui',
            'ditandatangani' => 'Ditandatangani',
            'final' => 'Final',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst($this->status_pleno),
        };
    }

    public function warnaBadgeStatus(): string
    {
        return match ($this->status_pleno) {
            'draft' => 'bg-gray-100 text-gray-600',
            'ditinjau' => 'bg-blue-100 text-blue-700',
            'disetujui' => 'bg-green-100 text-green-700',
            'ditandatangani' => 'bg-emerald-100 text-emerald-700',
            'final' => 'bg-emerald-100 text-emerald-700',
            'dibatalkan' => 'bg-red-100 text-red-600',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
