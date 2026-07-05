<?php

namespace App\Events\Governance;

use App\Models\MeetingSession;
use App\Models\OrgMandate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * MeetingStatusChanged
 *
 * Fired setiap kali status meeting berubah.
 * Listener dapat merekam audit trail, mengirim notifikasi, dll.
 */
class MeetingStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MeetingSession $meeting,
        public string $previousStatus,
        public OrgMandate $actorMandate
    ) {}
}
