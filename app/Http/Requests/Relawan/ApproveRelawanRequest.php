<?php

namespace App\Http\Requests\Relawan;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\RelawanPendaftaran;

class ApproveRelawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pendaftaran = $this->route('pendaftaran');
        if (is_numeric($pendaftaran)) {
            $pendaftaran = RelawanPendaftaran::findOrFail($pendaftaran);
        }
        return $this->user()->can('approveRelawan', $pendaftaran);
    }

    public function rules(): array
    {
        // Approve action usually doesn't need body parameters, but we can ensure empty or any additional notes if needed in future
        return [];
    }
}
