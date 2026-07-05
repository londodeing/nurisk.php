<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KlasterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid_klaster_operasi,
            'uuid_insiden' => $this->insiden->uuid_insiden ?? null,
            'id_master_klaster' => $this->id_master_klaster,
            'status_klaster' => $this->status_klaster,
            'prioritas' => $this->prioritas,
            'target_cakupan' => $this->target_cakupan,
            'catatan' => $this->catatan,
            'waktu_aktivasi' => $this->waktu_aktivasi ? $this->waktu_aktivasi->format('Y-m-d H:i:s') : null,
            'waktu_ditutup' => $this->waktu_ditutup ? $this->waktu_ditutup->format('Y-m-d H:i:s') : null,
            'dibuat_pada' => $this->dibuat_pada ? $this->dibuat_pada->format('Y-m-d H:i:s') : null,

            'master_klaster' => $this->whenLoaded('masterKlaster', function () {
                return [
                    'nama_klaster' => $this->masterKlaster->nama_klaster,
                ];
            }),
        ];
    }
}
