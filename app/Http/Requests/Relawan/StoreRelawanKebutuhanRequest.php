<?php

namespace App\Http\Requests\Relawan;

use Illuminate\Foundation\Http\FormRequest;

class StoreRelawanKebutuhanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('createKebutuhan', \App\Models\RelawanKebutuhan::class);
    }

    public function rules(): array
    {
        return [
            'uuid_insiden' => ['required', 'uuid', 'exists:operasi_insiden,uuid_insiden'],
            'id_operasi_klaster' => ['nullable', 'integer'],
            'id_posaju' => ['nullable', 'string'],
            'id_keahlian_utama' => ['nullable', 'integer', 'exists:auth_keahlian_master,id_keahlian'],
            'judul_posisi' => ['nullable', 'string', 'max:150'],
            'deskripsi_tugas' => ['required', 'string'],
            'jumlah_dibutuhkan' => ['required', 'integer', 'min:1'],
            'persyaratan' => ['nullable', 'string'],
            'tgl_mulai_tugas' => ['nullable', 'date'],
            'tgl_selesai_tugas' => ['nullable', 'date', 'after_or_equal:tgl_mulai_tugas'],
        ];
    }
}
