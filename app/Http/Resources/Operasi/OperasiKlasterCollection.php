<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OperasiKlasterCollection extends ResourceCollection
{
    /**
     * Eksplisit binding ke Resource class.
     */
    public $collects = OperasiKlasterResource::class;

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


