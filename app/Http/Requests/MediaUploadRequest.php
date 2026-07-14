<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity_type' => ['required', 'string', 'max:50'],
            'entity_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:jpeg,png,webp,pdf,docx', 'max:10240'],
            'visibility' => ['sometimes', 'string', 'in:PUBLIC,PRIVATE'],
        ];
    }
}
