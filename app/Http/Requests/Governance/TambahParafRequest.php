<?php

namespace App\Http\Requests\Governance;

use Illuminate\Foundation\Http\FormRequest;

class TambahParafRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_pengguna' => ['required', 'integer', 'exists:auth_users,id_pengguna'],
            'urutan' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}
