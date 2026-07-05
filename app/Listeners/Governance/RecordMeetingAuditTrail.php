<?php

namespace App\Listeners\Governance;

use App\Events\Governance\MeetingStatusChanged;
use App\Models\OrgAuditLog;
use App\Services\Governance\MandateResolverService;

/**
 * RecordMeetingAuditTrail
 *
 * Merekam audit trail immutable setiap kali status meeting berubah.
 */
class RecordMeetingAuditTrail
{
    public function __construct(
        private MandateResolverService $mandateResolver
    ) {}

    public function handle(MeetingStatusChanged $event): void
    {
        $snapshot = $this->mandateResolver->getLegalSnapshot($event->actorMandate);

        OrgAuditLog::create([
            'timestamp' => now(),
            'actor_user_id' => $snapshot['user_id'],
            'actor_name' => $snapshot['user_name'],
            'actor_position' => $snapshot['position_name'] . ' ' . $snapshot['node_name'],
            'sk_number' => $snapshot['sk_number'],
            'mandate_id' => $snapshot['mandate_id'],
            'action_type' => 'meeting_status_changed',
            'target_table' => 'meeting_sessions',
            'target_id' => $event->meeting->id,
            'digital_signature' => json_encode([
                'previous_status' => $event->previousStatus,
                'new_status' => $event->meeting->status,
                'meeting_title' => $event->meeting->title,
                'timestamp' => now()->toIso8601String(),
            ]),
        ]);
    }
}
