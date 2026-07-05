<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogistikKategori extends Model
{
    protected $table = 'logistik_kategori';
    protected $primaryKey = 'id_kategori';
    public $timestamps = false;

    protected $fillable = [
        'nama_kategori'
    ];
}
