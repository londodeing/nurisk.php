<?php

namespace App\Http\Resources\Relawan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelawanProfilResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pengguna = $this->pengguna;

        return [
            'id' => $this->id_pengguna,
            'nik' => $this->nik,
            'nama' => $this->nama_lengkap,
            'email' => $this->email,
            'id_desa_domisili' => $this->id_desa_domisili,

            'keahlian' => $this->when($pengguna && $pengguna->relationLoaded('keahlian'), fn () => 
                $pengguna->keahlian->map(fn ($k) => [
                    'id' => $k->id_keahlian,
                    'nama' => $k->nama_keahlian,
                ])
            ),
        ];
    }
}
