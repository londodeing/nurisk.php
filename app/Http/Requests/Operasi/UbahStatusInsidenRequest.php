<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UbahStatusInsidenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('ubahStatus', $this->route('insiden'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status_baru' => ['required', 'in:draft,terverifikasi,respon,pemulihan,selesai,dibatalkan'],
            'alasan'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
