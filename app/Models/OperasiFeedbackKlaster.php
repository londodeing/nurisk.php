<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperasiFeedbackKlaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_feedback_klaster';
    protected $primaryKey = 'id_feedback_klaster';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'uuid_feedback_klaster',
        'id_insiden',
        'id_klaster_operasi',
        'id_pengguna',
        'kecukupan_sumberdaya',
        'kualitas_layanan',
        'tepat_waktu',
        'tepat_sasaran',
        'kendala',
        'rekomendasi',
        'gap_terdeteksi',
        'status_feedback',
        'dikunci_pada',
    ];

    protected $casts = [
        'gap_terdeteksi' => 'array',
        'tepat_waktu' => 'boolean',
        'tepat_sasaran' => 'boolean',
        'dikunci_pada' => 'datetime',
    ];

    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    public function klasterOperasi(): BelongsTo
    {
        return $this->belongsTo(OperasiKlaster::class, 'id_klaster_operasi', 'id_klaster_operasi');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function gapKebutuhan(): HasMany
    {
        return $this->hasMany(OperasiGapKebutuhan::class, 'id_feedback_klaster', 'id_feedback_klaster');
    }

    public function isFinal(): bool
    {
        return $this->status_feedback === 'final';
    }

    public function finalize(): void
    {
        $this->update([
            'status_feedback' => 'final',
            'dikunci_pada' => now(),
        ]);
    }
}