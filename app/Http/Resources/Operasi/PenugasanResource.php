<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenugasanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid_penugasan' => $this->uuid_penugasan,
            'uuid_insiden' => $this->insiden->uuid_insiden ?? null,
            'id_pengguna' => $this->id_pengguna,
            'id_klaster_operasi' => $this->id_klaster_operasi,
            'peran_otoritas' => $this->peran_otoritas,
            'status_penugasan' => $this->status_penugasan,
            'waktu_mulai' => $this->waktu_mulai ? $this->waktu_mulai->format('Y-m-d H:i:s') : null,
            'waktu_selesai' => $this->waktu_selesai ? $this->waktu_selesai->format('Y-m-d H:i:s') : null,
            'ditugaskan_oleh' => $this->ditugaskan_oleh,
            'catatan' => $this->catatan,
            'dibuat_pada' => $this->dibuat_pada ? $this->dibuat_pada->format('Y-m-d H:i:s') : null,
            
            // Includes opsional via with()
            'pengguna' => $this->whenLoaded('pengguna', function () {
                return [
                    'id_pengguna' => $this->pengguna->id_pengguna,
                    'nama_lengkap' => $this->pengguna->nama_lengkap,
                ];
            }),
            'klaster' => $this->whenLoaded('klasterOperasi', function () {
                return [
                    'uuid_klaster_operasi' => $this->klasterOperasi->uuid_klaster_operasi,
                    'status_klaster' => $this->klasterOperasi->status_klaster,
                ];
            }),
        ];
    }
}
