<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterSuratTemplate extends Model
{
    protected $table = 'master_surat_template';
    protected $primaryKey = 'id_template';
    public $timestamps = false;

    protected $fillable = [
        'id_jenis_surat',
        'nama_template',
        'isi_template',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(MasterSuratJenis::class, 'id_jenis_surat', 'id_jenis_surat');
    }
}
