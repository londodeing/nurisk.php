<?php

namespace App\Http\Resources\Relawan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelawanPendaftaranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pendaftaran,
            'status' => $this->status_pendaftaran,
            'motivasi' => $this->motivasi_singkat,
            'catatan_verifikator' => $this->catatan_verifikator,
            'waktu_daftar' => $this->waktu_daftar?->toIso8601String(),
            'waktu_penugasan_dimulai' => $this->waktu_penugasan_dimulai?->toIso8601String(),
            'waktu_penugasan_selesai' => $this->waktu_penugasan_selesai?->toIso8601String(),

            'kebutuhan' => $this->when($this->relationLoaded('kebutuhan') && $this->kebutuhan, fn () => [
                'id' => $this->kebutuhan->id_relawan_kebutuhan,
                'judul_posisi' => $this->kebutuhan->judul_posisi,
            ]),

            'relawan' => $this->when($this->relationLoaded('relawan') && $this->relawan, fn () => [
                'id' => $this->relawan->id_pengguna,
                'nama' => $this->relawan->profil?->nama_lengkap,
            ]),

            'penugasan' => new RelawanPenugasanResource($this->whenLoaded('penugasan')),
        ];
    }
}
