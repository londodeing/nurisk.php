<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJabatanPenandatangan extends Model
{
    protected $table = 'master_jabatan_penandatangan';
    protected $primaryKey = 'id_jabatan';
    public $timestamps = false;

    protected $fillable = [
        'kode_jabatan',
        'nama_jabatan',
        'urutan_hierarki',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeOrderByHierarki($query)
    {
        return $query->orderBy('urutan_hierarki');
    }
}
