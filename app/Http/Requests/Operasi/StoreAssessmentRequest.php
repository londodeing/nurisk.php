<?php

namespace App\Http\Requests\Operasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Handled in controller via policy
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'uuid_insiden'             => 'required|exists:operasi_insiden,uuid_insiden',
            'jenis_laporan'            => 'required|in:kaji_cepat,pendataan_lanjutan',
            'cakupan_wilayah_deskripsi'=> 'required|string|min:10|max:255',
            'latitude'                 => 'nullable|numeric|min:-11|max:6',
            'longitude'                => 'nullable|numeric|min:95|max:141',
            'waktu_assesment'          => 'required|date|before_or_equal:now',

            // Dampak Manusia V1 (core) + V2 fields (extended)
            'dampak_manusia'                           => 'required|array',
            'dampak_manusia.meninggal'                 => 'nullable|integer|min:0',
            'dampak_manusia.hilang'                    => 'nullable|integer|min:0',
            'dampak_manusia.luka_berat'                => 'nullable|integer|min:0',
            'dampak_manusia.luka_ringan'               => 'nullable|integer|min:0',
            'dampak_manusia.menderita_mengungsi'       => 'nullable|integer|min:0',
            'dampak_manusia.terdampak_kk'              => 'nullable|integer|min:0',
            'dampak_manusia.pengungsi_jiwa'            => 'nullable|integer|min:0',
            'dampak_manusia.pengungsi_kk'              => 'nullable|integer|min:0',
            'dampak_manusia.pengungsi_balita'          => 'nullable|integer|min:0',
            'dampak_manusia.pengungsi_lansia'          => 'nullable|integer|min:0',
            'dampak_manusia.pengungsi_disabilitas'     => 'nullable|integer|min:0',
            'dampak_manusia.pengungsi_ibu_hamil'       => 'nullable|integer|min:0',

            // Kebutuhan Mendesak
            'kebutuhan_mendesak'                       => 'nullable|array',
            'kebutuhan_mendesak.*.nama_kebutuhan'      => 'required_with:kebutuhan_mendesak|string|max:255',
            'kebutuhan_mendesak.*.jumlah'              => 'required_with:kebutuhan_mendesak|integer|min:1',
            'kebutuhan_mendesak.*.satuan'              => 'required_with:kebutuhan_mendesak|string|max:50',
            'kebutuhan_mendesak.*.catatan'             => 'nullable|string',

            // Dampak Infrastruktur
            'dampak_infrastruktur'                                => 'nullable|array',
            'dampak_infrastruktur.rumah_rusak_berat'              => 'nullable|integer|min:0',
            'dampak_infrastruktur.rumah_rusak_sedang'             => 'nullable|integer|min:0',
            'dampak_infrastruktur.rumah_rusak_ringan'             => 'nullable|integer|min:0',
            'dampak_infrastruktur.rumah_terendam'                 => 'nullable|integer|min:0',
            'dampak_infrastruktur.fasilitas_kesehatan_rusak'      => 'nullable|integer|min:0',
            'dampak_infrastruktur.fasilitas_pendidikan_rusak'     => 'nullable|integer|min:0',
            'dampak_infrastruktur.tempat_ibadah_rusak'            => 'nullable|integer|min:0',
            'dampak_infrastruktur.kantor_pemerintah_rusak'        => 'nullable|integer|min:0',
            'dampak_infrastruktur.jalan_rusak_km'                 => 'nullable|numeric|min:0',
            'dampak_infrastruktur.jembatan_putus'                 => 'nullable|integer|min:0',
            'dampak_infrastruktur.jembatan_rusak'                 => 'nullable|integer|min:0',
            'dampak_infrastruktur.sarana_air_bersih_rusak'        => 'nullable|boolean',
            'dampak_infrastruktur.jaringan_listrik_padam_kk'      => 'nullable|integer|min:0',
            'dampak_infrastruktur.jaringan_komunikasi_putus'      => 'nullable|boolean',
            'dampak_infrastruktur.catatan_infrastruktur'          => 'nullable|string',

            // Dampak Lingkungan
            'dampak_lingkungan'                                   => 'nullable|array',
            'dampak_lingkungan.lahan_pertanian_rusak_ha'          => 'nullable|numeric|min:0',
            'dampak_lingkungan.hutan_terdampak_ha'                => 'nullable|numeric|min:0',
            'dampak_lingkungan.lahan_tercemar_ha'                 => 'nullable|numeric|min:0',
            'dampak_lingkungan.sumber_air_tercemar'               => 'nullable|boolean',
            'dampak_lingkungan.pencemaran_tanah'                  => 'nullable|boolean',
            'dampak_lingkungan.erosi_sedimentasi'                 => 'nullable|boolean',
            'dampak_lingkungan.kerusakan_ekosistem_pesisir'       => 'nullable|boolean',
            'dampak_lingkungan.kerusakan_daerah_aliran_sungai'    => 'nullable|boolean',
            'dampak_lingkungan.tingkat_kerusakan_lingkungan'      => 'nullable|in:tidak_ada,ringan,sedang,berat,sangat_berat',
            'dampak_lingkungan.butuh_rehabilitasi_lahan'          => 'nullable|boolean',
            'dampak_lingkungan.catatan_lingkungan'                => 'nullable|string',
            'dampak_lingkungan.ternak_terdampak_ekor'             => 'nullable|integer|min:0',

            // Dampak Ekonomi
            'dampak_ekonomi'                                      => 'nullable|array',
            'dampak_ekonomi.kerugian_perumahan'                   => 'nullable|numeric|min:0',
            'dampak_ekonomi.kerugian_pertanian'                   => 'nullable|numeric|min:0',
            'dampak_ekonomi.kerugian_peternakan'                  => 'nullable|numeric|min:0',
            'dampak_ekonomi.kerugian_perikanan'                   => 'nullable|numeric|min:0',
            'dampak_ekonomi.kerugian_umkm'                        => 'nullable|numeric|min:0',
            'dampak_ekonomi.kerugian_infrastruktur'               => 'nullable|numeric|min:0',
            'dampak_ekonomi.kerugian_lainnya'                     => 'nullable|numeric|min:0',
            'dampak_ekonomi.estimasi_kerugian_total'              => 'nullable|numeric|min:0',
            'dampak_ekonomi.mata_pencaharian_hilang'              => 'nullable|integer|min:0',
            'dampak_ekonomi.usaha_terdampak'                      => 'nullable|integer|min:0',
            'dampak_ekonomi.metodologi_estimasi'                  => 'nullable|string|max:255',
            'dampak_ekonomi.catatan_ekonomi'                      => 'nullable|string',

            // Biodata Kejadian
            'biodata_kejadian'                                    => 'nullable|array',
            'biodata_kejadian.tanggal_mulai_kejadian'             => 'required_with:biodata_kejadian|date',
            'biodata_kejadian.jam_mulai_kejadian'                 => 'nullable|date_format:H:i',
            'biodata_kejadian.kronologi_singkat'                  => 'required_with:biodata_kejadian|string|max:1000',
            'biodata_kejadian.penyebab_utama'                     => 'nullable|string|max:255',
            'biodata_kejadian.skala_kejadian'                     => 'nullable|in:lokal,kecamatan,kabupaten,provinsi,nasional',
            'biodata_kejadian.status_masih_berlangsung'           => 'nullable|boolean',

            // Narasi Kejadian
            'narasi_kejadian'                                     => 'nullable|array',
            'narasi_kejadian.fase'                                => 'required_with:narasi_kejadian|in:pra_bencana,saat_bencana,pasca_bencana',
            'narasi_kejadian.judul_narasi'                        => 'required_with:narasi_kejadian|string|max:255',
            'narasi_kejadian.isi_narasi'                          => 'required_with:narasi_kejadian|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
