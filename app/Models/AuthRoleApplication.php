<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AuthRole;

class AuthRoleApplication extends Model
{
    protected $table = 'auth_role_applications';
    protected $primaryKey = 'id_application';
    public $timestamps = false;

    protected $fillable = [
        'id_pengguna',
        'id_peran_diminta',
        'status_aplikasi',
        'waktu_pengajuan',
        'waktu_diproses',
        'id_approver',
        'catatan',
    ];

    public function pemohon()
    {
        return $this->belongsTo(AuthUser::class, 'id_pengguna', 'id_pengguna');
    }

    public function approver()
    {
        return $this->belongsTo(AuthUser::class, 'id_approver', 'id_pengguna');
    }

    public function peranDiminta()
    {
        return $this->belongsTo(AuthRole::class, 'id_peran_diminta', 'id_peran');
    }
}
