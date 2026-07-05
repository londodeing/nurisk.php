<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Scopes\ScopedByPcnu;

class LogistikGudang extends Model
{
    use HasFactory, SoftDeletes, ScopedByPcnu;

    protected $table = 'logistik_gudang';
    protected $primaryKey = 'id_gudang';
    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'nama_gudang',
        'id_pcnu',
        'alamat_fisik',
        'kapasitas_kubikasi',
        'pj_gudang',
        'status_aktif'
    ];

    public function pcnu() {
        return $this->belongsTo(OrganisasiPcnu::class, 'id_pcnu', 'id_pcnu');
    }

    public function pj() {
        return $this->belongsTo(AuthUser::class, 'pj_gudang', 'id_pengguna');
    }

    public function stoks() {
        return $this->hasMany(LogistikStok::class, 'id_gudang', 'id_gudang');
    }
}
