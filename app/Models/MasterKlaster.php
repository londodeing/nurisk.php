<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKlaster extends Model
{
    use HasFactory;

    protected $table = 'master_klaster';
    protected $primaryKey = 'id_master_klaster';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'nama_klaster',
        'deskripsi',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function operasiKlaster()
    {
        return $this->hasMany(OperasiKlaster::class, 'id_master_klaster', 'id_master_klaster');
    }
}
