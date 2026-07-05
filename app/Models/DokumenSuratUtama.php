<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DokumenSuratUtama extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_surat_keluar';
    protected $primaryKey = 'id_surat';
    public $timestamps = true;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'id_insiden',
        'id_jenis_surat',
        'nomor_surat_resmi',
        'perihal',
        'tgl_terbit',
        'id_pengguna_ttd',
        'id_jabatan_ttd',
        'isi_surat_snapshot',
        'file_pdf_path',
        'status_surat',
    ];

    protected $casts = [
        'tgl_terbit' => 'date',
        'dibuat_pada' => 'datetime',
        'dihapus_pada' => 'datetime',
    ];

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(MasterSuratJenis::class, 'id_jenis_surat', 'id_jenis_surat');
    }

    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna_ttd', 'id_pengguna');
    }

    public function jabatanTtd(): BelongsTo
    {
        return $this->belongsTo(MasterJabatanPenandatangan::class, 'id_jabatan_ttd', 'id_jabatan');
    }

    public function paraf(): HasMany
    {
        return $this->hasMany(DokumenSuratParaf::class, 'id_surat', 'id_surat')->orderBy('urutan');
    }

    public function tembusan(): HasMany
    {
        return $this->hasMany(DokumenSuratTembusan::class, 'id_surat', 'id_surat');
    }

    public function parafAktif(): HasOne
    {
        return $this->hasOne(DokumenSuratParaf::class, 'id_surat', 'id_surat')
            ->where('status_paraf', 'menunggu')
            ->orderBy('urutan');
    }

    public function scopeDraft($query)
    {
        return $query->where('status_surat', 'draft');
    }

    public function scopeAktif($query)
    {
        return $query->whereNotIn('status_surat', ['arsip']);
    }

    public function isFinal(): bool
    {
        return in_array($this->status_surat, ['ditandatangani', 'arsip']);
    }

    public function isDraft(): bool
    {
        return $this->status_surat === 'draft';
    }

    public function isReviewParaf(): bool
    {
        return $this->status_surat === 'review_paraf';
    }

    public function labelStatus(): string
    {
        return match ($this->status_surat) {
            'draft' => 'Draft',
            'review_paraf' => 'Review Paraf',
            'siap_tanda_tangan' => 'Siap Tanda Tangan',
            'ditandatangani' => 'Ditandatangani',
            'ditolak' => 'Ditolak',
            'arsip' => 'Arsip',
            default => ucfirst($this->status_surat),
        };
    }

    public function warnaBadgeStatus(): string
    {
        return match ($this->status_surat) {
            'draft' => 'bg-gray-100 text-gray-600',
            'review_paraf' => 'bg-blue-100 text-blue-700',
            'siap_tanda_tangan' => 'bg-yellow-100 text-yellow-700',
            'ditandatangani' => 'bg-green-100 text-green-700',
            'ditolak' => 'bg-red-100 text-red-600',
            'arsip' => 'bg-slate-100 text-slate-500',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
