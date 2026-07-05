<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LogistikMutasi extends Model
{
    protected $table = 'logistik_mutasi';
    protected $primaryKey = 'id_mutasi';
    const CREATED_AT = 'waktu_mutasi';
    const UPDATED_AT = null;

    protected $fillable = [
        'uuid_mutasi',
        'id_penginput',
        'id_stok',
        'tipe_mutasi',
        'jumlah',
        'asal_tujuan',
        'keterangan'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid_mutasi)) {
                $model->uuid_mutasi = (string) Str::uuid();
            }
        });
    }

    public function stok() {
        return $this->belongsTo(LogistikStok::class, 'id_stok', 'id_stok');
    }

    public function penginput() {
        return $this->belongsTo(AuthUser::class, 'id_penginput', 'id_pengguna');
    }
}
