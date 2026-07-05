<?php

namespace App\Services;

use App\Models\DokumenSuratUtama;
use App\Models\MasterSuratJenis;
use App\Models\OperasiInsiden;

class NomorSuratService
{
    public function generate(
        MasterSuratJenis $jenis,
        int $tahun,
        ?OperasiInsiden $insiden = null
    ): string {
        $format = $jenis->format_nomor ?? '{SEQ}/{KODE}/{BULAN_ROMAWI}/{TAHUN}';

        $seq = DokumenSuratUtama::whereHas('jenisSurat', fn($q) => $q->where('id_jenis_surat', $jenis->id_jenis_surat))
            ->whereYear('tgl_terbit', $tahun)
            ->withTrashed()
            ->count() + 1;

        $bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $pcnuNama = $insiden?->pcnu?->nama_pcnu ?? 'NURISK';

        $nomor = str_replace(
            ['{SEQ}', '{KODE}', '{PCNU}', '{BULAN_ROMAWI}', '{TAHUN}'],
            [
                str_pad($seq, 3, '0', STR_PAD_LEFT),
                $jenis->kode_jenis,
                strtoupper(str_replace(' ', '-', $pcnuNama)),
                $bulanRomawi[now()->month - 1],
                $tahun,
            ],
            $format
        );

        if (DokumenSuratUtama::withTrashed()->where('nomor_surat_resmi', $nomor)->exists()) {
            $nomor .= '-' . str_pad($seq + 1, 3, '0', STR_PAD_LEFT);
        }

        return $nomor;
    }
}
