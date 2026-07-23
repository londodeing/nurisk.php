<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WilayahKabupatenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id_kab,
            'nama'  => $this->nama_kab,
            'tipe'  => $this->tipe,
            'label' => str_starts_with(strtolower($this->nama_kab), strtolower($this->tipe)) ? $this->nama_kab : $this->tipe . ' ' . $this->nama_kab,
        ];
    }
}
