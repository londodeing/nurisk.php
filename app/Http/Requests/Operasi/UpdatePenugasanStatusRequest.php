<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenugasanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_penugasan' => 'required|string|in:aktif,selesai,dibatalkan',
            'catatan' => 'nullable|string',
        ];
    }
}
