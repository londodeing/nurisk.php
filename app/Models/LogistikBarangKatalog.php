<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogistikBarangKatalog extends Model
{
    protected $table = 'logistik_barang_katalog';
    protected $primaryKey = 'id_katalog';
    public $timestamps = false;

    protected $fillable = [
        'id_kategori',
        'id_satuan',
        'nama_barang_standar'
    ];

    public function kategori() {
        return $this->belongsTo(LogistikKategori::class, 'id_kategori', 'id_kategori');
    }
}
