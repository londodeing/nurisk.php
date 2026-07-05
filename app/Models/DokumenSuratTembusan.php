<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenSuratTembusan extends Model
{
    protected $table = 'dokumen_surat_tembusan';
    protected $primaryKey = 'id_tembusan';
    public $timestamps = false;

    protected $fillable = [
        'id_surat',
        'nama_pihak',
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(DokumenSuratUtama::class, 'id_surat', 'id_surat');
    }
}
