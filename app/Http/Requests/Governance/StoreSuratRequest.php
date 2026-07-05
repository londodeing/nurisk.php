<?php

namespace App\Http\Requests\Governance;

use App\Models\AuthUser;
use App\Models\DokumenSuratUtama;
use App\Models\MasterJabatanPenandatangan;
use App\Models\OperasiInsiden;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', DokumenSuratUtama::class);
    }

    public function rules(): array
    {
        return [
            'id_insiden' => ['nullable', 'integer', 'exists:operasi_insiden,id_insiden'],
            'id_jenis_surat' => ['required', 'integer', 'exists:master_surat_jenis,id_jenis_surat'],
            'perihal' => ['required', 'string', 'max:255'],
            'tgl_terbit' => ['required', 'date'],
            'id_pengguna_ttd' => ['required', 'integer', 'exists:auth_users,id_pengguna'],
            'id_jabatan_ttd' => ['nullable', 'integer', 'exists:master_jabatan_penandatangan,id_jabatan'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $idInsiden = $this->id_insiden;
            $tglTerbit = $this->tgl_terbit;
            if ($idInsiden && $tglTerbit) {
                $insiden = OperasiInsiden::find($idInsiden);
                if ($insiden && $insiden->waktu_mulai) {
                    if ($tglTerbit < $insiden->waktu_mulai->toDateString()) {
                        $v->errors()->add('tgl_terbit', 'Tanggal surat tidak boleh mendahului waktu mulai insiden (' . $insiden->waktu_mulai->format('d/m/Y') . ').');
                    }
                }
            }

            $idPenggunaTtd = $this->id_pengguna_ttd;
            $idJabatanTtd = $this->id_jabatan_ttd;
            if ($idPenggunaTtd && $idJabatanTtd) {
                $jabatanExists = MasterJabatanPenandatangan::where('id_jabatan', $idJabatanTtd)->exists();
                if (!$jabatanExists) {
                    $v->errors()->add('id_jabatan_ttd', 'Jabatan penandatangan tidak valid.');
                }
            }
        });
    }
}
