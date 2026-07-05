<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgAuditLog extends Model
{
    protected $table = 'org_audit_logs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'timestamp', 'actor_user_id', 'actor_name', 'actor_position',
        'sk_number', 'mandate_id', 'delegation_id', 'action_type',
        'target_table', 'target_id', 'digital_signature',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'actor_user_id', 'id_pengguna');
    }
}
