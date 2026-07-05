<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OperasiInsiden;

class StoreMobilisasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid_insiden' => 'required|uuid|exists:operasi_insiden,uuid_insiden',
            'id_pengguna' => 'required|integer|exists:auth_users,id_pengguna',
            'jenis_mobilisasi' => 'required|string|max:100',
            'lokasi_asal' => 'nullable|string|max:255',
            'lokasi_tujuan' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ];
    }
}
