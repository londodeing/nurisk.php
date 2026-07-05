<?php

namespace App\Services;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use App\Models\OperasiPlenoKeputusan;
use App\Models\OperasiPlenoPeserta;
use App\Models\OperasiEskalasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlanoService
{
    public function buatPlano(array $data): OperasiPleno
    {
        if (empty($data['nomor_pleno'])) {
            $data['nomor_pleno'] = $this->generateNomorPlano((int) $data['id_insiden']);
        }
        return OperasiPleno::create($data);
    }

    public function tambahKeputusan(OperasiPleno $pleno, array $data): OperasiPlenoKeputusan
    {
        if ($pleno->isFinal()) {
            throw new \RuntimeException('Pleno sudah final — tidak dapat menambah keputusan.');
        }
        return $pleno->keputusan()->create($data);
    }

    public function tambahPeserta(OperasiPleno $pleno, int $idPengguna, array $data = []): OperasiPlenoPeserta
    {
        if (!$pleno->isDraft()) {
            throw new \RuntimeException('Peserta hanya bisa ditambahkan saat pleno berstatus draft.');
        }
        return $pleno->peserta()->create(array_merge([
            'id_pengguna' => $idPengguna,
        ], $data));
    }

    public function updateVotePeserta(OperasiPlenoPeserta $peserta, string $statusPersetujuan, ?string $catatan): void
    {
        if ($peserta->pleno->isFinal()) {
            throw new \RuntimeException('Pleno sudah final — vote tidak dapat diubah.');
        }
        $peserta->update([
            'status_persetujuan' => $statusPersetujuan,
            'catatan_peserta' => $catatan,
        ]);
    }

    public function finalisasi(OperasiPleno $pleno, AuthUser $aktor): OperasiPleno
    {
        if ($pleno->status_pleno !== 'ditinjau') {
            throw new \RuntimeException(
                'Pleno wajib berstatus "ditinjau" sebelum dapat difinalisasi. Status saat ini: ' . $pleno->status_pleno
            );
        }

        return DB::transaction(function () use ($pleno, $aktor) {
            $pleno->update([
                'status_pleno' => 'final',
                'disetujui_oleh' => $aktor->id_pengguna,
                'waktu_disetujui' => now(),
                'waktu_difinalisasi' => now(),
            ]);

            $this->catatJurnal($pleno, $aktor, 'Pleno difinalisasi');

            return $pleno->fresh();
        });
    }

    public function eskalasiInsiden(
        OperasiInsiden $insiden,
        array $data,
        AuthUser $aktor
    ): OperasiEskalasi {
        $hierarki = ['lokal' => 1, 'pcnu' => 2, 'pwnu' => 3, 'nasional' => 4];

        if (($hierarki[$data['level_baru']] ?? 0) <= ($hierarki[$data['level_sebelumnya']] ?? 0)) {
            throw new \InvalidArgumentException(
                'Level eskalasi baru (' . $data['level_baru'] . ') harus lebih tinggi dari level sebelumnya (' . $data['level_sebelumnya'] . ').'
            );
        }

        $pleno = OperasiPleno::where('id_pleno', $data['id_pleno'])
            ->where('id_insiden', $insiden->id_insiden)
            ->firstOrFail();

        return DB::transaction(function () use ($insiden, $pleno, $data, $aktor) {
            $eskalasi = OperasiEskalasi::create([
                'id_insiden' => $insiden->id_insiden,
                'id_pleno' => $pleno->id_pleno,
                'level_sebelumnya' => $data['level_sebelumnya'],
                'level_baru' => $data['level_baru'],
                'alasan_eskalasi' => $data['alasan_eskalasi'],
            ]);

            $this->catatJurnal($pleno, $aktor, 'Eskalasi insiden dari ' . $data['level_sebelumnya'] . ' ke ' . $data['level_baru']);

            return $eskalasi;
        });
    }

    private function generateNomorPlano(int $idInsiden): string
    {
        $insiden = OperasiInsiden::findOrFail($idInsiden);
        $pcnu = $insiden->pcnu;
        $count = OperasiPleno::where('id_insiden', $idInsiden)->count() + 1;
        $bulanRomawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bulan = $bulanRomawi[now()->month - 1];
        $tahun = now()->year;
        $namaPcnu = strtoupper(str_replace(' ', '-', $pcnu->nama_pcnu ?? 'PCNU'));

        return str_pad($count, 3, '0', STR_PAD_LEFT) . '/PLENO/' . $namaPcnu . '/' . $bulan . '/' . $tahun;
    }

    private function catatJurnal(OperasiPleno $pleno, AuthUser $aktor, string $judul): void
    {
        if (Schema::hasTable('operasi_jurnal')) {
            \App\Models\OperasiJurnal::create([
                'id_insiden' => $pleno->id_insiden,
                'id_pengguna' => $aktor->id_pengguna,
                'kategori_event' => 'sistem',
                'judul_event' => $judul,
                'deskripsi_event' => 'Pleno ID: ' . $pleno->id_pleno . ' — ' . $pleno->nomor_pleno,
                'id_referensi' => $pleno->id_pleno,
                'tabel_referensi' => 'operasi_pleno',
            ]);
        }
    }
}
