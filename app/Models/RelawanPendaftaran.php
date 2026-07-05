<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model untuk tabel relawan_pendaftaran
 *
 * Catatan SQL v37 Frozen:
 * - PK: id_pendaftaran (BIGINT UNSIGNED, AUTO_INCREMENT)
 * - Timestamps: HANYA waktu_daftar (timestamp DEFAULT current_timestamp) — bukan created_at/updated_at
 * - Soft Delete: dihapus_pada ✅
 * - UNIQUE: (id_pengguna, id_relawan_kebutuhan) — satu relawan satu kali per kebutuhan
 * - status_pendaftaran: enum('dibuka','seleksi','diterima','ditugaskan','selesai','ditolak')
 *   DEFAULT 'dibuka' — STATE MACHINE rekrutmen
 *
 * FK Keluar:
 * - id_relawan_kebutuhan → relawan_kebutuhan.id_relawan_kebutuhan (CASCADE)
 * - id_pengguna          → auth_users.id_pengguna
 * - id_verifikator       → auth_users.id_pengguna (nullable)
 * - id_penyaring         → auth_users.id_pengguna (nullable)
 *
 * FK Masuk:
 * - relawan_penugasan.id_pendaftaran (CASCADE)
 */
class RelawanPendaftaran extends Model
{
    use SoftDeletes;

    protected $table = 'relawan_pendaftaran';
    protected $primaryKey = 'id_pendaftaran';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Tabel tidak memiliki created_at / updated_at standar Eloquent.
     * Kolom waktu_daftar dihandle via default current_timestamp di DB.
     */
    public $timestamps = false;

    /**
     * Custom deleted_at column sesuai SQL Frozen.
     */
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'id_relawan_kebutuhan',
        'id_pengguna',
        'motivasi_singkat',
        'catatan_verifikator',
        'id_verifikator',
        'waktu_verifikasi',
        'status_pendaftaran',
        'id_penyaring',
        'waktu_penyaringan',
        'waktu_penugasan_dimulai',
        'waktu_penugasan_selesai',
    ];

    protected $casts = [
        'waktu_daftar'            => 'datetime',
        'waktu_verifikasi'        => 'datetime',
        'waktu_penyaringan'       => 'datetime',
        'waktu_penugasan_dimulai' => 'datetime',
        'waktu_penugasan_selesai' => 'datetime',
        'dihapus_pada'            => 'datetime',
    ];

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Kebutuhan relawan yang didaftari.
     * FK: id_relawan_kebutuhan → relawan_kebutuhan.id_relawan_kebutuhan (CASCADE)
     */
    public function kebutuhan(): BelongsTo
    {
        return $this->belongsTo(RelawanKebutuhan::class, 'id_relawan_kebutuhan', 'id_relawan_kebutuhan');
    }

    /**
     * Relawan (pengguna) yang mendaftar.
     * FK: id_pengguna → auth_users.id_pengguna
     */
    public function relawan(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Petugas yang melakukan verifikasi (nullable).
     * FK: id_verifikator → auth_users.id_pengguna
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_verifikator', 'id_pengguna');
    }

    /**
     * Petugas yang melakukan penyaringan/screening (nullable).
     * FK: id_penyaring → auth_users.id_pengguna
     */
    public function penyaring(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_penyaring', 'id_pengguna');
    }

    /**
     * Penugasan lapangan (jika pendaftaran sudah di-assign).
     * FK masuk: relawan_penugasan.id_pendaftaran (CASCADE)
     */
    public function penugasan(): HasOne
    {
        return $this->hasOne(RelawanPenugasan::class, 'id_pendaftaran', 'id_pendaftaran');
    }

    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    /**
     * Filter berdasarkan status pendaftaran.
     */
    public function scopeDenganStatus($query, string $status)
    {
        return $query->where('status_pendaftaran', $status);
    }

    /**
     * Pendaftaran milik pengguna tertentu.
     */
    public function scopeByRelawan($query, int $idPengguna)
    {
        return $query->where('id_pengguna', $idPengguna);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Apakah status pendaftaran ini sudah terminal (tidak bisa berubah lagi)?
     */
    public function isStatusTerminal(): bool
    {
        return in_array($this->status_pendaftaran, ['selesai', 'ditolak']);
    }

    /**
     * Apakah relawan sudah diterima (diterima atau ditugaskan)?
     */
    public function isDiterima(): bool
    {
        return in_array($this->status_pendaftaran, ['diterima', 'ditugaskan', 'selesai']);
    }

    /**
     * Label status pendaftaran yang ramah pembaca.
     */
    public function labelStatus(): string
    {
        return match ($this->status_pendaftaran) {
            'dibuka'     => 'Menunggu Seleksi',
            'seleksi'    => 'Dalam Seleksi',
            'diterima'   => 'Diterima',
            'ditugaskan' => 'Ditugaskan',
            'selesai'    => 'Selesai',
            'ditolak'    => 'Ditolak',
            default      => ucfirst((string) $this->status_pendaftaran),
        };
    }
}
