<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OperasiMobilisasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operasi_mobilisasi';
    protected $primaryKey = 'id_mobilisasi';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    const DELETED_AT = 'dihapus_pada';

    // protected $with = ['insiden']; // removed — eager-load explicitly where needed

    protected $fillable = [
        'uuid_mobilisasi',
        'id_insiden',
        'id_pengguna',
        'jenis_mobilisasi',
        'status_mobilisasi',
        'lokasi_asal',
        'lokasi_tujuan',
        'waktu_berangkat',
        'waktu_tiba',
        'catatan',
        'sync_version',
        'created_by',
        'updated_by',
        'deleted_by',
        'alasan_hapus',
    ];

    protected $casts = [
        'waktu_berangkat' => 'datetime',
        'waktu_tiba' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid_mobilisasi)) {
                $model->uuid_mobilisasi = (string) Str::uuid();
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

    public function pengguna()
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function pembuat()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id_pengguna');
    }
}
