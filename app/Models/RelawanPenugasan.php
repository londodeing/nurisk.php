<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model untuk tabel relawan_penugasan
 *
 * Catatan SQL v37 Frozen:
 * - PK: id_penugasan_relawan (BIGINT UNSIGNED, AUTO_INCREMENT)
 * - Timestamps: TIDAK ADA (tidak ada dibuat_pada / diperbarui_pada)
 * - Soft Delete: dihapus_pada ✅
 * - status_aktif: tinyint(1) DEFAULT 1 (0 = selesai/ditarik) — bukan enum
 *
 * FK Keluar:
 * - id_pendaftaran       → relawan_pendaftaran.id_pendaftaran   (CASCADE)
 * - id_penugasan_insiden → operasi_penugasan.id_incident_assignment (SET NULL, nullable)
 * - id_posaju            → operasi_posaju.id_posaju                 (nullable)
 * - id_surat_tugas       → operasi_surat_keluar.id_surat            (SET NULL, nullable)
 *
 * FK Masuk:
 * - relawan_shift.id_penugasan_relawan (CASCADE)
 */
class RelawanPenugasan extends Model
{
    use SoftDeletes;

    protected $table = 'relawan_penugasan';
    protected $primaryKey = 'id_penugasan_relawan';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Tabel tidak memiliki timestamps sama sekali di SQL v37 Frozen.
     */
    public $timestamps = false;

    /**
     * Custom deleted_at column sesuai SQL Frozen.
     */
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'id_pendaftaran',
        'id_penugasan_insiden',
        'id_posaju',
        'peran_lapangan',
        'tgl_mulai_aktif',
        'tgl_selesai_aktif',
        'status_aktif',
        'id_surat_tugas',
    ];

    protected $casts = [
        'status_aktif'      => 'boolean',
        'tgl_mulai_aktif'   => 'date',
        'tgl_selesai_aktif' => 'date',
        'dihapus_pada'      => 'datetime',
    ];

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Pendaftaran asal yang menghasilkan penugasan ini.
     * FK: id_pendaftaran → relawan_pendaftaran.id_pendaftaran (CASCADE)
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(RelawanPendaftaran::class, 'id_pendaftaran', 'id_pendaftaran');
    }

    /**
     * Shift jadwal kerja relawan ini.
     * FK masuk: relawan_shift.id_penugasan_relawan (CASCADE)
     */
    public function shift(): HasMany
    {
        return $this->hasMany(RelawanShift::class, 'id_penugasan_relawan', 'id_penugasan_relawan');
    }

    /**
     * Pos Aju tempat ditugaskan.
     * FK: id_posaju -> operasi_posaju.id_posaju
     */
    public function posaju(): BelongsTo
    {
        return $this->belongsTo(OperasiPosaju::class, 'id_posaju', 'id_posaju');
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Penugasan Insiden (Command Staff link)
     */
    public function penugasanInsiden(): BelongsTo
    {
        return $this->belongsTo(
            OperasiPenugasan::class,
            'id_penugasan_insiden',
            'id_incident_assignment'
        );
    }

    /**
     * Apakah relawan masih aktif bertugas?
     */
    public function isAktif(): bool
    {
        return (bool) $this->status_aktif;
    }
}
