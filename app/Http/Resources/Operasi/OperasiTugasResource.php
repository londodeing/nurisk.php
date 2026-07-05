<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperasiTugasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Anti-corruption layer: kolom internal (id_operasi_klaster, id_posaju,
     * ditugaskan_ke, id_surat_perintah, dihapus_pada) tidak dikembalikan langsung.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id_tugas,
            'judul'  => $this->judul_tugas,
            'status' => $this->status_tugas,

            'target_indikator' => $this->target_indikator,
            'progress'         => (float) $this->progres_persen,

            'klaster' => $this->when($this->relationLoaded('klaster') && $this->klaster, fn () => [
                'id'        => $this->klaster->id_klaster_operasi,
                'status'    => $this->klaster->status_klaster,
                'prioritas' => $this->klaster->prioritas,
            ]),

            'posaju' => $this->when($this->relationLoaded('posaju') && $this->posaju, fn () => [
                'id'   => $this->posaju->id_posaju,
                'nama' => $this->posaju->nama_posaju,
            ]),

            'pelaksana' => $this->when($this->relationLoaded('pelaksana') && $this->pelaksana, fn () => [
                'id' => $this->pelaksana->id_pengguna,
            ]),

            'dibuat_pada' => $this->dibuat_pada?->toIso8601String(),
        ];
    }
}
