<?php

namespace App\Models\Inventaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisJenis extends Model
{
    use HasFactory;

    protected $table = 'inventaris_jenis';
    protected $primaryKey = 'id_jenis';
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $guarded = ['id_jenis'];
}
