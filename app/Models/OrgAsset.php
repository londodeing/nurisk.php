<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class OrgAsset extends Model
{
    protected $table = 'org_assets';
        protected $primaryKey = 'id';
use HasFactory;
    protected $fillable = [
        'asset_code',
        'name',
        'legal_owner_name',
        'category',
        'sub_category',
        'owner_node_id',
        'custodian_node_id',
        'affiliated_node_id',
        'home_territory_code',
        'current_territory_code',
        'status',
        'readiness',
        'verification_status',
        'metadata',
    ];
    protected $casts = [
        'metadata' => 'array',
    ];
    public function ownerNode()
    {
        return $this->belongsTo(OrgNode::class, 'owner_node_id');
    }
    public function custodianNode()
    {
        return $this->belongsTo(OrgNode::class, 'custodian_node_id');
    }
}
