<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrgGovernanceFunction extends Model
{
    protected $table = 'org_governance_functions';
        protected $primaryKey = 'id';
protected $fillable = ['name', 'description'];
    public function authorities() { return $this->belongsToMany(OrgAuthority::class, 'org_function_authorities', 'function_id', 'authority_id'); }
}
