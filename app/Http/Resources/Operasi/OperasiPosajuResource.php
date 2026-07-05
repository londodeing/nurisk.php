<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperasiPosajuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Anti-corruption layer: hanya field yang diizinkan yang tampil.
     * Kolom internal (id_pleno_pendirian, id_surat_pendirian, id_periode_operasi,
     * dihapus_pada, pj_posaju) TIDAK pernah dikembalikan langsung.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id_posaju,
            'nama'   => $this->nama_posaju,
            'status' => $this->status_alur,

            'insiden' => $this->when($this->relationLoaded('insiden') && $this->insiden, fn () => [
                'uuid' => $this->insiden->uuid_insiden,
                'nama' => $this->insiden->kode_kejadian,
            ]),

            'koordinat' => [
                'lat' => $this->latitude,
                'lng' => $this->longitude,
            ],

            'alamat_lokasi' => $this->alamat_lokasi,

            'penanggung_jawab' => $this->when($this->relationLoaded('pj') && $this->pj, fn () => [
                'id'   => $this->pj->id_pengguna,
            ]),

            'waktu_diaktifkan'     => $this->waktu_diaktifkan?->toIso8601String(),
            'diperpanjang_hingga'  => $this->diperpanjang_hingga?->toIso8601String(),
            'waktu_ditutup'        => $this->waktu_ditutup?->toIso8601String(),
            'alasan_penutupan'     => $this->when($this->status_alur === 'ditutup', $this->alasan_penutupan),
            'dibuat_pada'          => $this->dibuat_pada?->toIso8601String(),
        ];
    }
}
