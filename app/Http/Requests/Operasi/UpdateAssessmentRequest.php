<?php

namespace App\Http\Requests\Operasi;

class UpdateAssessmentRequest extends StoreAssessmentRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return parent::rules();
    }
}
