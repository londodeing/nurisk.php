<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterJabatanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_jabatan_posisi,
            'nama' => $this->nama_jabatan,
            'slug' => $this->slug,
            'deskripsi' => $this->deskripsi,
        ];
    }
}
