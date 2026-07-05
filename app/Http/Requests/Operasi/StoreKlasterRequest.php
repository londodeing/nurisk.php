<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreKlasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid_insiden' => 'required|uuid|exists:operasi_insiden,uuid_insiden',
            'id_master_klaster' => 'required|integer|exists:master_klaster,id_master_klaster',
            'prioritas' => 'nullable|string|in:rendah,sedang,tinggi,kritis',
            'target_cakupan' => 'nullable|string',
            'catatan' => 'nullable|string',
        ];
    }
}
