<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperasiAktivasi extends Model
{
    protected $table = 'operasi_aktivasi';
    protected $primaryKey = 'id_aktivasi';
    public $timestamps = false;

    protected $fillable = [
        'id_insiden',
        'id_komandan',
        'id_surat_tugas',
        'status_darurat',
        'waktu_mulai',
        'waktu_selesai',
    ];

    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    public function komandan(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_komandan', 'id_pengguna');
    }
}
