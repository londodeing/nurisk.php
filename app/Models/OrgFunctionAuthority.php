<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrgFunctionAuthority extends Model
{
    protected $table = 'org_function_authorities';
        protected $primaryKey = 'id';
protected $fillable = ['function_id', 'authority_id'];
}
