<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrgAuthority extends Model
{
    protected $table = 'org_authorities';
        protected $primaryKey = 'id';
protected $fillable = ['code', 'domain', 'description'];
    public function functions() { return $this->belongsToMany(OrgGovernanceFunction::class, 'org_function_authorities', 'authority_id', 'function_id'); }
}
