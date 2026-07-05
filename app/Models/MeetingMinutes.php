<?php

namespace App\Models;

use App\Traits\HasGovernanceFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingMinutes extends Model
{
    use HasGovernanceFields;

    protected $table = 'meeting_minutes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'meeting_id',
        'content_snapshot',
        'summary',
        'prepared_by_mandate_id',
        'reviewed_by_mandate_id',
        'approved_by_mandate_id',
        'status',
        'document_hash',
        'file_path',
        'approved_at',
        'locked_at',
        'node_id',
        'territory_id',
        'created_by_mandate_id',
        'updated_by_mandate_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_REVIEW = 'review';
    const STATUS_APPROVED = 'approved';
    const STATUS_LOCKED = 'locked';

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(MeetingSession::class, 'meeting_id');
    }

    public function preparedByMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'prepared_by_mandate_id');
    }

    public function reviewedByMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'reviewed_by_mandate_id');
    }

    public function approvedByMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'approved_by_mandate_id');
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
