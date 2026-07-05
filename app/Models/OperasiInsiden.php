<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Scopes\ScopedByPcnu;

class OperasiInsiden extends Model
{
    use HasFactory, SoftDeletes, ScopedByPcnu;

    protected $table = 'operasi_insiden';
    protected $primaryKey = 'id_insiden';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'kode_kejadian',
        'id_laporan_asal',
        'id_jenis_bencana',
        'id_pcnu',
        'id_mwc',
        'status_insiden',
        'status_operasi',
        'is_locked',
        'prioritas',
        'no_spk_assesment',
        'tgl_spk_assesment',
        'id_pemberi_spk',
        'id_penerima_spk',
        'waktu_mulai',
        'waktu_selesai',
        'waktu_verifikasi',
        'waktu_respon_dimulai',
        'waktu_pemulihan_dimulai',
        'waktu_ditutup',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'waktu_verifikasi' => 'datetime',
        'waktu_respon_dimulai' => 'datetime',
        'waktu_pemulihan_dimulai' => 'datetime',
        'waktu_ditutup' => 'datetime',
        'tgl_spk_assesment' => 'date',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
        'dihapus_pada' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($insiden) {
            if (empty($insiden->uuid_insiden)) {
                $insiden->uuid_insiden = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Relasi ke model BencanaMasterJenis
     */
    public function jenisBencana(): BelongsTo
    {
        return $this->belongsTo(BencanaMasterJenis::class, 'id_jenis_bencana', 'id_jenis');
    }

    /**
     * Relasi ke laporan asal (laporan_kejadian)
     */
    public function laporanAsal(): BelongsTo
    {
        return $this->belongsTo(LaporanKejadian::class, 'id_laporan_asal', 'id_laporan_kejadian');
    }

    /**
     * Relasi ke model OrganisasiPcnu
     */
    public function pcnu(): BelongsTo
    {
        return $this->belongsTo(OrganisasiPcnu::class, 'id_pcnu', 'id_pcnu');
    }

    /**
     * Relasi ke model OperasiPosaju
     */
    public function posaju(): HasMany
    {
        return $this->hasMany(OperasiPosaju::class, 'id_insiden', 'id_insiden');
    }

    /**
     * Relasi ke model RiwayatStatusInsiden
     */
    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusInsiden::class, 'id_insiden', 'id_insiden');
    }

    /**
     * Relasi ke AssessmentUtama
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(AssessmentUtama::class, 'id_insiden', 'id_insiden');
    }

    /**
     * Relasi ke OperasiPenugasan
     */
    public function penugasan(): HasMany
    {
        return $this->hasMany(OperasiPenugasan::class, 'id_insiden', 'id_insiden');
    }

    public function pleno(): HasMany
    {
        return $this->hasMany(OperasiPleno::class, 'id_insiden', 'id_insiden');
    }

    public function pemberiSpk(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pemberi_spk', 'id_pengguna');
    }

    public function penerimaSpk(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_penerima_spk', 'id_pengguna');
    }

    /**
     * Query Scope: Insiden Aktif (belum selesai/dibatalkan)
     */
    public function scopeAktif($query)
    {
        return $query->whereNotIn('status_insiden', ['selesai', 'dibatalkan']);
    }

    /**
     * Query Scope: Filter berdasarkan PCNU
     */
    public function scopeByPcnu($query, int $idPcnu)
    {
        return $query->where('id_pcnu', $idPcnu);
    }

    /**
     * Query Scope: Insiden tidak terkunci
     */
    public function scopeTidakTerkunci($query)
    {
        return $query->where('is_locked', false);
    }

    /**
     * Helper: Apakah data insiden terkunci?
     */
    public function isTerkunci(): bool
    {
        return (bool) $this->is_locked;
    }

    /**
     * Helper: Apakah status insiden sudah selesai?
     */
    public function isSelesai(): bool
    {
        return $this->status_insiden === 'selesai';
    }

    /**
     * Helper: Apakah status insiden dibatalkan?
     */
    public function isDibatalkan(): bool
    {
        return $this->status_insiden === 'dibatalkan';
    }

    /**
     * Helper: Apakah insiden terkunci secara data (locked) atau status terminal (selesai/dibatalkan)?
     */
    public function isClosed(): bool
    {
        return $this->isTerkunci() || $this->isSelesai() || $this->isDibatalkan();
    }

    /**
     * Helper: Label status insiden yang ramah pembaca
     */
    public function labelStatus(): string
    {
        return match ($this->status_insiden) {
            'draft' => 'Draft',
            'terverifikasi' => 'Terverifikasi',
            'respon' => 'Respon',
            'pemulihan' => 'Pemulihan',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status_insiden)),
        };
    }

    /**
     * Helper: Warna badge status CSS Tailwind
     */
    public function warnaBadgeStatus(): string
    {
        return match ($this->status_insiden) {
            'draft' => 'bg-gray-100 text-gray-600',
            'terverifikasi' => 'bg-blue-100 text-blue-700',
            'respon' => 'bg-orange-100 text-orange-700',
            'pemulihan' => 'bg-yellow-100 text-yellow-700',
            'selesai' => 'bg-green-100 text-green-700',
            'dibatalkan' => 'bg-red-100 text-red-600',
            default => 'bg-gray-100 text-gray-500',
        };
    }
}
