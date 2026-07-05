<?php

namespace App\Http\Requests\Relawan;

use Illuminate\Foundation\Http\FormRequest;

class DaftarRelawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Setiap relawan yang aktif bisa mendaftar
        $authContext = app(\App\Services\Auth\AuthorizationContextService::class);
        return $authContext->hasRole('relawan');
    }

    public function rules(): array
    {
        return [
            'id_relawan_kebutuhan' => ['required', 'integer', 'exists:relawan_kebutuhan,id_relawan_kebutuhan'],
            'motivasi_singkat' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
