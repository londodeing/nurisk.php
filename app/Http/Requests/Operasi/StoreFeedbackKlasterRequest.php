<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackKlasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_insiden' => ['required', 'integer', 'exists:operasi_insiden,id_insiden'],
            'id_klaster_operasi' => ['required', 'integer', 'exists:operasi_klaster,id_klaster_operasi'],
            'kecukupan_sumberdaya' => ['required', 'in:kurang,cukup,berlebih'],
            'kualitas_layanan' => ['required', 'in:baik,sedang,buruk'],
            'tepat_waktu' => ['required', 'boolean'],
            'tepat_sasaran' => ['required', 'boolean'],
            'kendala' => ['nullable', 'string'],
            'rekomendasi' => ['nullable', 'string'],
            'gap_terdeteksi' => ['nullable', 'array'],
        ];
    }
}