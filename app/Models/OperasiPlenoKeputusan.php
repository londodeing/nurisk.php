<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperasiPlenoKeputusan extends Model
{
    protected $table = 'operasi_pleno_keputusan';
    protected $primaryKey = 'id_keputusan';
    public $timestamps = false;

    protected $fillable = [
        'id_pleno',
        'kategori_objek',
        'jenis_keputusan',
        'tipe_target_keputusan',
        'referensi_tabel',
        'referensi_id',
        'deskripsi_keputusan',
        'status_pelaksanaan',
        'payload_eksekusi',
    ];

    protected $casts = [
        'referensi_id' => 'integer',
        'payload_eksekusi' => 'array',
    ];

    public function pleno(): BelongsTo
    {
        return $this->belongsTo(OperasiPleno::class, 'id_pleno', 'id_pleno');
    }
}
