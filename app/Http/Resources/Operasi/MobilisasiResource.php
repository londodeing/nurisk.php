<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobilisasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid_mobilisasi,
            'uuid_insiden' => $this->insiden ? $this->insiden->uuid_insiden : null,
            'id_pengguna' => $this->id_pengguna,
            'jenis_mobilisasi' => $this->jenis_mobilisasi,
            'status_mobilisasi' => $this->status_mobilisasi,
            'lokasi_asal' => $this->lokasi_asal,
            'lokasi_tujuan' => $this->lokasi_tujuan,
            'waktu_berangkat' => $this->waktu_berangkat,
            'waktu_tiba' => $this->waktu_tiba,
            'catatan' => $this->catatan,
            'sync_version' => $this->sync_version,
            'dibuat_pada' => $this->dibuat_pada,
            'diperbarui_pada' => $this->diperbarui_pada,
        ];
    }
}
