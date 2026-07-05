<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateInsidenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('insiden'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $insiden = $this->route('insiden');
        $idInsiden = $insiden ? $insiden->id_insiden : null;

        return [
            'kode_kejadian'    => [
                'required',
                'string',
                'max:25',
                Rule::unique('operasi_insiden', 'kode_kejadian')->ignore($idInsiden, 'id_insiden')
            ],
            'id_jenis_bencana' => ['required', 'integer', 'exists:bencana_master_jenis,id_jenis'],
            'prioritas'        => ['nullable', 'in:rendah,sedang,tinggi,kritis'],
            'waktu_mulai'      => ['required', 'date'],
            'waktu_selesai'    => ['nullable', 'date', 'after:waktu_mulai'],
        ];
    }
}
