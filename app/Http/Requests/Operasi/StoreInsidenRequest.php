<?php

namespace App\Http\Requests\Operasi;

use App\Models\OperasiInsiden;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreInsidenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', OperasiInsiden::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'kode_kejadian'    => ['nullable', 'string', 'max:25', 'unique:operasi_insiden,kode_kejadian'],
            'id_jenis_bencana' => ['required', 'integer', 'exists:bencana_master_jenis,id_jenis'],
            'id_pcnu'          => ['required', 'integer', 'exists:organisasi_pcnu,id_pcnu'],
            'prioritas'        => ['nullable', 'in:rendah,sedang,tinggi,kritis'],
            'waktu_mulai'      => ['required', 'date', 'before_or_equal:now'],
            'waktu_selesai'    => ['nullable', 'date', 'after:waktu_mulai'],
            'id_mwc'           => ['nullable', 'integer'],
        ];
    }
}
