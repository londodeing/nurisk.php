<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrgInstitution extends Model
{
    protected $table = 'org_institutions';
        protected $primaryKey = 'id';
protected $fillable = ['name', 'domain'];
    public function nodes() { return $this->hasMany(OrgNode::class, 'institution_id'); }
}
