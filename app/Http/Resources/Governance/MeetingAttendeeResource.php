<?php

namespace App\Http\Resources\Governance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingAttendeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mandate' => new MandateSnapshotResource($this->whenLoaded('mandate')),
            'role_in_meeting' => $this->role_in_meeting,
            'attendance_status' => $this->attendance_status,
            'has_voting_right' => $this->has_voting_right,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
        ];
    }
}
