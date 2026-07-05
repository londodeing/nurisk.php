<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrgStructureLevel extends Model
{
    protected $table = 'org_structure_levels';
        protected $primaryKey = 'id';
protected $fillable = ['name', 'weight'];
}
