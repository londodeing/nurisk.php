<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenSuratParaf extends Model
{
    protected $table = 'dokumen_surat_paraf';
    protected $primaryKey = 'id_paraf';
    public $timestamps = false;

    protected $fillable = [
        'id_surat',
        'id_pengguna',
        'urutan',
        'status_paraf',
        'catatan',
        'waktu_paraf',
    ];

    protected $casts = [
        'waktu_paraf' => 'datetime',
        'urutan' => 'integer',
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(DokumenSuratUtama::class, 'id_surat', 'id_surat');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function isAktif(): bool
    {
        return $this->status_paraf === 'menunggu';
    }
}
