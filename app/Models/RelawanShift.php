<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk tabel relawan_shift
 *
 * Catatan SQL v37 Frozen:
 * - PK: id_relawan_shift (BIGINT UNSIGNED, AUTO_INCREMENT)
 * - Timestamps: TIDAK ADA
 * - Soft Delete: TIDAK ADA (cascade dari relawan_penugasan)
 * - Kolom: waktu_mulai (datetime NOT NULL), waktu_selesai (datetime NOT NULL)
 *
 * FK Keluar:
 * - id_penugasan_relawan → relawan_penugasan.id_penugasan_relawan (CASCADE)
 */
class RelawanShift extends Model
{
    protected $table = 'relawan_shift';
    protected $primaryKey = 'id_relawan_shift';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Tidak ada timestamps dan tidak ada soft delete di SQL v37.
     */
    public $timestamps = false;

    protected $fillable = [
        'id_penugasan_relawan',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected $casts = [
        'waktu_mulai'   => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Penugasan relawan yang memiliki shift ini.
     * FK: id_penugasan_relawan → relawan_penugasan.id_penugasan_relawan (CASCADE)
     */
    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(RelawanPenugasan::class, 'id_penugasan_relawan', 'id_penugasan_relawan');
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Durasi shift dalam menit.
     */
    public function durasiMenit(): int
    {
        return (int) $this->waktu_mulai->diffInMinutes($this->waktu_selesai);
    }
}
