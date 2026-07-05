<?php

namespace App\Models;

use App\Traits\HasGovernanceFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingAgenda extends Model
{
    use HasGovernanceFields;

    protected $table = 'meeting_agendas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'meeting_id',
        'sequence',
        'title',
        'description',
        'duration_minutes',
        'presenter_mandate_id',
        'status',
        'resolution',
        'node_id',
        'territory_id',
        'created_by_mandate_id',
        'updated_by_mandate_id',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_DISCUSSED = 'discussed';
    const STATUS_DEFERRED = 'deferred';
    const STATUS_DECIDED = 'decided';

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(MeetingSession::class, 'meeting_id');
    }

    public function presenterMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'presenter_mandate_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(MeetingVote::class, 'agenda_id');
    }

    public function voteResults(): array
    {
        $votes = $this->votes;
        return [
            'approve' => $votes->where('vote', 'approve')->count(),
            'reject' => $votes->where('vote', 'reject')->count(),
            'abstain' => $votes->where('vote', 'abstain')->count(),
            'total' => $votes->count(),
        ];
    }

    public function isDecided(): bool
    {
        return $this->status === self::STATUS_DECIDED;
    }
}
