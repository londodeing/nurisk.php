<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrgPositionFunction extends Model
{
    protected $table = 'org_position_functions';
        protected $primaryKey = 'id';
protected $fillable = ['node_position_id', 'function_id'];
}
