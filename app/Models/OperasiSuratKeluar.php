<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperasiSuratKeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_surat_keluar';
    protected $primaryKey = 'id_surat';
    
    public $timestamps = true;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null; // Update via custom query atau ditangani manual
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'id_insiden',
        'id_jenis_surat',
        'nomor_surat_resmi',
        'perihal',
        'tgl_terbit',
        'id_pengguna_ttd',
        'id_jabatan_ttd',
        'isi_surat_snapshot',
        'file_pdf_path',
        'status_surat',
    ];

    protected $casts = [
        'tgl_terbit' => 'date',
        'dibuat_pada' => 'datetime',
        'dihapus_pada' => 'datetime',
    ];

    public function insiden(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna_ttd', 'id_pengguna');
    }
}
