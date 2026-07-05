<?php

namespace App\Models\Inventaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisPemeliharaan extends Model
{
    use HasFactory;

    protected $table = 'inventaris_pemeliharaan';
    protected $primaryKey = 'id_pemeliharaan';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $guarded = ['id_pemeliharaan'];
}
