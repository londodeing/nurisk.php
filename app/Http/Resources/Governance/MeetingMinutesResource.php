<?php

namespace App\Http\Resources\Governance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingMinutesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (!$this->resource) {
            return [];
        }

        return [
            'id' => $this->id,
            'status' => $this->status,
            'summary' => $this->summary,
            'content_snapshot' => $this->when($request->routeIs('*.show'), $this->content_snapshot),
            'document_hash' => $this->document_hash,
            'file_path' => $this->file_path,
            'prepared_by' => new MandateSnapshotResource($this->whenLoaded('preparedByMandate')),
            'reviewed_by' => new MandateSnapshotResource($this->whenLoaded('reviewedByMandate')),
            'approved_by' => new MandateSnapshotResource($this->whenLoaded('approvedByMandate')),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'locked_at' => $this->locked_at?->toIso8601String(),
        ];
    }
}
