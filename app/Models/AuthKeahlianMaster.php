<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model untuk tabel auth_keahlian_master
 *
 * Catatan SQL v37 Frozen:
 * - PK: id_keahlian (INT, AUTO_INCREMENT — dikonfirmasi via MODIFY)
 * - UNIQUE: nama_keahlian
 * - Tidak ada timestamps
 * - Tidak ada soft delete
 * - Data seed: 7 keahlian (Medis, Water Rescue, Vertical Rescue, Logistik, Dapur Umum, Psikososial, Komunikasi Radio)
 */
class AuthKeahlianMaster extends Model
{
    protected $table = 'auth_keahlian_master';
    protected $primaryKey = 'id_keahlian';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Tidak ada created_at / updated_at di SQL v37.
     */
    public $timestamps = false;

    protected $fillable = [
        'nama_keahlian',
        'deskripsi',
    ];

    /**
     * Pengguna yang memiliki keahlian ini.
     * M:N via auth_pengguna_keahlian.
     * FK cascade: hapus keahlian master → hapus pivot.
     */
    public function pengguna(): BelongsToMany
    {
        return $this->belongsToMany(
            AuthUser::class,
            'auth_pengguna_keahlian',
            'id_keahlian',
            'id_pengguna'
        );
    }

    /**
     * Kebutuhan relawan yang mensyaratkan keahlian ini.
     * FK: relawan_kebutuhan.id_keahlian_utama → auth_keahlian_master.id_keahlian
     */
    public function kebutuhanRelawan(): HasMany
    {
        return $this->hasMany(RelawanKebutuhan::class, 'id_keahlian_utama', 'id_keahlian');
    }
}
