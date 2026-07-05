<?php

namespace App\Http\Requests\Governance;

use App\Models\OperasiPleno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StorePlanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', OperasiPleno::class);
    }

    public function rules(): array
    {
        return [
            'id_insiden' => ['required', 'integer', 'exists:operasi_insiden,id_insiden'],
            'nomor_pleno' => ['nullable', 'string', 'max:100'],
            'waktu_pleno' => ['required', 'date'],
            'jenis_pleno' => ['required', 'in:aktivasi_operasi,evaluasi_rutin,perpanjangan_operasi,penutupan_operasi,eskalasi_wilayah,khusus'],
            'pimpinan_pleno' => ['required', 'integer', 'exists:auth_users,id_pengguna'],
            'notulis_pleno' => ['required', 'integer', 'exists:auth_users,id_pengguna'],
            'lokasi_pleno' => ['nullable', 'string', 'max:255'],
            'hasil_umum' => ['nullable', 'string'],
        ];
    }
}
