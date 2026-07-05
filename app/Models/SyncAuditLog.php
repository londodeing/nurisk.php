<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncAuditLog extends Model
{
    use HasFactory;

    protected $table = 'sync_audit_logs';
    protected $primaryKey = 'id';
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = null;

    protected $fillable = [
        'device_uuid',
        'request_id',
        'entities_synced',
        'duration_ms',
        'status',
        'scope_type',
        'scope_id',
    ];

    protected $casts = [
        'entities_synced' => 'integer',
        'duration_ms' => 'integer',
        'scope_id' => 'integer',
        'dibuat_pada' => 'datetime',
    ];
}
