<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperasiPenugasanHistory extends Model
{
    protected $table = 'operasi_penugasan_history';
    protected $primaryKey = 'id_history';
    public $timestamps = false;

    protected $fillable = [
        'id_penugasan',
        'status_sebelumnya',
        'status_baru',
        'waktu_perubahan',
        'diubah_oleh',
    ];

    public function penugasan()
    {
        return $this->belongsTo(OperasiPenugasan::class, 'id_penugasan', 'id_penugasan');
    }

    public function pengubah()
    {
        return $this->belongsTo(AuthUser::class, 'diubah_oleh', 'id_pengguna');
    }
}
