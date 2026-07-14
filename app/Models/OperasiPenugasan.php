<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OperasiPenugasan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_penugasan';
    protected $primaryKey = 'id_penugasan';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $with = ["insiden"];
    protected $fillable = [
        'uuid_penugasan',
        'id_insiden',
        'id_pengguna',
        'id_klaster_operasi',
        'id_posaju',
        'peran_otoritas',
        'status_penugasan',
        'waktu_mulai',
        'waktu_selesai',
        'ditugaskan_oleh',
        'catatan',
        'sync_version',
        'id_surat_tugas',
        'deleted_by',
        'alasan_hapus',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'waktu_checkin' => 'datetime',
        'waktu_checkout' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid_penugasan)) {
                $model->uuid_penugasan = (string) Str::uuid();
            }
            if (empty($model->sync_version)) {
                $model->sync_version = 1;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty() && !$model->isDirty('sync_version')) {
                $model->sync_version++;
            }
        });
    }

    /**
     * Query Scope: Penugasan Aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status_penugasan', 'aktif');
    }

    public function insiden()
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    public function pengguna()
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function pemberiTugas()
    {
        return $this->belongsTo(AuthUser::class, 'ditugaskan_oleh', 'id_pengguna');
    }

    public function klasterOperasi()
    {
        return $this->belongsTo(OperasiKlaster::class, 'id_klaster_operasi', 'id_klaster_operasi');
    }

    public function posaju()
    {
        return $this->belongsTo(OperasiPosaju::class, 'id_posaju', 'id_posaju');
    }

    public function suratTugas()
    {
        return $this->belongsTo(DokumenSuratUtama::class, 'id_surat_tugas', 'id_surat');
    }
}
