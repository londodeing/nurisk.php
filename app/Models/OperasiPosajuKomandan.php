<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperasiPosajuKomandan extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'operasi_posaju_komandan';
    protected $primaryKey = 'id_komandan';
    public $timestamps = true;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'id_posaju',
        'id_pengguna',
        'id_pleno_keputusan',
        'waktu_mulai_tugas',
        'waktu_selesai_tugas',
    ];

    protected $casts = [
        'waktu_mulai_tugas' => 'datetime',
        'waktu_selesai_tugas' => 'datetime',
        'dibuat_pada' => 'datetime',
        'dihapus_pada' => 'datetime',
    ];

    public function posaju(): BelongsTo
    {
        return $this->belongsTo(OperasiPosaju::class, 'id_posaju', 'id_posaju');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function keputusanPleno(): BelongsTo
    {
        return $this->belongsTo(OperasiPlenoKeputusan::class, 'id_pleno_keputusan', 'id_keputusan');
    }

    public function isAktif(): bool
    {
        return $this->waktu_selesai_tugas === null;
    }
}
