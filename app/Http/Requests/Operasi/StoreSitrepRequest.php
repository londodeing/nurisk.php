<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreSitrepRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled in the Controller via Policy
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid_insiden' => 'required|uuid|exists:operasi_insiden,uuid_insiden',
            'periode_sitrep' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ];
    }
}
