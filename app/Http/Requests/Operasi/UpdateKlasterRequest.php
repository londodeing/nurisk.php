<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKlasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_klaster' => 'nullable|string|in:aktif,selesai',
            'prioritas' => 'nullable|string|in:rendah,sedang,tinggi,kritis',
            'target_cakupan' => 'nullable|string',
            'catatan' => 'nullable|string',
            'progres_persen' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
