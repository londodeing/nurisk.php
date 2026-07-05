<?php

namespace App\Http\Requests\Governance;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePesertaVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_persetujuan' => ['required', 'in:setuju,tolak,abstain'],
            'catatan_peserta' => ['required_if:status_persetujuan,tolak', 'nullable', 'string', 'max:500'],
        ];
    }
}
