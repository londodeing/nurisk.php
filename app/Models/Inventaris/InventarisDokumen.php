<?php

namespace App\Models\Inventaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisDokumen extends Model
{
    use HasFactory;

    protected $table = 'inventaris_dokumen';
    protected $primaryKey = 'id_dokumen';
    const CREATED_AT = 'diunggah_pada';
    const UPDATED_AT = null;

    protected $guarded = ['id_dokumen'];
}
