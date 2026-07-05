<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiwayatStatusInsiden extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'riwayat_status_insiden';
    protected $primaryKey = 'id_history';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'id_insiden',
        'status_sebelumnya',
        'status_terbaru',
        'id_pengguna',
        'alasan',
    ];

    protected $casts = [
        'dibuat_pada' => 'datetime',
        'dihapus_pada' => 'datetime',
    ];

    /**
     * Relasi ke model OperasiInsiden
     */
    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    /**
     * Relasi ke model AuthUser
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }
}
