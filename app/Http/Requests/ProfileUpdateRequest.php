<?php

namespace App\Http\Requests;

use App\Models\AuthPenggunaProfil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => [
                'required',
                'string',
                'size:16',
                Rule::unique('auth_pengguna_profil', 'nik')->ignore($this->user()->id_pengguna, 'id_pengguna'),
            ],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('auth_pengguna_profil', 'email')->ignore($this->user()->id_pengguna, 'id_pengguna'),
            ],
            'alamat' => ['nullable', 'string', 'max:500'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'string', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'profesi' => ['nullable', 'string', 'max:100'],
            'pengalaman_kebencanaan' => ['nullable', 'string'],
            'keahlian' => ['nullable', 'array'],
            'keahlian.*' => ['integer', 'exists:auth_keahlian_master,id_keahlian'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus 16 digit.',
            'nik.unique' => 'NIK ini sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan.',
        ];
    }
}
