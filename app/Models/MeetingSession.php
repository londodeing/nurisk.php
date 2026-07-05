<?php

namespace App\Models;

use App\Traits\HasGovernanceFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * MeetingSession — Rapat Governance
 *
 * Standalone meeting yang TIDAK terikat ke insiden.
 * Menggunakan mandate-based roles (chairperson, secretary, approver).
 *
 * LIFECYCLE:
 * draft → scheduled → invitation → running → voting → decision → minutes → closed
 *                                                                            ↗
 *                                                            cancelled ←←←←←
 */
class MeetingSession extends Model
{
    use HasGovernanceFields;

    protected $table = 'meeting_sessions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'node_id',
        'territory_id',
        'meeting_number',
        'title',
        'meeting_type',
        'scheduled_at',
        'started_at',
        'ended_at',
        'venue',
        'venue_type',
        'quorum_required',
        'quorum_met',
        'chairperson_mandate_id',
        'secretary_mandate_id',
        'approved_by_mandate_id',
        'status',
        'related_incident_id',
        'related_letter_id',
        'created_by_mandate_id',
        'updated_by_mandate_id',
        'document_hash',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'quorum_met' => 'boolean',
    ];

    // ===================================================================
    // STATUS CONSTANTS
    // ===================================================================

    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_INVITATION = 'invitation';
    const STATUS_RUNNING = 'running';
    const STATUS_VOTING = 'voting';
    const STATUS_DECISION = 'decision';
    const STATUS_MINUTES = 'minutes';
    const STATUS_CLOSED = 'closed';
    const STATUS_CANCELLED = 'cancelled';

    const ALLOWED_TRANSITIONS = [
        'draft'      => ['scheduled', 'cancelled'],
        'scheduled'  => ['invitation', 'cancelled'],
        'invitation' => ['running', 'cancelled'],
        'running'    => ['voting'],
        'voting'     => ['decision'],
        'decision'   => ['minutes'],
        'minutes'    => ['closed'],
        'closed'     => [],
        'cancelled'  => [],
    ];

    // ===================================================================
    // RELASI
    // ===================================================================

    public function chairpersonMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'chairperson_mandate_id');
    }

    public function secretaryMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'secretary_mandate_id');
    }

    public function approvedByMandate(): BelongsTo
    {
        return $this->belongsTo(OrgMandate::class, 'approved_by_mandate_id');
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(MeetingAgenda::class, 'meeting_id')->orderBy('sequence');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class, 'meeting_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(MeetingVote::class, 'meeting_id');
    }

    public function minutes(): HasOne
    {
        return $this->hasOne(MeetingMinutes::class, 'meeting_id');
    }

    public function relatedIncident(): BelongsTo
    {
        return $this->belongsTo(OperasiInsiden::class, 'related_incident_id', 'id_insiden');
    }

    // ===================================================================
    // SCOPES
    // ===================================================================

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['closed', 'cancelled']);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['scheduled', 'invitation'])
                     ->where('scheduled_at', '>=', now())
                     ->orderBy('scheduled_at');
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    // ===================================================================
    // HELPERS
    // ===================================================================

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status] ?? []);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_CLOSED, self::STATUS_CANCELLED]);
    }

    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function hasQuorum(): bool
    {
        if ($this->quorum_required <= 0) {
            return true;
        }

        $presentCount = $this->attendees()
            ->where('attendance_status', 'present')
            ->count();

        return $presentCount >= $this->quorum_required;
    }

    public function presentAttendees(): HasMany
    {
        return $this->attendees()->where('attendance_status', 'present');
    }

    public function voters(): HasMany
    {
        return $this->attendees()
            ->where('has_voting_right', true)
            ->where('attendance_status', 'present');
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'scheduled' => 'Dijadwalkan',
            'invitation' => 'Undangan Terkirim',
            'running' => 'Berlangsung',
            'voting' => 'Voting',
            'decision' => 'Keputusan',
            'minutes' => 'Notulensi',
            'closed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
