<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AssessmentUtama extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assessment_utama';
    protected $primaryKey = 'id_assessment_utama';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    // $with = ["insiden"]; // removed — eager-load explicitly where needed
    protected $fillable = [
        'id_insiden',
        'id_petugas_assessment',
        'jenis_laporan',
        'cakupan_wilayah_deskripsi',
        'latitude',
        'longitude',
        'is_latest',
        'status_review',
        'catatan_review',
        'id_reviewer',
        'waktu_review',
        'waktu_assesment',
        'uuid_assessment',
        'sync_version',
        'deleted_by',
        'alasan_hapus',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid_assessment)) {
                $model->uuid_assessment = (string) Str::uuid();
            }
            if (empty($model->sync_version)) {
                $model->sync_version = 1;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty() && !$model->isDirty('sync_version')) {
                $model->sync_version++;
            }
        });
    }

    protected $casts = [
        'is_latest' => 'boolean',
        'waktu_assesment' => 'datetime',
        'waktu_review' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_petugas_assessment', 'id_pengguna');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_reviewer', 'id_pengguna');
    }

    public function scopeLatest($query)
    {
        return $query->where('is_latest', 1);
    }

    public function insiden()
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    /**
     * Dampak manusia V1 — legacy, prefer dampakManusiaV2
     * @deprecated Use dampakManusiaV2() instead
     */
    public function dampakManusia()
    {
        return $this->hasOne(AssessmentDampakManusia::class, 'id_assessment_utama', 'id_assessment_utama');
    }

    /**
     * Kebutuhan mendesak V1 — legacy
     * @deprecated
     */
    public function kebutuhanMendesak()
    {
        return $this->hasMany(AssessmentKebutuhanMendesak::class, 'id_assessment_utama', 'id_assessment_utama');
    }

    /**
     * Dampak manusia V2 — canonical relation (preferred over dampakManusia)
     */
    public function dampakManusiaV2(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentDampakManusiaV2::class, 'id_assessment', 'id_assessment_utama');
    }

    public function dampakInfrastruktur()
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentDampakInfrastruktur::class, 'id_assessment', 'id_assessment_utama');
    }

    public function dampakLingkungan()
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentDampakLingkungan::class, 'id_assessment', 'id_assessment_utama');
    }

    public function dampakEkonomi()
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentDampakEkonomi::class, 'id_assessment', 'id_assessment_utama');
    }

    public function biodataKejadian()
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentBiodataKejadian::class, 'id_assessment', 'id_assessment_utama');
    }

    public function narasiKejadian()
    {
        return $this->hasMany(\App\Models\Assessment\AssessmentNarasiKejadian::class, 'id_assessment', 'id_assessment_utama');
    }

    // Relasi ke extension tables (BARU)
    public function lokasiDetail(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentLokasiDetail::class, 'id_assessment', 'id_assessment_utama');
    }

    public function narasiDetail(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentNarasiDetail::class, 'id_assessment', 'id_assessment_utama');
    }

    public function kebutuhanLanjutan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentKebutuhanLanjutan::class, 'id_assessment', 'id_assessment_utama');
    }

    public function kebutuhanNumerik(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Assessment\AssessmentKebutuhanNumerik::class, 'id_assessment', 'id_assessment_utama')
                    ->with('item')
                    ->orderBy('id_item');
    }

    public function dampakRumah(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentDampakRumah::class, 'id_assessment', 'id_assessment_utama');
    }

    public function dampakFasum(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentDampakFasum::class, 'id_assessment', 'id_assessment_utama');
    }

    public function dampakVital(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentDampakVital::class, 'id_assessment', 'id_assessment_utama');
    }

    /**
     * Dampak manusia LANJUTAN — V1 extension (tabel: assessment_dampak_manusia_lanjutan)
     */
    public function dampakManusiaLanjutan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentDampakManusiaLanjutan::class, 'id_assessment', 'id_assessment_utama');
    }

    /**
     * Ringkasan skor — hasil kalkulasi (tabel: assessment_ringkasan_skor)
     */
    public function ringkasanSkor(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Assessment\AssessmentRingkasanSkor::class, 'id_assessment', 'id_assessment_utama');
    }

    /**
     * Skor item detail per indikator
     */
    public function skorItem(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Assessment\AssessmentSkorItem::class, 'id_assessment', 'id_assessment_utama');
    }

    // Accessor kompatibilitas backward — form menggunakan kondisi_mutakhir, DB pakai kondisi_umum
    public function getKondisiMutakhirAttribute(): ?string
    {
        return $this->kondisi_umum;
    }

    public function setKondisiMutakhirAttribute(?string $value): void
    {
        $this->attributes['kondisi_umum'] = $value;
    }
}

