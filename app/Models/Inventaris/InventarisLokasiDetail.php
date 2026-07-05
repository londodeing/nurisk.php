<?php

namespace App\Models\Inventaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisLokasiDetail extends Model
{
    use HasFactory;

    protected $table = 'inventaris_lokasi_detail';
    protected $primaryKey = 'id_lokasi';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $guarded = ['id_lokasi'];
}
