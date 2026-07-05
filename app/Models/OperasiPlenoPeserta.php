<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperasiPlenoPeserta extends Model
{
    protected $table = 'operasi_pleno_peserta';
    protected $primaryKey = 'id_pleno_peserta';
    public $timestamps = false;

    protected $fillable = [
        'id_pleno',
        'id_pengguna',
        'peran_dalam_rapat',
        'status_kehadiran',
        'hak_suara',
        'status_persetujuan',
        'catatan_peserta',
    ];

    protected $casts = [
        'hak_suara' => 'boolean',
    ];

    public function pleno(): BelongsTo
    {
        return $this->belongsTo(OperasiPleno::class, 'id_pleno', 'id_pleno');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function sudahVoting(): bool
    {
        return !is_null($this->status_persetujuan);
    }
}
