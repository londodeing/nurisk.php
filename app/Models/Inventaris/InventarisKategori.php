<?php

namespace App\Models\Inventaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisKategori extends Model
{
    use HasFactory;

    protected $table = 'inventaris_kategori';
    protected $primaryKey = 'id_kategori';
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $guarded = ['id_kategori'];
}
