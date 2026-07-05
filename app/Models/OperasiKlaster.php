<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OperasiKlaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_klaster';
    protected $primaryKey = 'id_klaster_operasi';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    protected $fillable = [
        'uuid_klaster_operasi',
        'id_insiden',
        'id_master_klaster',
        'status_klaster',
        'prioritas',
        'target_cakupan',
        'catatan',
        'waktu_aktivasi',
        'waktu_ditutup',
        'id_pembuat',
        'progres_persen',
        'dibutuhkan',
        'indikator_keberhasilan',
        'sync_version',
        'deleted_by',
        'alasan_hapus',
    ];

    protected $casts = [
        'waktu_aktivasi' => 'datetime',
        'waktu_ditutup' => 'datetime',
        'progres_persen' => 'float',
        'dibutuhkan' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid_klaster_operasi)) {
                $model->uuid_klaster_operasi = (string) Str::uuid();
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

    public function insiden()
    {
        return $this->belongsTo(OperasiInsiden::class, 'id_insiden', 'id_insiden');
    }

    public function masterKlaster()
    {
        return $this->belongsTo(MasterKlaster::class, 'id_master_klaster', 'id_master_klaster');
    }

    public function pembuat()
    {
        return $this->belongsTo(AuthUser::class, 'id_pembuat', 'id_pengguna');
    }

    public function penugasan()
    {
        return $this->hasMany(OperasiPenugasan::class, 'id_klaster_operasi', 'id_klaster_operasi');
    }

    public function tugas()
    {
        return $this->hasMany(OperasiTugas::class, 'id_operasi_klaster', 'id_klaster_operasi');
    }
}
