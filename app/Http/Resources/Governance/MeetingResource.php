<?php

namespace App\Http\Resources\Governance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meeting_number' => $this->meeting_number,
            'title' => $this->title,
            'meeting_type' => $this->meeting_type,
            'status' => $this->status,
            'status_label' => $this->labelStatus(),

            'schedule' => [
                'scheduled_at' => $this->scheduled_at?->toIso8601String(),
                'started_at' => $this->started_at?->toIso8601String(),
                'ended_at' => $this->ended_at?->toIso8601String(),
                'venue' => $this->venue,
                'venue_type' => $this->venue_type,
            ],

            'quorum' => [
                'required' => $this->quorum_required,
                'met' => $this->quorum_met,
                'present_count' => $this->whenLoaded('attendees', fn() =>
                    $this->attendees->where('attendance_status', 'present')->count()
                ),
            ],

            'roles' => [
                'chairperson' => new MandateSnapshotResource($this->whenLoaded('chairpersonMandate')),
                'secretary' => new MandateSnapshotResource($this->whenLoaded('secretaryMandate')),
                'approved_by' => new MandateSnapshotResource($this->whenLoaded('approvedByMandate')),
            ],

            'node' => $this->when($this->relationLoaded('node'), fn() => [
                'id' => $this->node->id,
                'name' => $this->node->name,
                'territory_code' => $this->node->territory_code,
            ]),

            'counts' => [
                'agendas' => $this->whenLoaded('agendas', fn() => $this->agendas->count()),
                'attendees' => $this->whenLoaded('attendees', fn() => $this->attendees->count()),
                'votes' => $this->whenLoaded('votes', fn() => $this->votes->count()),
            ],

            'agendas' => MeetingAgendaResource::collection($this->whenLoaded('agendas')),
            'attendees' => MeetingAttendeeResource::collection($this->whenLoaded('attendees')),
            'minutes' => new MeetingMinutesResource($this->whenLoaded('minutes')),

            'related_incident_id' => $this->related_incident_id,
            'document_hash' => $this->document_hash,

            'audit' => [
                'created_by_mandate_id' => $this->created_by_mandate_id,
                'updated_by_mandate_id' => $this->updated_by_mandate_id,
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ],
        ];
    }
}
