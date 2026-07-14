<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackDistribusiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kecukupan'   => ['required', 'in:kurang,cukup,berlebih'],
            'kualitas'    => ['required', 'in:baik,sedang,buruk'],
            'tepat_waktu' => ['required', 'boolean'],
            'tepat_sasaran' => ['required', 'boolean'],
            'kendala'     => ['nullable', 'string'],
            'rekomendasi' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'kecukupan.required'   => 'Kecukupan wajib dipilih.',
            'kecukupan.in'         => 'Nilai kecukupan tidak valid.',
            'kualitas.required'    => 'Kualitas wajib dipilih.',
            'kualitas.in'          => 'Nilai kualitas tidak valid.',
            'tepat_waktu.required' => 'Tepat waktu wajib dipilih.',
            'tepat_sasaran.required' => 'Tepat sasaran wajib dipilih.',
        ];
    }
}