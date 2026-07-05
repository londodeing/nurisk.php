<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model untuk tabel auth_pengguna_profil
 *
 * Catatan SQL v37 Frozen:
 * - PK: id_pengguna (BigInt Unsigned, NOT incrementing — ini adalah FK ke auth_users)
 * - Tidak ada timestamps (dibuat_pada / diperbarui_pada tidak ada di skema)
 * - UNIQUE: nik, email
 * - FK: id_pengguna → auth_users ON DELETE CASCADE
 * - FK: id_desa_domisili → wilayah_desa ON DELETE SET NULL ON UPDATE CASCADE
 */
class AuthPenggunaProfil extends Model
{
    use HasFactory;
    protected $table = 'auth_pengguna_profil';

    /**
     * Primary key adalah id_pengguna (FK ke auth_users).
     * Bukan auto-increment — nilainya sama dengan auth_users.id_pengguna.
     */
    protected $primaryKey = 'id_pengguna';
    public $incrementing = false;
    protected $keyType = 'int';

    /**
     * Tabel tidak memiliki kolom created_at / updated_at di SQL v37.
     */
    public $timestamps = false;

    protected $fillable = [
        'id_pengguna',
        'nik',
        'nama_lengkap',
        'email',
        'id_desa_domisili',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'tempat_lahir',
        'profesi',
        'pengalaman_kebencanaan',
    ];

    /**
     * Relasi ke AuthUser (pemilik profil).
     * FK: auth_pengguna_profil.id_pengguna → auth_users.id_pengguna (CASCADE)
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relasi ke WilayahDesa (domisili relawan).
     * FK: id_desa_domisili → wilayah_desa.id_desa (SET NULL on delete)
     */
    public function desaDomisili(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'id_desa_domisili', 'id_desa');
    }
}
