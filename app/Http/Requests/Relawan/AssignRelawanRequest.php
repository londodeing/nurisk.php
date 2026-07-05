<?php

namespace App\Http\Requests\Relawan;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RelawanPendaftaran;

class AssignRelawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pendaftaran = $this->route('pendaftaran');
        if (is_numeric($pendaftaran)) {
            $pendaftaran = RelawanPendaftaran::findOrFail($pendaftaran);
        }
        return $this->user()->can('assignRelawan', $pendaftaran);
    }

    public function rules(): array
    {
        return [
            'id_posaju' => ['nullable', 'integer', 'exists:operasi_posaju,id_posaju'],
            'peran_lapangan' => ['nullable', 'string', 'max:100'],
        ];
    }
}
