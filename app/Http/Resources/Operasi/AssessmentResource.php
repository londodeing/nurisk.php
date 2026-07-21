<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid_assessment' => $this->uuid_assessment,
            'uuid_insiden' => $this->insiden->uuid_insiden ?? null,
            'jenis_laporan' => $this->jenis_laporan,
            'cakupan_wilayah_deskripsi' => $this->cakupan_wilayah_deskripsi,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_latest' => $this->is_latest,
            'waktu_assesment' => $this->waktu_assesment?->toIso8601String(),
            'dampak_manusia' => $this->whenLoaded('dampakManusiaV2', fn() => [
                'meninggal'             => $this->dampakManusiaV2->meninggal,
                'hilang'                => $this->dampakManusiaV2->hilang,
                'luka_berat'            => $this->dampakManusiaV2->luka_berat,
                'luka_ringan'           => $this->dampakManusiaV2->luka_ringan,
                'terdampak_jiwa'        => $this->dampakManusiaV2->terdampak_jiwa,
                'terdampak_kk'          => $this->dampakManusiaV2->terdampak_kk,
                'pengungsi_jiwa'        => $this->dampakManusiaV2->pengungsi_jiwa,
                'pengungsi_kk'          => $this->dampakManusiaV2->pengungsi_kk,
                'pengungsi_balita'      => $this->dampakManusiaV2->pengungsi_balita,
                'pengungsi_lansia'      => $this->dampakManusiaV2->pengungsi_lansia,
                'pengungsi_disabilitas' => $this->dampakManusiaV2->pengungsi_disabilitas,
                'pengungsi_ibu_hamil'   => $this->dampakManusiaV2->pengungsi_ibu_hamil,
            ]),
            'dampak_rumah' => $this->whenLoaded('dampakRumah', fn() => [
                'rusak_berat'  => $this->dampakRumah->rusak_berat,
                'rusak_sedang' => $this->dampakRumah->rusak_sedang,
                'rusak_ringan' => $this->dampakRumah->rusak_ringan,
                'terendam'     => $this->dampakRumah->terendam,
                'terancam'     => $this->dampakRumah->terancam,
            ]),
            'dampak_fasum' => $this->whenLoaded('dampakFasum', fn() => [
                'sanitasi'    => $this->dampakFasum->sanitasi,
                'pendidikan'  => $this->dampakFasum->pendidikan,
                'kesehatan'   => $this->dampakFasum->kesehatan,
                'ibadah'      => $this->dampakFasum->ibadah,
                'komunikasi'  => $this->dampakFasum->komunikasi,
                'listrik'     => $this->dampakFasum->listrik,
                'kantor'      => $this->dampakFasum->kantor,
                'jembatan'    => $this->dampakFasum->jembatan,
                'pasar'       => $this->dampakFasum->pasar,
                'spbu'        => $this->dampakFasum->spbu,
            ]),
            'dampak_vital' => $this->whenLoaded('dampakVital', fn() => [
                'air_bersih'      => $this->dampakVital->air_bersih,
                'listrik'         => $this->dampakVital->listrik,
                'telekomunikasi'  => $this->dampakVital->telekomunikasi,
                'irigasi'         => $this->dampakVital->irigasi,
                'jalan'           => $this->dampakVital->jalan,
                'spbu'            => $this->dampakVital->spbu,
            ]),
            'dampak_lingkungan' => $this->whenLoaded('dampakLingkungan', fn() => [
                'lahan_pertanian_rusak_ha'       => $this->dampakLingkungan->lahan_pertanian_rusak_ha,
                'hutan_terdampak_ha'             => $this->dampakLingkungan->hutan_terdampak_ha,
                'lahan_tercemar_ha'              => $this->dampakLingkungan->lahan_tercemar_ha,
                'ternak_terdampak_ekor'          => $this->dampakLingkungan->ternak_terdampak_ekor ?? 0,
                'sumber_air_tercemar'            => (bool)($this->dampakLingkungan->sumber_air_tercemar ?? false),
                'pencemaran_tanah'               => (bool)($this->dampakLingkungan->pencemaran_tanah ?? false),
                'erosi_sedimentasi'              => (bool)($this->dampakLingkungan->erosi_sedimentasi ?? false),
                'kerusakan_ekosistem_pesisir'    => (bool)($this->dampakLingkungan->kerusakan_ekosistem_pesisir ?? false),
                'kerusakan_daerah_aliran_sungai' => (bool)($this->dampakLingkungan->kerusakan_daerah_aliran_sungai ?? false),
                'catatan_lingkungan'             => $this->dampakLingkungan->catatan_lingkungan,
            ]),
            'dampak_ekonomi' => $this->whenLoaded('dampakEkonomi', fn() => [
                'persentase_ekonomi_terdampak'  => $this->dampakEkonomi->persentase_ekonomi_terdampak,
                'sektor_pencaharian_1'          => $this->dampakEkonomi->sektor_pencaharian_1,
                'kontribusi_1'                  => $this->dampakEkonomi->kontribusi_1,
                'status_terdampak_1'            => $this->dampakEkonomi->status_terdampak_1,
                'sektor_pencaharian_2'          => $this->dampakEkonomi->sektor_pencaharian_2,
                'kontribusi_2'                  => $this->dampakEkonomi->kontribusi_2,
                'status_terdampak_2'            => $this->dampakEkonomi->status_terdampak_2,
                'sektor_pencaharian_3'          => $this->dampakEkonomi->sektor_pencaharian_3,
                'kontribusi_3'                  => $this->dampakEkonomi->kontribusi_3,
                'status_terdampak_3'            => $this->dampakEkonomi->status_terdampak_3,
                'distribusi_hasil_panen'        => $this->dampakEkonomi->distribusi_hasil_panen,
                'fasilitas_pengolahan_kolektif' => $this->dampakEkonomi->fasilitas_pengolahan_kolektif,
                'catatan_ekonomi'               => $this->dampakEkonomi->catatan_ekonomi,
            ]),
            'lokasi_detail' => $this->whenLoaded('lokasiDetail', fn() => [
                'id_kec'          => $this->lokasiDetail->id_kec,
                'id_desa'         => $this->lokasiDetail->id_desa,
                'alamat_spesifik' => $this->lokasiDetail->alamat_spesifik,
                'region_terdampak'=> $this->lokasiDetail->region_terdampak,
            ]),
            'narasi_detail' => $this->whenLoaded('narasiDetail', fn() => [
                'sebaran_dampak'   => $this->narasiDetail->sebaran_dampak,
                'kondisi_umum'     => $this->narasiDetail->kondisi_umum,
                'upaya_penanganan' => $this->narasiDetail->upaya_penanganan,
                'rekomendasi_aksi' => $this->narasiDetail->rekomendasi_aksi,
            ]),
            'kebutuhan_mendesak' => $this->whenLoaded('kebutuhanMendesak', function () {
                return $this->kebutuhanMendesak->map(fn($k) => [
                    'nama_kebutuhan' => $k->nama_kebutuhan,
                    'jumlah' => $k->jumlah,
                    'satuan' => $k->satuan,
                    'catatan' => $k->catatan,
                ]);
            }),
            'kebutuhan_lanjutan' => $this->whenLoaded('kebutuhanLanjutan', fn() => [
                'kebutuhan_dana'      => $this->kebutuhanLanjutan->kebutuhan_dana,
                'kebutuhan_relawan'   => $this->kebutuhanLanjutan->kebutuhan_relawan,
                'kebutuhan_logistik'  => $this->kebutuhanLanjutan->kebutuhan_logistik,
                'kebutuhan_peralatan' => $this->kebutuhanLanjutan->kebutuhan_peralatan,
                'kebutuhan_medis'     => $this->kebutuhanLanjutan->kebutuhan_medis,
                'kebutuhan_pangan'    => $this->kebutuhanLanjutan->kebutuhan_pangan,
            ]),
            'kebutuhan_numerik' => $this->whenLoaded('kebutuhanNumerik', function () {
                return $this->kebutuhanNumerik->map(fn($n) => [
                    'kode_item'        => $n->item->kode_item,
                    'nama_item'        => $n->item->nama_item,
                    'jumlah_dibutuhkan'=> $n->jumlah_dibutuhkan,
                    'jumlah_tersedia'  => $n->jumlah_tersedia,
                    'satuan'           => $n->satuan,
                    'prioritas'        => $n->prioritas,
                ]);
            }),
            'dibuat_pada' => $this->dibuat_pada?->toIso8601String(),
            'diperbarui_pada' => $this->diperbarui_pada?->toIso8601String(),
        ];
    }
}
