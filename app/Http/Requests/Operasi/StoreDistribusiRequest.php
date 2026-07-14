<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDistribusiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_klaster_operasi'  => ['required', 'integer', 'exists:operasi_klaster,id_klaster_operasi'],
            'id_penugasan'        => ['nullable', 'integer', 'exists:operasi_penugasan,id_penugasan'],
            'id_barang_katalog'   => ['nullable', 'integer', 'exists:logistik_barang_katalog,id_katalog'],
            'nama_barang'         => ['required', 'string', 'max:255'],
            'jumlah'              => ['required', 'numeric', 'min:0.01'],
            'satuan'              => ['required', 'string', 'max:50'],
            'lokasi_tujuan'       => ['nullable', 'string'],
            'penerima'            => ['nullable', 'string', 'max:255'],
            'waktu_distribusi'    => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_klaster_operasi.required' => 'Klaster wajib dipilih.',
            'id_klaster_operasi.exists'   => 'Klaster tidak valid.',
            'id_penugasan.exists'         => 'Penugasan tidak valid.',
            'id_barang_katalog.exists'    => 'Katalog barang tidak valid.',
            'nama_barang.required'        => 'Nama barang wajib diisi.',
            'jumlah.required'             => 'Jumlah wajib diisi.',
            'jumlah.min'                  => 'Jumlah minimal 0.01.',
            'satuan.required'             => 'Satuan wajib diisi.',
            'waktu_distribusi.required'   => 'Waktu distribusi wajib diisi.',
            'waktu_distribusi.date'       => 'Format waktu tidak valid.',
        ];
    }
}