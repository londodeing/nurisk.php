<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MeetingVote — IMMUTABLE
 *
 * Setelah di-insert, TIDAK BOLEH di-update atau di-delete.
 * Menyimpan snapshot posisi dan SK voter saat voting.
 */
class MeetingVote extends Model
{
    use HasUuids;

    protected $table = 'meeting_votes';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // Explicitly set created_at only
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'meeting_id',
        'agenda_id',
        'voter_mandate_id',
        'vote',
        'reason',
        'voted_at',
        'voter_position_snapshot',
        'voter_sk_snapshot',
        'node_id',
        'territory_id',
        'created_by_mandate_id',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(MeetingSession::class, 'meeting_id');
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(MeetingAgenda::class, 'agenda_id');
    }

    public function voterMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'voter_mandate_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(OrgNode::class, 'node_id');
    }

    public function isApprove(): bool
    {
        return $this->vote === 'approve';
    }

    public function isReject(): bool
    {
        return $this->vote === 'reject';
    }
}
