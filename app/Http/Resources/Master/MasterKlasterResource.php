<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterKlasterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_master_klaster,
            'nama' => $this->nama_klaster,
            'deskripsi' => $this->deskripsi,
            'is_aktif' => $this->is_aktif,
        ];
    }
}
