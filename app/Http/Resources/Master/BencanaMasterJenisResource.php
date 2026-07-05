<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BencanaMasterJenisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_jenis,
            'nama' => $this->nama_bencana,
            'ikon_map' => $this->ikon_map,
        ];
    }
}
