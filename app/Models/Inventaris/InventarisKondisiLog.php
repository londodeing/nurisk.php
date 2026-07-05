<?php

namespace App\Models\Inventaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisKondisiLog extends Model
{
    use HasFactory;

    protected $table = 'inventaris_kondisi_log';
    protected $primaryKey = 'id_log';
    const CREATED_AT = 'dicatat_pada';
    const UPDATED_AT = null;

    protected $guarded = ['id_log'];
}
