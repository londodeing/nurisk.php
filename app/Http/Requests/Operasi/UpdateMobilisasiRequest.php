<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMobilisasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_mobilisasi' => 'sometimes|string|max:100',
            'lokasi_asal' => 'nullable|string|max:255',
            'lokasi_tujuan' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ];
    }
}
