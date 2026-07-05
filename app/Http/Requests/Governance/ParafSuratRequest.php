<?php

namespace App\Http\Requests\Governance;

use Illuminate\Foundation\Http\FormRequest;

class ParafSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_paraf' => ['required', 'in:disetujui,ditolak'],
            'catatan' => ['required_if:status_paraf,ditolak', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'catatan.required_if' => 'Catatan wajib diisi ketika paraf ditolak.',
        ];
    }
}
