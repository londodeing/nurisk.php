<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperasiJurnal extends Model
{
    protected $table = 'operasi_jurnal';
    protected $primaryKey = 'id_jurnal';
    public $timestamps = false;

    protected $fillable = [
        'id_insiden',
        'id_pengguna',
        'kategori_event',
        'judul_event',
        'deskripsi_event',
        'id_referensi',
        'tabel_referensi',
    ];

    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }
}
