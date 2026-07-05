<?php

namespace App\Models\Histori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriIndikatorRisikoWilayah extends Model
{
    use HasFactory;

    protected $table = 'histori_indikator_risiko_wilayah';
    protected $primaryKey = 'id_indikator';
    const CREATED_AT = null;
    const UPDATED_AT = 'diperbarui_pada';

    protected $guarded = ['id_indikator'];
}
