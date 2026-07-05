<?php

namespace App\Services\Governance;

use App\Events\Governance\MeetingStatusChanged;
use App\Models\MeetingAgenda;
use App\Models\MeetingAttendee;
use App\Models\MeetingMinutes;
use App\Models\MeetingSession;
use App\Models\MeetingVote;
use App\Models\OrgMandate;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * MeetingLifecycleService
 *
 * State Machine untuk Meeting Governance.
 *
 * LIFECYCLE:
 * draft → scheduled → invitation → running → voting → decision → minutes → closed
 *                                                                            ↗
 *                                                            cancelled ←←←←←
 *
 * PRINSIP:
 * - Semua transisi melalui service ini (BUKAN direct update di controller)
 * - Setiap transisi merekam audit trail
 * - Quorum check pada saat open meeting
 * - Vote immutable setelah cast
 */
class MeetingLifecycleService
{
    public function __construct(
        private MandateResolverService $mandateResolver
    ) {}

    // ===================================================================
    // LIFECYCLE TRANSITIONS
    // ===================================================================

    /**
     * Buat meeting baru.
     */
    public function createMeeting(array $data, OrgMandate $creatorMandate): MeetingSession
    {
        return DB::transaction(function () use ($data, $creatorMandate) {
            $meeting = MeetingSession::create(array_merge($data, [
                'status' => MeetingSession::STATUS_DRAFT,
                'created_by_mandate_id' => $creatorMandate->id,
                'node_id' => $data['node_id'] ?? $creatorMandate->nodePosition?->node_id,
                'territory_id' => $data['territory_id']
                    ?? $creatorMandate->nodePosition?->node?->territory_code,
            ]));

            // Auto-add chairperson & secretary sebagai attendee
            $this->addAttendee($meeting, $data['chairperson_mandate_id'], 'chairperson', true, $creatorMandate);
            $this->addAttendee($meeting, $data['secretary_mandate_id'], 'secretary', false, $creatorMandate);

            return $meeting->fresh();
        });
    }

    /**
     * Draft → Scheduled: Set jadwal dan tempat.
     */
    public function schedule(MeetingSession $meeting, array $scheduleData, OrgMandate $actorMandate): MeetingSession
    {
        $this->assertTransition($meeting, MeetingSession::STATUS_SCHEDULED);

        return DB::transaction(function () use ($meeting, $scheduleData, $actorMandate) {
            $previousStatus = $meeting->status;

            $meeting->update(array_merge($scheduleData, [
                'status' => MeetingSession::STATUS_SCHEDULED,
                'updated_by_mandate_id' => $actorMandate->id,
            ]));

            event(new MeetingStatusChanged($meeting, $previousStatus, $actorMandate));

            return $meeting->fresh();
        });
    }

    /**
     * Scheduled → Invitation: Kirim undangan ke peserta.
     */
    public function sendInvitations(MeetingSession $meeting, OrgMandate $actorMandate): MeetingSession
    {
        $this->assertTransition($meeting, MeetingSession::STATUS_INVITATION);

        if ($meeting->attendees()->count() < 2) {
            throw new RuntimeException('Minimal 2 peserta harus ditambahkan sebelum mengirim undangan.');
        }

        return DB::transaction(function () use ($meeting, $actorMandate) {
            $previousStatus = $meeting->status;

            $meeting->update([
                'status' => MeetingSession::STATUS_INVITATION,
                'updated_by_mandate_id' => $actorMandate->id,
            ]);

            // Update semua attendee yang masih 'invited' (tetap)
            event(new MeetingStatusChanged($meeting, $previousStatus, $actorMandate));

            return $meeting->fresh();
        });
    }

    /**
     * Invitation → Running: Buka rapat (quorum check).
     */
    public function openMeeting(MeetingSession $meeting, OrgMandate $actorMandate): MeetingSession
    {
        $this->assertTransition($meeting, MeetingSession::STATUS_RUNNING);

        return DB::transaction(function () use ($meeting, $actorMandate) {
            $previousStatus = $meeting->status;

            $quorumMet = $meeting->hasQuorum();

            $meeting->update([
                'status' => MeetingSession::STATUS_RUNNING,
                'started_at' => now(),
                'quorum_met' => $quorumMet,
                'updated_by_mandate_id' => $actorMandate->id,
            ]);

            event(new MeetingStatusChanged($meeting, $previousStatus, $actorMandate));

            return $meeting->fresh();
        });
    }

    /**
     * Running → Voting: Mulai sesi voting.
     */
    public function startVoting(MeetingSession $meeting, OrgMandate $actorMandate): MeetingSession
    {
        $this->assertTransition($meeting, MeetingSession::STATUS_VOTING);

        if ($meeting->agendas()->count() === 0) {
            throw new RuntimeException('Minimal 1 agenda harus ada sebelum memulai voting.');
        }

        return DB::transaction(function () use ($meeting, $actorMandate) {
            $previousStatus = $meeting->status;

            $meeting->update([
                'status' => MeetingSession::STATUS_VOTING,
                'updated_by_mandate_id' => $actorMandate->id,
            ]);

            event(new MeetingStatusChanged($meeting, $previousStatus, $actorMandate));

            return $meeting->fresh();
        });
    }

    /**
     * Cast vote pada agenda tertentu (IMMUTABLE).
     */
    public function castVote(
        MeetingSession $meeting,
        MeetingAgenda $agenda,
        OrgMandate $voterMandate,
        string $vote,
        ?string $reason = null
    ): MeetingVote {
        if ($meeting->status !== MeetingSession::STATUS_VOTING) {
            throw new RuntimeException('Voting hanya dapat dilakukan saat status meeting = voting.');
        }

        // Pastikan voter adalah attendee dengan hak suara
        $attendee = $meeting->attendees()
            ->where('mandate_id', $voterMandate->id)
            ->where('has_voting_right', true)
            ->where('attendance_status', 'present')
            ->first();

        if (!$attendee) {
            throw new RuntimeException('Anda bukan peserta dengan hak suara di rapat ini.');
        }

        // Check belum pernah vote di agenda ini
        $existingVote = MeetingVote::where('agenda_id', $agenda->id)
            ->where('voter_mandate_id', $voterMandate->id)
            ->exists();

        if ($existingVote) {
            throw new RuntimeException('Anda sudah memberikan suara pada agenda ini. Vote bersifat immutable.');
        }

        // Generate immutable snapshot
        $snapshot = $this->mandateResolver->getLegalSnapshot($voterMandate);

        return MeetingVote::create([
            'meeting_id' => $meeting->id,
            'agenda_id' => $agenda->id,
            'voter_mandate_id' => $voterMandate->id,
            'vote' => $vote,
            'reason' => $reason,
            'voted_at' => now(),
            'voter_position_snapshot' => $snapshot['position_name'] . ' ' . $snapshot['node_name'],
            'voter_sk_snapshot' => $snapshot['sk_number'],
            'node_id' => $meeting->node_id,
            'territory_id' => $meeting->territory_id,
            'created_by_mandate_id' => $voterMandate->id,
        ]);
    }

    /**
     * Voting → Decision: Tutup voting, tally results.
     */
    public function closeVoting(MeetingSession $meeting, OrgMandate $actorMandate): MeetingSession
    {
        $this->assertTransition($meeting, MeetingSession::STATUS_DECISION);

        return DB::transaction(function () use ($meeting, $actorMandate) {
            $previousStatus = $meeting->status;

            // Mark semua agenda yang punya votes sebagai 'decided'
            $meeting->agendas()->whereHas('votes')->update(['status' => MeetingAgenda::STATUS_DECIDED]);

            $meeting->update([
                'status' => MeetingSession::STATUS_DECISION,
                'approved_by_mandate_id' => $actorMandate->id,
                'updated_by_mandate_id' => $actorMandate->id,
            ]);

            event(new MeetingStatusChanged($meeting, $previousStatus, $actorMandate));

            return $meeting->fresh();
        });
    }

    /**
     * Decision → Minutes: Generate notulensi.
     */
    public function generateMinutes(
        MeetingSession $meeting,
        OrgMandate $actorMandate,
        string $content,
        ?string $summary = null
    ): MeetingMinutes {
        $this->assertTransition($meeting, MeetingSession::STATUS_MINUTES);

        return DB::transaction(function () use ($meeting, $actorMandate, $content, $summary) {
            $previousStatus = $meeting->status;

            $minutes = MeetingMinutes::create([
                'meeting_id' => $meeting->id,
                'content_snapshot' => $content,
                'summary' => $summary,
                'prepared_by_mandate_id' => $actorMandate->id,
                'status' => MeetingMinutes::STATUS_DRAFT,
                'node_id' => $meeting->node_id,
                'territory_id' => $meeting->territory_id,
                'created_by_mandate_id' => $actorMandate->id,
            ]);

            $meeting->update([
                'status' => MeetingSession::STATUS_MINUTES,
                'updated_by_mandate_id' => $actorMandate->id,
            ]);

            event(new MeetingStatusChanged($meeting, $previousStatus, $actorMandate));

            return $minutes;
        });
    }

    /**
     * Minutes → Closed: Finalisasi meeting.
     */
    public function closeMeeting(MeetingSession $meeting, OrgMandate $actorMandate): MeetingSession
    {
        $this->assertTransition($meeting, MeetingSession::STATUS_CLOSED);

        return DB::transaction(function () use ($meeting, $actorMandate) {
            $previousStatus = $meeting->status;

            // Lock minutes jika ada
            $minutes = $meeting->minutes;
            if ($minutes && !$minutes->isLocked()) {
                $minutes->update([
                    'status' => MeetingMinutes::STATUS_LOCKED,
                    'locked_at' => now(),
                    'approved_by_mandate_id' => $actorMandate->id,
                    'updated_by_mandate_id' => $actorMandate->id,
                ]);
            }

            // Generate document hash
            $hash = hash('sha256', json_encode([
                'meeting_id' => $meeting->id,
                'title' => $meeting->title,
                'agendas' => $meeting->agendas()->with('votes')->get()->toArray(),
                'attendees' => $meeting->attendees->toArray(),
                'minutes' => $minutes?->content_snapshot,
                'closed_at' => now()->toIso8601String(),
            ]));

            $meeting->update([
                'status' => MeetingSession::STATUS_CLOSED,
                'ended_at' => now(),
                'document_hash' => $hash,
                'updated_by_mandate_id' => $actorMandate->id,
            ]);

            event(new MeetingStatusChanged($meeting, $previousStatus, $actorMandate));

            return $meeting->fresh();
        });
    }

    /**
     * Cancel meeting (hanya dari draft/scheduled/invitation).
     */
    public function cancelMeeting(MeetingSession $meeting, OrgMandate $actorMandate): MeetingSession
    {
        $this->assertTransition($meeting, MeetingSession::STATUS_CANCELLED);

        return DB::transaction(function () use ($meeting, $actorMandate) {
            $previousStatus = $meeting->status;

            $meeting->update([
                'status' => MeetingSession::STATUS_CANCELLED,
                'updated_by_mandate_id' => $actorMandate->id,
            ]);

            event(new MeetingStatusChanged($meeting, $previousStatus, $actorMandate));

            return $meeting->fresh();
        });
    }

    // ===================================================================
    // AGENDA & ATTENDEE MANAGEMENT
    // ===================================================================

    /**
     * Tambah agenda ke meeting (hanya saat draft/scheduled).
     */
    public function addAgenda(MeetingSession $meeting, array $data, OrgMandate $actorMandate): MeetingAgenda
    {
        if (!in_array($meeting->status, ['draft', 'scheduled', 'invitation', 'running'])) {
            throw new RuntimeException('Agenda hanya bisa ditambahkan sebelum voting.');
        }

        $maxSequence = $meeting->agendas()->max('sequence') ?? 0;

        return MeetingAgenda::create(array_merge($data, [
            'meeting_id' => $meeting->id,
            'sequence' => $data['sequence'] ?? ($maxSequence + 1),
            'status' => MeetingAgenda::STATUS_PENDING,
            'node_id' => $meeting->node_id,
            'territory_id' => $meeting->territory_id,
            'created_by_mandate_id' => $actorMandate->id,
        ]));
    }

    /**
     * Tambah attendee ke meeting.
     */
    public function addAttendee(
        MeetingSession $meeting,
        int $mandateId,
        string $role = 'voter',
        bool $hasVotingRight = false,
        OrgMandate $actorMandate = null
    ): MeetingAttendee {
        if ($meeting->isClosed()) {
            throw new RuntimeException('Tidak bisa menambahkan peserta ke meeting yang sudah selesai.');
        }

        return MeetingAttendee::create([
            'meeting_id' => $meeting->id,
            'mandate_id' => $mandateId,
            'role_in_meeting' => $role,
            'attendance_status' => 'invited',
            'has_voting_right' => $hasVotingRight,
            'node_id' => $meeting->node_id,
            'territory_id' => $meeting->territory_id,
            'created_by_mandate_id' => $actorMandate?->id ?? $meeting->created_by_mandate_id,
        ]);
    }

    /**
     * Check-in attendee (mark as present).
     */
    public function checkInAttendee(MeetingAttendee $attendee, OrgMandate $actorMandate): MeetingAttendee
    {
        $attendee->update([
            'attendance_status' => 'present',
            'checked_in_at' => now(),
            'updated_by_mandate_id' => $actorMandate->id,
        ]);

        return $attendee->fresh();
    }

    // ===================================================================
    // PRIVATE
    // ===================================================================

    /**
     * Validasi transisi status.
     */
    private function assertTransition(MeetingSession $meeting, string $targetStatus): void
    {
        if (!$meeting->canTransitionTo($targetStatus)) {
            throw new RuntimeException(
                "Transisi ilegal: [{$meeting->status}] → [{$targetStatus}]. " .
                "Transisi yang diizinkan dari [{$meeting->status}]: " .
                implode(', ', MeetingSession::ALLOWED_TRANSITIONS[$meeting->status] ?? [])
            );
        }
    }
}
