<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class OperasiSitrep extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_sitrep';
    protected $primaryKey = 'id_sitrep';
    public $timestamps = true;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $with = ["insiden"];
    protected $fillable = [
        'id_insiden',
        'id_assessment_basis',
        'nomor_sitrep',
        'periode_sitrep',
        'waktu_sitrep',
        'id_pembuat',
        'catatan',
        'jumlah_personel',
        'jumlah_klaster_aktif',
        'sync_version',
        'deleted_by',
        'alasan_hapus',
    ];

    protected $casts = [
        'waktu_sitrep' => 'datetime',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
        'dihapus_pada' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid_sitrep)) {
                $model->uuid_sitrep = (string) Str::uuid();
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

    /**
     * Relasi ke insiden
     */
    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    /**
     * Relasi ke assessment yang menjadi basis sitrep ini
     */
    public function assessmentBasis(): BelongsTo
    {
        return $this->belongsTo(AssessmentUtama::class, 'id_assessment_basis', 'id_assessment_utama');
    }

    /**
     * Relasi ke pembuat (user)
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pembuat', 'id_pengguna');
    }

    /**
     * Relasi ke dampak (snapshot)
     */
    public function dampak(): HasOne
    {
        return $this->hasOne(OperasiSitrepDampak::class, 'id_sitrep', 'id_sitrep');
    }

    /**
     * Relasi ke kebutuhan (snapshot)
     */
    public function kebutuhan(): HasMany
    {
        return $this->hasMany(OperasiSitrepKebutuhan::class, 'id_sitrep', 'id_sitrep');
    }
}
