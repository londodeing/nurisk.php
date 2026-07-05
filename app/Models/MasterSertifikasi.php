<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSertifikasi extends Model
{
    protected $table = 'master_sertifikasi';
    protected $primaryKey = 'id_sertifikasi';
    public $timestamps = false;

    protected $fillable = ['nama_sertifikasi', 'lembaga_penerbit'];

}
