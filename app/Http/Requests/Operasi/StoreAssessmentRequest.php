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
        $minMeninggal = 0;

        if ($this->uuid_insiden) {
            $insiden = \App\Models\OperasiInsiden::where('uuid_insiden', $this->uuid_insiden)->first();
            if ($insiden) {
                $query = \App\Models\AssessmentUtama::where('id_insiden', $insiden->id_insiden)
                    ->latest('id_assessment_utama');
                
                if ($isUpdate && $this->route('assessment')) {
                    $assessmentId = $this->route('assessment') instanceof \App\Models\AssessmentUtama 
                        ? $this->route('assessment')->id_assessment_utama 
                        : $this->route('assessment');
                    $query->where('id_assessment_utama', '<', $assessmentId);
                }

                $previousAssessment = $query->first();
                if ($previousAssessment) {
                    $minMeninggal = $previousAssessment->dampakManusiaV2?->meninggal ?? 0;
                }
            }
        }

        return [
            'uuid_insiden'             => 'required|exists:operasi_insiden,uuid_insiden',
            'jenis_laporan'            => 'required|in:kaji_cepat,pendataan_lanjutan',
            'cakupan_wilayah_deskripsi'=> 'required|string|min:10|max:255',
            'latitude'                 => 'nullable|numeric|min:-11|max:6',
            'longitude'                => 'nullable|numeric|min:95|max:141',
            'waktu_assesment'          => 'required|date|before_or_equal:now',

            // Dampak Manusia V1 (core) + V2 fields (extended)
            'dampak_manusia'                           => 'required|array',
            'dampak_manusia.meninggal'                 => 'nullable|integer|min:' . $minMeninggal,
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

            // Kebutuhan Numerik (V2)
            'kebutuhan_numerik'                        => 'nullable|array',
            'kebutuhan_numerik.*.id_item'              => 'required_with:kebutuhan_numerik|integer|exists:assessment_kebutuhan_numerik_master,id_item',
            'kebutuhan_numerik.*.jumlah_dibutuhkan'    => 'required_with:kebutuhan_numerik|numeric|min:0',
            'kebutuhan_numerik.*.jumlah_tersedia'      => 'nullable|numeric|min:0',
            'kebutuhan_numerik.*.satuan'               => 'nullable|string|max:30',
            'kebutuhan_numerik.*.prioritas'            => 'nullable|in:darurat,penting,normal',
            'kebutuhan_numerik.*.keterangan'           => 'nullable|string|max:255',

            // Dampak Infrastruktur (V1 + V2)
            'dampak_infrastruktur'                                => 'nullable|array',
            'dampak_infrastruktur.rumah_rusak_berat'              => 'nullable|integer|min:0',
            'dampak_infrastruktur.rumah_rusak_sedang'             => 'nullable|integer|min:0',
            'dampak_infrastruktur.rumah_rusak_ringan'             => 'nullable|integer|min:0',
            'dampak_infrastruktur.rumah_terendam'                 => 'nullable|integer|min:0',
            'dampak_infrastruktur.rumah_terancam'                 => 'nullable|integer|min:0',
            'dampak_infrastruktur.fasilitas_kesehatan_rusak'      => 'nullable|integer|min:0',
            'dampak_infrastruktur.fasilitas_pendidikan_rusak'     => 'nullable|integer|min:0',
            'dampak_infrastruktur.tempat_ibadah_rusak'            => 'nullable|integer|min:0',
            'dampak_infrastruktur.kantor_pemerintah_rusak'        => 'nullable|integer|min:0',
            'dampak_infrastruktur.sanitasi'                       => 'nullable|integer|min:0',
            'dampak_infrastruktur.pasar'                          => 'nullable|integer|min:0',
            'dampak_infrastruktur.spbu'                           => 'nullable|integer|min:0',
            'dampak_infrastruktur.jalan_rusak_km'                 => 'nullable|numeric|min:0',
            'dampak_infrastruktur.jembatan_putus'                 => 'nullable|integer|min:0',
            'dampak_infrastruktur.jembatan_rusak'                 => 'nullable|integer|min:0',
            'dampak_infrastruktur.sarana_air_bersih_rusak'        => 'nullable|boolean',
            'dampak_infrastruktur.jaringan_listrik_padam_kk'      => 'nullable|integer|min:0',
            'dampak_infrastruktur.jaringan_komunikasi_putus'      => 'nullable|boolean',
            'dampak_infrastruktur.irigasi'                        => 'nullable|numeric|min:0',
            'dampak_infrastruktur.sawah_ha'                       => 'nullable|numeric|min:0',
            'dampak_infrastruktur.ternak_ekor'                    => 'nullable|integer|min:0',
            'dampak_infrastruktur.hutan_ha'                       => 'nullable|numeric|min:0',
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
            'dampak_lingkungan.catatan_lingkungan'                => 'nullable|string',
            'dampak_lingkungan.unggas'                            => 'nullable|integer|min:0',
            'dampak_lingkungan.kaki_empat'                        => 'nullable|integer|min:0',
            'dampak_lingkungan.perikanan_kolam'                   => 'nullable|numeric|min:0',
            'dampak_lingkungan.perikanan_nelayan'                 => 'nullable|integer|min:0',

            // Dampak Ekonomi
            'dampak_ekonomi'                                      => 'nullable|array',
            'dampak_ekonomi.persentase_ekonomi_terdampak'         => 'nullable|in:< 25%,25% - 50%,51% - 75%,> 75%',
            'dampak_ekonomi.sektor_pencaharian_1'                 => 'nullable|string|max:255',
            'dampak_ekonomi.kontribusi_1'                         => 'nullable|numeric|min:0|max:100',
            'dampak_ekonomi.status_terdampak_1'                   => 'nullable|in:tidak_terdampak,sementara,permanen',
            'dampak_ekonomi.sektor_pencaharian_2'                 => 'nullable|string|max:255',
            'dampak_ekonomi.kontribusi_2'                         => 'nullable|numeric|min:0|max:100',
            'dampak_ekonomi.status_terdampak_2'                   => 'nullable|in:tidak_terdampak,sementara,permanen',
            'dampak_ekonomi.sektor_pencaharian_3'                 => 'nullable|string|max:255',
            'dampak_ekonomi.kontribusi_3'                         => 'nullable|numeric|min:0|max:100',
            'dampak_ekonomi.status_terdampak_3'                   => 'nullable|in:tidak_terdampak,sementara,permanen',
            'dampak_ekonomi.distribusi_hasil_panen'               => 'nullable|in:berfungsi,rusak_sebagian,rusak_total',
            'dampak_ekonomi.fasilitas_pengolahan_kolektif'        => 'nullable|in:berfungsi,rusak_sebagian,rusak_total',
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

            // Lokasi Detail (V2)
            'lokasi_detail'                                       => 'nullable|array',
            'lokasi_detail.id_kec'                                => 'nullable|exists:wilayah_kecamatan,id_kec',
            'lokasi_detail.id_desa'                               => 'nullable|exists:wilayah_desa,id_desa',
            'lokasi_detail.region_terdampak'                      => 'nullable|string|max:255',

            // Narasi Detail (V2)
            'narasi_detail'                                       => 'nullable|array',
            'narasi_detail.sebaran_dampak'                        => 'nullable|string',
            'narasi_detail.kondisi_umum'                          => 'nullable|string',
            'narasi_detail.upaya_penanganan'                      => 'nullable|string',
            'narasi_detail.kendala_lapangan'                      => 'nullable|string',
            'narasi_detail.kendala_tambahan'                      => 'nullable|string',
            'narasi_detail.rekomendasi_aksi'                      => 'nullable|string',

            // Kebutuhan Lanjutan (V2)
            'kebutuhan_lanjutan'                                  => 'nullable|array',
            'kebutuhan_lanjutan.kebutuhan_relawan'                => 'nullable|string',
            'kebutuhan_lanjutan.kebutuhan_logistik'               => 'nullable|string',
            'kebutuhan_lanjutan.kebutuhan_peralatan'              => 'nullable|string',
            'kebutuhan_lanjutan.kebutuhan_medis'                  => 'nullable|string',
            'kebutuhan_lanjutan.kebutuhan_pangan'                 => 'nullable|string',
            'kebutuhan_lanjutan.kebutuhan_lainnya'                => 'nullable|string',
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
