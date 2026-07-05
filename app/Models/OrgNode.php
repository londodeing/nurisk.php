<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrgNode extends Model
{
    protected $table = 'org_nodes';
        protected $primaryKey = 'id';
protected $fillable = ['institution_id', 'structure_level_id', 'territory_code', 'name', 'status'];
    public function institution() { return $this->belongsTo(OrgInstitution::class, 'institution_id'); }
    public function structureLevel() { return $this->belongsTo(OrgStructureLevel::class, 'structure_level_id'); }
    public function positions() { return $this->belongsToMany(OrgPosition::class, 'org_node_positions', 'node_id', 'position_id'); }
}
