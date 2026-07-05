<?php

namespace App\Models\Histori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriProbabilitasWilayah extends Model
{
    use HasFactory;

    protected $table = 'histori_probabilitas_wilayah';
    protected $primaryKey = 'id_prob';
    const CREATED_AT = 'dihitung_pada';
    const UPDATED_AT = null;

    protected $guarded = ['id_prob'];
}
