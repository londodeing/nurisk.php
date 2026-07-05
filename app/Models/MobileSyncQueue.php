<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class MobileSyncQueue extends Model
{
    protected $table = 'mobile_sync_queues';
        protected $primaryKey = 'id';
use HasFactory;
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';
    protected $fillable = [
        'request_id',
        'device_uuid',
        'id_pcnu',
        'scope_type',
        'scope_id',
        'payload',
        'response',
        'status',
        'processed_at',
    ];
    protected $casts = [
        'id_pcnu' => 'integer',
        'scope_id' => 'integer',
        'payload' => 'array',
        'response' => 'array',
        'processed_at' => 'datetime',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
    ];
}
