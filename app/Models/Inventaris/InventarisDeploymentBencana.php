<?php

namespace App\Models\Inventaris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisDeploymentBencana extends Model
{
    use HasFactory;

    protected $table = 'inventaris_deployment_bencana';
    protected $primaryKey = 'id_deployment';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $guarded = ['id_deployment'];
}
