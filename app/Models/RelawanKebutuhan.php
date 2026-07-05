<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model untuk tabel relawan_kebutuhan
 *
 * Catatan SQL v37 Frozen:
 * - PK: id_relawan_kebutuhan (BIGINT UNSIGNED, AUTO_INCREMENT)
 * - Timestamps: HANYA dibuat_pada (tidak ada diperbarui_pada)
 * - Soft Delete: dihapus_pada ✅
 * - status_rekrutmen: enum('dibuka','terpenuhi','dibatalkan','ditutup') DEFAULT 'dibuka'
 *
 * FK Keluar:
 * - id_insiden          → operasi_insiden.id_insiden          (RESTRICT)
 * - id_keahlian_utama   → auth_keahlian_master.id_keahlian    (nullable)
 * - id_posaju           → operasi_posaju.id_posaju            (SET NULL, nullable)
 * - id_operasi_klaster  → operasi_klaster.id_operasi_klaster  (CASCADE, nullable)
 *
 * FK Masuk:
 * - relawan_pendaftaran.id_relawan_kebutuhan (CASCADE)
 */
class RelawanKebutuhan extends Model
{
    use SoftDeletes;

    protected $table = 'relawan_kebutuhan';
    protected $primaryKey = 'id_relawan_kebutuhan';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Tabel hanya memiliki 'dibuat_pada', tidak ada 'diperbarui_pada'.
     * Nonaktifkan timestamps Eloquent otomatis dan definisikan CREATED_AT manual.
     */
    public $timestamps = false;
    const CREATED_AT = 'dibuat_pada';

    /**
     * Custom deleted_at column sesuai SQL Frozen.
     */
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'id_insiden',
        'id_operasi_klaster',
        'id_posaju',
        'id_keahlian_utama',
        'judul_posisi',
        'deskripsi_tugas',
        'jumlah_dibutuhkan',
        'persyaratan',
        'status_rekrutmen',
        'tgl_mulai_tugas',
        'tgl_selesai_tugas',
    ];

    protected $casts = [
        'jumlah_dibutuhkan' => 'int',
        'tgl_mulai_tugas'   => 'date',
        'tgl_selesai_tugas' => 'date',
        'dibuat_pada'       => 'datetime',
        'dihapus_pada'      => 'datetime',
    ];

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Insiden yang membutuhkan relawan ini.
     * FK: id_insiden → operasi_insiden.id_insiden (RESTRICT)
     */
    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    /**
     * Keahlian utama yang dibutuhkan (nullable).
     * FK: id_keahlian_utama → auth_keahlian_master.id_keahlian
     */
    public function keahlianUtama(): BelongsTo
    {
        return $this->belongsTo(AuthKeahlianMaster::class, 'id_keahlian_utama', 'id_keahlian');
    }

    /**
     * Seluruh pendaftar untuk kebutuhan ini.
     * FK masuk: relawan_pendaftaran.id_relawan_kebutuhan (CASCADE)
     */
    public function pendaftaran(): HasMany
    {
        return $this->hasMany(RelawanPendaftaran::class, 'id_relawan_kebutuhan', 'id_relawan_kebutuhan');
    }

    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    /**
     * Kebutuhan yang masih dalam status rekrutmen terbuka.
     */
    public function scopeDibuka($query)
    {
        return $query->where('status_rekrutmen', 'dibuka');
    }

    /**
     * Kebutuhan berdasarkan insiden tertentu.
     */
    public function scopeByInsiden($query, int $idInsiden)
    {
        return $query->where('id_insiden', $idInsiden);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Apakah rekrutmen masih dibuka?
     */
    public function isDibuka(): bool
    {
        return $this->status_rekrutmen === 'dibuka';
    }

    /**
     * Label status rekrutmen ramah pembaca.
     */
    public function labelStatusRekrutmen(): string
    {
        return match ($this->status_rekrutmen) {
            'dibuka'    => 'Dibuka',
            'terpenuhi' => 'Terpenuhi',
            'dibatalkan'=> 'Dibatalkan',
            'ditutup'   => 'Ditutup',
            default     => ucfirst((string) $this->status_rekrutmen),
        };
    }
}
