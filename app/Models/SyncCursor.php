<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncCursor extends Model
{
    use HasFactory;

    protected $table = 'sync_cursors';
    protected $primaryKey = 'id_cursor';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'entity_type',
        'uuid_entity',
        'cursor_value',
        'action',
        'id_pcnu',
        'scope_type',
        'scope_id',
    ];

    protected $casts = [
        'cursor_value' => 'integer',
        'id_pcnu' => 'integer',
        'scope_id' => 'integer',
        'dibuat_pada' => 'datetime',
        'diperbarui_pada' => 'datetime',
    ];
}
