<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncTombstone extends Model
{
    use HasFactory;

    protected $table = 'sync_tombstones';
    protected $primaryKey = 'id_tombstone';

    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'uuid_entity',
        'deleted_at',
        'deleted_by',
        'alasan_hapus',
        'cursor_value',
        'id_pcnu',
        'scope_type',
        'scope_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'cursor_value' => 'integer',
        'id_pcnu' => 'integer',
        'scope_id' => 'integer',
    ];

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'deleted_by', 'id_pengguna');
    }
}
