<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class SyncConflict extends Model
{
    protected $table = 'sync_conflicts';
        protected $primaryKey = 'id';
use HasFactory;
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;
    protected $fillable = [
        'device_uuid',
        'entity_type',
        'uuid_entity',
        'id_pcnu',
        'client_version',
        'server_version',
        'client_data',
        'server_data',
        'resolved_at',
        'scope_type',
        'scope_id',
    ];
    protected $casts = [
        'client_version' => 'integer',
        'server_version' => 'integer',
        'id_pcnu' => 'integer',
        'scope_id' => 'integer',
        'client_data' => 'array',
        'server_data' => 'array',
        'resolved_at' => 'datetime',
        'dibuat_pada' => 'datetime',
    ];
}
