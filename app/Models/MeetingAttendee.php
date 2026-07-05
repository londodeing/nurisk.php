<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAttendee extends Model
{
    use HasUuids;

    protected $table = 'meeting_attendees';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'meeting_id',
        'mandate_id',
        'role_in_meeting',
        'attendance_status',
        'has_voting_right',
        'confirmed_at',
        'checked_in_at',
        'node_id',
        'territory_id',
        'created_by_mandate_id',
        'updated_by_mandate_id',
    ];

    protected $casts = [
        'has_voting_right' => 'boolean',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(MeetingSession::class, 'meeting_id');
    }

    public function mandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'mandate_id');
    }

    public function createdByMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'created_by_mandate_id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(OrgNode::class, 'node_id');
    }

    public function isPresent(): bool
    {
        return $this->attendance_status === 'present';
    }

    public function canVote(): bool
    {
        return $this->has_voting_right && $this->isPresent();
    }

    public function scopePresent($query)
    {
        return $query->where('attendance_status', 'present');
    }

    public function scopeVoters($query)
    {
        return $query->where('has_voting_right', true)->where('attendance_status', 'present');
    }
}
