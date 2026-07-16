<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_date' => 'required|date',
            'event_time' => 'nullable|string',
            'jenis_laporan' => 'nullable|string',
            'id_kecamatan' => 'nullable|string|max:6',
            'id_desa' => 'nullable|string|max:10',
            'alamat_spesifik' => 'nullable|string',
            'region' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'kondisi_mutakhir' => 'nullable|string',
            'upaya_penanganan' => 'nullable|string',
            'sebaran_dampak' => 'nullable|string',
            'kendala_lapangan' => 'nullable|string',
            'kendala_tambahan' => 'nullable|string',
            'rekomendasi_aksi' => 'nullable|string',
            'kebutuhan' => 'nullable|array',
            'kebutuhan.dana' => 'nullable|string',
            'kebutuhan.relawan' => 'nullable|string',
            'kebutuhan.logistik' => 'nullable|string',
            'kebutuhan.peralatan' => 'nullable|string',
            'kebutuhan.medis' => 'nullable|string',
            'kebutuhan.pangan' => 'nullable|string',
            'kebutuhan.lainnya' => 'nullable|string',
            'needs_numeric' => 'nullable|array',
            'dampak_manusia' => 'nullable|array',
            'dampak_manusia.meninggal' => 'nullable|integer|min:0',
            'dampak_manusia.hilang' => 'nullable|integer|min:0',
            'dampak_manusia.luka_berat' => 'nullable|integer|min:0',
            'dampak_manusia.luka_ringan' => 'nullable|integer|min:0',
            'dampak_manusia.dampak_manusia' => 'nullable|integer|min:0',
            'dampak_manusia.pengungsi_jiwa' => 'nullable|integer|min:0',
            'dampak_manusia.pengungsi_kk' => 'nullable|integer|min:0',
            'dampak_rumah' => 'nullable|array',
            'dampak_rumah.berat' => 'nullable|integer|min:0',
            'dampak_rumah.sedang' => 'nullable|integer|min:0',
            'dampak_rumah.ringan' => 'nullable|integer|min:0',
            'dampak_fasum' => 'nullable|array',
            'dampak_fasum.sanitas' => 'nullable|integer|min:0',
            'dampak_fasum.pendidikan' => 'nullable|integer|min:0',
            'dampak_fasum.kesehatan' => 'nullable|integer|min:0',
            'dampak_fasum.ibadah' => 'nullable|integer|min:0',
            'dampak_fasum.komunikasi' => 'nullable|integer|min:0',
            'dampak_fasum.listrik' => 'nullable|integer|min:0',
            'dampak_fasum.kantor' => 'nullable|integer|min:0',
            'dampak_fasum.jembatan' => 'nullable|integer|min:0',
            'dampak_fasum.pasar' => 'nullable|integer|min:0',
            'dampak_fasum.spbu' => 'nullable|integer|min:0',
            'dampak_vital' => 'nullable|array',
            'dampak_vital.air' => 'nullable|integer|min:0',
            'dampak_vital.listrik' => 'nullable|integer|min:0',
            'dampak_vital.telkom' => 'nullable|integer|min:0',
            'dampak_vital.irigasi' => 'nullable|numeric|min:0',
            'dampak_vital.jalan' => 'nullable|numeric|min:0',
            'dampak_vital.spbu' => 'nullable|integer|min:0',
            'dampak_lingkungan' => 'nullable|array',
            'dampak_lingkungan.sawah' => 'nullable|numeric|min:0',
            'dampak_lingkungan.hutan' => 'nullable|numeric|min:0',
            'dampak_lingkungan.unggas' => 'nullable|integer|min:0',
            'dampak_lingkungan.kaki_empat' => 'nullable|integer|min:0',
            'dampak_lingkungan.perikanan_kolam' => 'nullable|numeric|min:0',
            'dampak_lingkungan.perikanan_nelayan' => 'nullable|integer|min:0',
            'dampak_ekonomi' => 'nullable|array',
            'dampak_ekonomi.persentase' => 'nullable|string',
            'dampak_ekonomi.sektor_1' => 'nullable|string',
            'dampak_ekonomi.kontribusi_1' => 'nullable|numeric|min:0',
            'dampak_ekonomi.status_1' => 'nullable|string',
            'dampak_ekonomi.sektor_2' => 'nullable|string',
            'dampak_ekonomi.kontribusi_2' => 'nullable|numeric|min:0',
            'dampak_ekonomi.status_2' => 'nullable|string',
            'dampak_ekonomi.sektor_3' => 'nullable|string',
            'dampak_ekonomi.kontribusi_3' => 'nullable|numeric|min:0',
            'dampak_ekonomi.status_3' => 'nullable|string',
            'dampak_ekonomi.distribusi' => 'nullable|string',
            'dampak_ekonomi.fasilitas' => 'nullable|string',
        ];
    }
}
