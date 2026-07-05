<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SitrepResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid_sitrep' => $this->uuid_sitrep,
            'uuid_insiden' => $this->insiden->uuid_insiden ?? null,
            'nomor_sitrep' => $this->nomor_sitrep,
            'periode_sitrep' => $this->periode_sitrep,
            'waktu_sitrep' => $this->waktu_sitrep ? $this->waktu_sitrep->format('Y-m-d H:i:s') : null,
            'catatan' => $this->catatan,
            'dampak' => $this->whenLoaded('dampak', function () {
                return [
                    'meninggal' => $this->dampak->meninggal,
                    'hilang' => $this->dampak->hilang,
                    'luka_berat' => $this->dampak->luka_berat,
                    'luka_ringan' => $this->dampak->luka_ringan,
                    'mengungsi' => $this->dampak->mengungsi,
                ];
            }),
            'kebutuhan' => $this->whenLoaded('kebutuhan', function () {
                return $this->kebutuhan->map(function ($keb) {
                    return [
                        'nama_kebutuhan' => $keb->nama_kebutuhan,
                        'jumlah' => $keb->jumlah,
                        'satuan' => $keb->satuan,
                    ];
                });
            }),
            'pembuat' => $this->whenLoaded('pembuat', function () {
                return $this->pembuat->nama_lengkap ?? null; // Adjust based on AuthUser columns
            }),
            'dibuat_pada' => $this->dibuat_pada ? $this->dibuat_pada->format('Y-m-d H:i:s') : null,
        ];
    }
}
