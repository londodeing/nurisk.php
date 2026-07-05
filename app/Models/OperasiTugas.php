<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperasiTugas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_tugas';
    protected $primaryKey = 'id_tugas';
    
    public $timestamps = true;

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null; // Tidak ada diperbarui_pada di audit
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'id_operasi_klaster',
        'id_posaju',
        'ditugaskan_ke',
        'id_surat_perintah',
        'judul_tugas',
        'target_indikator',
        'status_tugas',
        'progres_persen',
    ];

    protected $casts = [
        'progres_persen' => 'float',
        'dibuat_pada' => 'datetime',
        'dihapus_pada' => 'datetime',
    ];

    public function klaster(): BelongsTo
    {
        return $this->belongsTo(OperasiKlaster::class, 'id_operasi_klaster', 'id_klaster_operasi');
    }

    public function posaju(): BelongsTo
    {
        return $this->belongsTo(OperasiPosaju::class, 'id_posaju', 'id_posaju');
    }

    public function pelaksana(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'ditugaskan_ke', 'id_pengguna');
    }

    public function suratPerintah(): BelongsTo
    {
        return $this->belongsTo(OperasiSuratKeluar::class, 'id_surat_perintah', 'id_surat');
    }
}
