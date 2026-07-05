<?php

namespace App\Http\Resources\Governance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MandateSnapshotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (!$this->resource) {
            return [];
        }

        $this->resource->loadMissing(['nodePosition.position', 'nodePosition.node', 'user.profil', 'sk']);

        return [
            'mandate_id' => $this->id,
            'user_name' => $this->user?->profil?->nama_lengkap ?? $this->user?->no_hp ?? 'Unknown',
            'position' => $this->nodePosition?->position?->name ?? 'Unknown',
            'node' => $this->nodePosition?->node?->name ?? 'Unknown',
            'sk_number' => $this->sk?->nomor_sk,
        ];
    }
}
