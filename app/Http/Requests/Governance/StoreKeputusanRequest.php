<?php

namespace App\Http\Requests\Governance;

use Illuminate\Foundation\Http\FormRequest;

class StoreKeputusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_objek' => ['required', 'in:insiden,posaju,klaster,personil,logistik,anggaran'],
            'jenis_keputusan' => ['required', 'in:penunjukan_personil,aktivasi_pos,perubahan_status_insiden,alokasi_sumberdaya,lainnya'],
            'tipe_target_keputusan' => ['required', 'in:pos_aju,personil,logistik,insiden,klaster,perpanjangan_operasi'],
            'deskripsi_keputusan' => ['required', 'string', 'min:10'],
            'referensi_id' => ['nullable', 'integer'],
            'referensi_tabel' => ['nullable', 'string', 'max:50'],
        ];
    }
}
