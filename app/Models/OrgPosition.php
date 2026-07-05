<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgPosition extends Model
{
    protected $table = 'org_positions';
    protected $primaryKey = 'id';

    protected $fillable = ['name', 'level'];
}
