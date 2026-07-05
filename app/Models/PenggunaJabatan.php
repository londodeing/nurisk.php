<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenggunaJabatan extends Model
{
    use HasFactory;

    protected $table = 'pengguna_jabatan';
    protected $primaryKey = 'id_pengguna_jabatan';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_pengguna',
        'id_jabatan_posisi',
        'tipe_lingkup',
        'id_lingkup',
        'ditugaskan_pada',
        'berakhir_pada',
        'status_aktif',
    ];

    protected $casts = [
        'ditugaskan_pada' => 'datetime',
        'berakhir_pada' => 'datetime',
        'status_aktif' => 'boolean',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
    ];

    /**
     * Relasi ke model AuthUser
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relasi ke model JabatanPosisi
     */
    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(JabatanPosisi::class, 'id_jabatan_posisi', 'id_jabatan_posisi');
    }

    /**
     * Scope untuk memfilter jabatan yang aktif saat ini
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true)
            ->where(function ($q) {
                $q->whereNull('berakhir_pada')
                  ->orWhereDate('berakhir_pada', '>=', now());
            });
    }

    /**
     * Scope untuk memfilter berdasarkan ID Pengguna
     */
    public function scopeByPengguna($query, $idPengguna)
    {
        return $query->where('id_pengguna', $idPengguna);
    }

    /**
     * Scope untuk memfilter berdasarkan lingkup organisasi
     */
    public function scopeByLingkup($query, string $tipe, int $id)
    {
        return $query->where('tipe_lingkup', $tipe)->where('id_lingkup', $id);
    }
}
