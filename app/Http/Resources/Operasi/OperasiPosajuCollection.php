<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OperasiPosajuCollection extends ResourceCollection
{
    /**
     * Eksplisit binding ke Resource class.
     * Ini memastikan setiap item di koleksi melewati OperasiPosajuResource.
     */
    public $collects = OperasiPosajuResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

}
