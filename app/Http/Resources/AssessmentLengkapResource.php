<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentLengkapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id_assessment_utama,
            'jenis_laporan'   => $this->jenis_laporan,
            'waktu_assesment' => $this->waktu_assesment?->toIso8601String(),
            'is_latest'       => (bool)$this->is_latest,
            'petugas'         => $this->whenLoaded('petugas', fn() => [
                'id'   => $this->petugas->id_pengguna,
                'nama' => $this->petugas->profil?->nama_lengkap,
            ]),
            'lokasi'          => $this->whenLoaded('lokasiDetail', fn() => [
                'id_kec'          => $this->lokasiDetail?->id_kec,
                'nama_kec'        => $this->lokasiDetail?->kecamatan?->nama_kec,
                'id_desa'         => $this->lokasiDetail?->id_desa,
                'nama_desa'       => $this->lokasiDetail?->desa?->nama_desa,
                'alamat_spesifik' => $this->lokasiDetail?->alamat_spesifik,
                'region'          => $this->lokasiDetail?->region_terdampak,
                'latitude'        => $this->latitude,
                'longitude'       => $this->longitude,
            ]),
            'narasi'          => $this->whenLoaded('narasiDetail', fn() => [
                'kondisi_mutakhir' => $this->narasiDetail?->kondisi_umum,
                'upaya_penanganan' => $this->narasiDetail?->upaya_penanganan,
                'sebaran_dampak'   => $this->narasiDetail?->sebaran_dampak,
                'kendala_lapangan' => $this->narasiDetail?->kendala_lapangan,
                'kendala_tambahan' => $this->narasiDetail?->kendala_tambahan,
                'rekomendasi_aksi' => $this->narasiDetail?->rekomendasi_aksi,
            ]),
            'kebutuhan'       => $this->whenLoaded('kebutuhanLanjutan', fn() => [
                'dana'       => $this->kebutuhanLanjutan?->kebutuhan_dana,
                'relawan'    => $this->kebutuhanLanjutan?->kebutuhan_relawan,
                'logistik'   => $this->kebutuhanLanjutan?->kebutuhan_logistik,
                'peralatan'  => $this->kebutuhanLanjutan?->kebutuhan_peralatan,
                'medis'      => $this->kebutuhanLanjutan?->kebutuhan_medis,
                'pangan'     => $this->kebutuhanLanjutan?->kebutuhan_pangan,
                'lainnya'    => $this->kebutuhanLanjutan?->kebutuhan_lainnya,
            ]),
            'needs_numeric'   => $this->whenLoaded('kebutuhanNumerik', fn() => $this->kebutuhanNumerik->map(fn($item) => [
                'kode_item' => $item->item?->kode_item,
                'nama_item' => $item->item?->nama_item,
                'jumlah_dibutuhkan' => $item->jumlah_dibutuhkan,
                'satuan' => $item->satuan,
                'prioritas' => $item->prioritas,
                'keterangan' => $item->keterangan,
            ])),
            'dampak_manusia'  => $this->whenLoaded('dampakManusiaV2', fn() => [
                'meninggal' => $this->dampakManusiaV2?->meninggal,
                'hilang' => $this->dampakManusiaV2?->hilang,
                'luka_berat' => $this->dampakManusiaV2?->luka_berat,
                'luka_ringan' => $this->dampakManusiaV2?->luka_ringan,
                'terdampak_jiwa' => $this->dampakManusiaV2?->terdampak_jiwa,
                'terdampak_kk' => $this->dampakManusiaV2?->terdampak_kk,
                'pengungsi_jiwa' => $this->dampakManusiaV2?->pengungsi_jiwa,
                'pengungsi_kk' => $this->dampakManusiaV2?->pengungsi_kk,
            ]),
            'dampak_rumah'    => $this->whenLoaded('dampakRumah', fn() => [
                'rusak_berat' => $this->dampakRumah?->rusak_berat,
                'rusak_sedang' => $this->dampakRumah?->rusak_sedang,
                'rusak_ringan' => $this->dampakRumah?->rusak_ringan,
                'terendam' => $this->dampakRumah?->terendam,
                'terancam' => $this->dampakRumah?->terancam,
                'estimasi_kerugian_juta' => $this->dampakRumah?->estimasi_kerugian_juta,
            ]),
            'dampak_fasum'    => $this->whenLoaded('dampakFasum', fn() => [
                'sanitasi' => $this->dampakFasum?->sanitasi,
                'pendidikan' => $this->dampakFasum?->pendidikan,
                'kesehatan' => $this->dampakFasum?->kesehatan,
                'ibadah' => $this->dampakFasum?->ibadah,
                'komunikasi' => $this->dampakFasum?->komunikasi,
                'listrik' => $this->dampakFasum?->listrik,
                'kantor' => $this->dampakFasum?->kantor,
                'jembatan' => $this->dampakFasum?->jembatan,
                'pasar' => $this->dampakFasum?->pasar,
                'spbu' => $this->dampakFasum?->spbu,
            ]),
            'dampak_vital'    => $this->whenLoaded('dampakVital', fn() => [
                'air_bersih' => $this->dampakVital?->air_bersih,
                'listrik' => $this->dampakVital?->listrik,
                'telekomunikasi' => $this->dampakVital?->telekomunikasi,
                'irigasi' => $this->dampakVital?->irigasi,
                'jalan' => $this->dampakVital?->jalan,
            ]),
            'dampak_lingkungan' => $this->whenLoaded('dampakLingkungan', fn() => [
                'lahan_pertanian_rusak_ha' => $this->dampakLingkungan?->lahan_pertanian_rusak_ha,
                'hutan_terdampak_ha'       => $this->dampakLingkungan?->hutan_terdampak_ha,
                'lahan_tercemar_ha'        => $this->dampakLingkungan?->lahan_tercemar_ha,
                'ternak_terdampak_ekor'    => $this->dampakLingkungan?->ternak_terdampak_ekor ?? 0,
            ]),
        ];
    }
}
