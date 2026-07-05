<?php

namespace App\Http\Requests\Relawan;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RelawanPendaftaran;

class RejectRelawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pendaftaran = $this->route('pendaftaran');
        if (is_numeric($pendaftaran)) {
            $pendaftaran = RelawanPendaftaran::findOrFail($pendaftaran);
        }
        return $this->user()->can('rejectRelawan', $pendaftaran);
    }

    public function rules(): array
    {
        return [
            'catatan_verifikator' => ['required', 'string', 'max:1000'],
        ];
    }
}
