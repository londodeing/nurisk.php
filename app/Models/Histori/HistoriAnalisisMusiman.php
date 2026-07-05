<?php

namespace App\Models\Histori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriAnalisisMusiman extends Model
{
    use HasFactory;

    protected $table = 'histori_analisis_musiman';
    protected $primaryKey = 'id_analisis';
    const CREATED_AT = 'dihitung_pada';
    const UPDATED_AT = null;

    protected $guarded = ['id_analisis'];
}
