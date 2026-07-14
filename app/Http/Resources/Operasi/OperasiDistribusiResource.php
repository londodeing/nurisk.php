<?php

namespace App\Http\Resources\Operasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperasiDistribusiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_distribusi' => $this->id_distribusi,
            'uuid_distribusi' => $this->uuid_distribusi,
            'posaju' => $this->whenLoaded('posaju', fn() => [
                'id_posaju' => $this->posaju->id_posaju,
                'nama_posaju' => $this->posaju->nama_posaju,
            ]),
            'klaster' => $this->whenLoaded('klasterOperasi', fn() => [
                'id_klaster_operasi' => $this->klasterOperasi->id_klaster_operasi,
                'nama_klaster' => $this->klasterOperasi->masterKlaster?->nama_klaster,
            ]),
            'barang' => [
                'id_barang_katalog' => $this->id_barang_katalog,
                'nama_barang' => $this->nama_barang,
                'jumlah' => $this->jumlah,
                'satuan' => $this->satuan,
            ],
            'lokasi_tujuan' => $this->lokasi_tujuan,
            'penerima' => $this->penerima,
            'waktu_distribusi' => $this->waktu_distribusi?->toIso8601String(),
            'status_distribusi' => $this->status_distribusi,
            'status_label' => $this->labelStatus(),
            'feedback' => $this->whenLoaded('feedback', fn() => [
                'id_feedback' => $this->feedback->id_feedback,
                'kecukupan' => $this->feedback->kecukupan,
                'kualitas' => $this->feedback->kualitas,
                'tepat_waktu' => $this->feedback->tepat_waktu,
                'tepat_sasaran' => $this->feedback->tepat_sasaran,
                'kendala' => $this->feedback->kendala,
                'rekomendasi' => $this->feedback->rekomendasi,
                'status_feedback' => $this->feedback->status_feedback,
                'dikunci_pada' => $this->feedback->dikunci_pada?->toIso8601String(),
                'pengguna' => $this->feedback->pengguna?->profil?->nama_lengkap,
            ]),
            'dibuat_oleh' => $this->whenLoaded('pembuat', fn() => $this->pembuat->profil?->nama_lengkap),
            'dibuat_pada' => $this->dibuat_pada?->toIso8601String(),
        ];
    }
}