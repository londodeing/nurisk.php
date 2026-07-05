<?php

namespace App\Policies\Governance;

use App\Models\AuthUser;
use App\Models\MeetingSession;

/**
 * MeetingPolicy — Mandate-Based Access Control
 *
 * Semua authorization berdasarkan Mandate + Authority + Territory.
 * BUKAN berdasarkan role.
 */
class MeetingPolicy extends GovernanceBasePolicy
{
    public function viewAny(AuthUser $user): bool
    {
        return $this->mandateHasAuthority($user, 'view_meeting');
    }

    public function view(AuthUser $user, MeetingSession $meeting): bool
    {
        if (!$this->mandateHasAuthority($user, 'view_meeting')) {
            return false;
        }

        return $this->isInTerritory($user, $meeting->territory_id);
    }

    public function create(AuthUser $user): bool
    {
        return $this->mandateHasAuthority($user, 'create_meeting');
    }

    public function update(AuthUser $user, MeetingSession $meeting): bool
    {
        if ($meeting->isClosed()) {
            return false;
        }

        if (!$this->mandateHasAuthority($user, 'update_meeting')) {
            return false;
        }

        return $this->isInTerritory($user, $meeting->territory_id);
    }

    public function schedule(AuthUser $user, MeetingSession $meeting): bool
    {
        if (!$meeting->isDraft()) {
            return false;
        }

        return $this->mandateHasAuthorityOnNode($user, 'schedule_meeting', $meeting->node_id);
    }

    public function invite(AuthUser $user, MeetingSession $meeting): bool
    {
        return $this->mandateHasAuthorityOnNode($user, 'invite_meeting', $meeting->node_id);
    }

    public function open(AuthUser $user, MeetingSession $meeting): bool
    {
        return $this->mandateHasAuthorityOnNode($user, 'chair_meeting', $meeting->node_id);
    }

    public function vote(AuthUser $user, MeetingSession $meeting): bool
    {
        if ($meeting->status !== MeetingSession::STATUS_VOTING) {
            return false;
        }

        // Voter harus attendee dengan hak suara
        $mandate = $this->getPrimaryMandate($user);
        if (!$mandate) {
            return false;
        }

        return $meeting->attendees()
            ->where('mandate_id', $mandate->id)
            ->where('has_voting_right', true)
            ->where('attendance_status', 'present')
            ->exists();
    }

    public function close(AuthUser $user, MeetingSession $meeting): bool
    {
        return $this->mandateHasAuthorityOnNode($user, 'chair_meeting', $meeting->node_id);
    }

    public function cancel(AuthUser $user, MeetingSession $meeting): bool
    {
        if (!$meeting->canTransitionTo(MeetingSession::STATUS_CANCELLED)) {
            return false;
        }

        return $this->mandateHasAuthorityOnNode($user, 'cancel_meeting', $meeting->node_id);
    }

    public function delete(AuthUser $user, MeetingSession $meeting): bool
    {
        if (!$meeting->isDraft()) {
            return false;
        }

        return $this->mandateHasAuthorityOnNode($user, 'delete_meeting', $meeting->node_id);
    }
}
