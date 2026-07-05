<?php

namespace App\Http\Requests\Governance;

use Illuminate\Foundation\Http\FormRequest;

class StoreEskalasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_pleno' => ['required', 'integer', 'exists:operasi_pleno,id_pleno'],
            'level_sebelumnya' => ['required', 'in:lokal,pcnu,pwnu,nasional'],
            'level_baru' => ['required', 'in:lokal,pcnu,pwnu,nasional', 'different:level_sebelumnya'],
            'alasan_eskalasi' => ['required', 'string', 'min:10'],
        ];
    }
}
