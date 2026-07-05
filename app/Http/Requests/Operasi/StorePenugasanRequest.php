<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;

class StorePenugasanRequest extends FormRequest
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
            'id_klaster_operasi' => 'nullable|integer|exists:operasi_klaster,id_klaster_operasi',
            'peran_otoritas' => 'required|string|in:komandan_insiden,trc,relawan,medis,logistik,operator',
            'waktu_mulai' => 'nullable|date',
            'catatan' => 'nullable|string',
        ];
    }
}
