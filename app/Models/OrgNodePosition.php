<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrgNodePosition extends Model
{
    protected $table = 'org_node_positions';
        protected $primaryKey = 'id';
protected $fillable = ['node_id', 'position_id'];
    public function node() { return $this->belongsTo(OrgNode::class, 'node_id'); }
    public function position() { return $this->belongsTo(OrgPosition::class, 'position_id'); }
    public function functions() { return $this->belongsToMany(OrgGovernanceFunction::class, 'org_position_functions', 'node_position_id', 'function_id'); }
}
