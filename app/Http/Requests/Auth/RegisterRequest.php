<?php

namespace App\Http\Requests\Auth;

use App\Services\Auth\RegistrationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $jenis = $this->route('jenis');

        $rules = [
            'no_hp'               => ['required', 'string', 'max:20',
                                      'regex:/^(08|\+628)[0-9]{8,12}$/',
                                      'unique:auth_users,no_hp'],
            'kata_sandi'          => ['required', 'string', 'min:8', 'confirmed'],
            'kata_sandi_confirmation' => ['required'],

            'nama_lengkap'        => ['required', 'string', 'max:150'],
            'nik'                 => ['nullable', 'string', 'size:16', 'unique:auth_pengguna_profil,nik'],
            'email'               => ['nullable', 'email', 'max:150', 'unique:auth_pengguna_profil,email'],

            'alamat_deskriptif'   => ['required', 'string', 'max:500'],

            'id_kabupaten'        => ['required', 'string', 'size:4', 'exists:wilayah_kabupaten,id_kab'],
            'id_kecamatan'        => ['required', 'string', 'size:6', 'exists:wilayah_kecamatan,id_kec'],
            'id_desa'             => ['required', 'string', 'size:10', 'exists:wilayah_desa,id_desa'],

            'keahlian'            => ['nullable', 'array'],
            'keahlian.*'          => ['integer', 'exists:auth_keahlian_master,id_keahlian'],
        ];

        if ($jenis === RegistrationService::JENIS_TRC_PCNU) {
            $rules['id_pcnu'] = ['required', 'integer', 'exists:organisasi_pcnu,id_pcnu'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'no_hp.required'          => 'Nomor HP wajib diisi.',
            'no_hp.regex'             => 'Format nomor HP tidak valid. Contoh: 08123456789',
            'no_hp.unique'            => 'Nomor HP ini sudah terdaftar.',
            'kata_sandi.required'     => 'Kata sandi wajib diisi.',
            'kata_sandi.min'          => 'Kata sandi minimal 8 karakter.',
            'kata_sandi.confirmed'    => 'Konfirmasi kata sandi tidak cocok.',
            'kata_sandi_confirmation.required' => 'Konfirmasi kata sandi wajib diisi.',
            'nama_lengkap.required'   => 'Nama lengkap wajib diisi.',
            'nik.size'                => 'NIK harus tepat 16 digit.',
            'nik.unique'              => 'NIK ini sudah terdaftar.',
            'email.unique'            => 'Email ini sudah terdaftar.',
            'alamat_deskriptif.required' => 'Alamat lengkap (RT/RW, Dusun) wajib diisi.',

            'id_kabupaten.required'   => 'Pilih kabupaten/kota domisili.',
            'id_kecamatan.required'   => 'Pilih kecamatan domisili.',
            'id_desa.required'        => 'Pilih desa domisili.',
            'id_pcnu.required'        => 'Pilih PCNU asal untuk pendaftaran ini.',
            'keahlian.*.exists'       => 'Keahlian yang dipilih tidak valid.',
        ];
    }
}
