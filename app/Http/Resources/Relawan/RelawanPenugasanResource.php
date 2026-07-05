<?php

namespace App\Http\Resources\Relawan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelawanPenugasanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_penugasan_relawan,
            'peran' => $this->peran_lapangan,
            'status_aktif' => (bool) $this->status_aktif,
            'tgl_mulai' => $this->tgl_mulai_aktif?->toDateString(),
            'tgl_selesai' => $this->tgl_selesai_aktif?->toDateString(),

            'posaju' => $this->when($this->relationLoaded('posaju') && $this->posaju, fn () => [
                'id' => $this->posaju->id_posaju,
                'nama' => $this->posaju->nama_posaju,
            ]),
        ];
    }
}
