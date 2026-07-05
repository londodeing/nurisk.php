<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperasiKlasterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Anti-corruption layer: kolom internal (dibuat_oleh, waktu_nonaktif,
     * id_klaster sebagai FK) tidak dikembalikan langsung.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $names = [
            1 => 'Klaster Penyelamatan / TRC',
            2 => 'Klaster Kesehatan / Medis',
            3 => 'Klaster Logistik / Dapur Umum',
            4 => 'Klaster Hunian / Pengungsian',
            5 => 'Klaster Keamanan',
            6 => 'Klaster Administrasi / Command Center / Humas',
        ];

        return [
            'id'        => $this->uuid_klaster_operasi,
            'nama'      => $names[$this->id_master_klaster] ?? 'Klaster ' . $this->id_master_klaster,
            'status'    => $this->status_klaster,
            'prioritas' => $this->prioritas,
            'progress'  => (float) $this->progres_persen,

            'dibutuhkan'             => $this->dibutuhkan,
            'target_cakupan'         => $this->target_cakupan,
            'indikator_keberhasilan' => $this->indikator_keberhasilan,

            'insiden' => $this->when($this->relationLoaded('insiden') && $this->insiden, fn () => [
                'uuid' => $this->insiden->uuid_insiden ?? null,
                'nama' => $this->insiden->kode_kejadian,
            ]),

            'waktu_aktivasi' => $this->waktu_aktivasi?->toIso8601String(),
        ];
    }
}
