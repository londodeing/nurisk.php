<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperasiEskalasi extends Model
{
    protected $table = 'operasi_eskalasi';
    protected $primaryKey = 'id_eskalasi';
    public $timestamps = false;

    protected $fillable = [
        'id_insiden',
        'id_pleno',
        'level_sebelumnya',
        'level_baru',
        'alasan_eskalasi',
    ];

    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    public function pleno(): BelongsTo
    {
        return $this->belongsTo(OperasiPleno::class, 'id_pleno', 'id_pleno');
    }
}
