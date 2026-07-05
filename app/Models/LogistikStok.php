<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogistikStok extends Model
{
    protected $table = 'logistik_stok';
    protected $primaryKey = 'id_stok';
    const CREATED_AT = null;
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_posaju',
        'id_gudang',
        'id_katalog',
        'jumlah_tersedia'
    ];

    public function mutasis() {
        return $this->hasMany(LogistikMutasi::class, 'id_stok', 'id_stok');
    }

    public function gudang() {
        return $this->belongsTo(LogistikGudang::class, 'id_gudang', 'id_gudang');
    }

    public function posaju() {
        return $this->belongsTo(OperasiPosaju::class, 'id_posaju', 'id_posaju');
    }

    public function katalog() {
        return $this->belongsTo(LogistikBarangKatalog::class, 'id_katalog', 'id_katalog');
    }
}
