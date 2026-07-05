<?php

namespace App\Models\Histori;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriPetaRisikoBencana extends Model
{
    use HasFactory;

    protected $table = 'histori_peta_risiko_bencana';
    protected $primaryKey = 'id_peta';
    const CREATED_AT = 'dihitung_pada';
    const UPDATED_AT = null;

    protected $guarded = ['id_peta'];
}
