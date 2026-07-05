<?php

namespace App\Http\Resources\Governance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingAgendaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sequence' => $this->sequence,
            'title' => $this->title,
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status,
            'resolution' => $this->resolution,
            'presenter' => new MandateSnapshotResource($this->whenLoaded('presenterMandate')),
            'vote_results' => $this->when($this->relationLoaded('votes'), fn() => $this->voteResults()),
        ];
    }
}
