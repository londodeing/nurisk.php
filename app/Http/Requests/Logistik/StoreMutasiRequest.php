<?php

namespace App\Http\Requests\Logistik;

use Illuminate\Foundation\Http\FormRequest;

class StoreMutasiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id_stok' => 'required|exists:logistik_stok,id_stok',
            'tipe_mutasi' => 'required|in:masuk,keluar,penyesuaian',
            'jumlah' => 'required|numeric|min:0.01',
            'asal_tujuan' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ];
    }
}
