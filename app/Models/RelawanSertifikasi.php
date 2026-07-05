<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelawanSertifikasi extends Model
{
    protected $table = 'relawan_sertifikasi';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_pengguna', 'id_sertifikasi', 'tanggal_terbit', 'tanggal_kedaluwarsa'];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function sertifikasi(): BelongsTo
    {
        return $this->belongsTo(MasterSertifikasi::class, 'id_sertifikasi', 'id_sertifikasi');
    }
}
