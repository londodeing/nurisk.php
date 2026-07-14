<?php

namespace App\Services\Operasi;

use App\Models\OperasiJurnal;
use App\Models\OperasiPosaju;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;

class PosajuJurnalService
{
    public function catat(
        string $kategoriEvent,
        OperasiPosaju $posaju,
        ?string $deskripsi = null,
        ?AuthUser $user = null
    ): OperasiJurnal {
        $insiden = $posaju->insiden;

        if (!$insiden) {
            throw new \InvalidArgumentException('Pos Aju must have an insiden relation loaded');
        }

        return OperasiJurnal::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $user?->id_pengguna ?? auth()->id(),
            'kategori_event' => $kategoriEvent,
            'judul_event' => $this->getJudul($kategoriEvent, $posaju),
            'deskripsi_event' => $deskripsi ?? $this->getDefaultDeskripsi($kategoriEvent, $posaju),
            'id_referensi' => $posaju->id_posaju,
            'tabel_referensi' => 'operasi_posaju',
            'dibuat_pada' => now(),
        ]);
    }

    private function getJudul(string $kategoriEvent, OperasiPosaju $posaju): string
    {
        return match ($kategoriEvent) {
            'posaju_dibuat'         => "Pos Aju {$posaju->nama_posaju} Dibuat",
            'posaju_diaktifkan'     => "Pos Aju {$posaju->nama_posaju} Diaktifkan",
            'posaju_diperpanjang'   => "Pos Aju {$posaju->nama_posaju} Diperpanjang",
            'posaju_ditutup'        => "Pos Aju {$posaju->nama_posaju} Ditutup",
            'komandan_ditunjuk'     => "Komandan Pos Aju {$posaju->nama_posaju} Ditunjuk",
            'komandan_berakhir'     => "Tugas Komandan Pos Aju {$posaju->nama_posaju} Berakhir",
            'distribusi_dibuat'     => "Distribusi Bantuan Direncanakan di {$posaju->nama_posaju}",
            'distribusi_dikirim'    => "Distribusi Bantuan Dikirim dari {$posaju->nama_posaju}",
            'distribusi_direview'   => "Feedback Distribusi di {$posaju->nama_posaju} Disubmit",
            default                 => "Event Pos Aju: {$kategoriEvent}",
        };
    }

    private function getDefaultDeskripsi(string $kategoriEvent, OperasiPosaju $posaju): string
    {
        return match ($kategoriEvent) {
            'posaju_dibuat'         => "Pos Aju {$posaju->nama_posaju} dibuat manual oleh " . (auth()->user()?->profil?->nama_lengkap ?? 'sistem'),
            'posaju_diaktifkan'     => "Pos Aju {$posaju->nama_posaju} diaktifkan pada " . now()->format('d/m/Y H:i'),
            'posaju_diperpanjang'   => "Pos Aju {$posaju->nama_posaju} diperpanjang hingga " . ($posaju->diperpanjang_hingga?->format('d/m/Y H:i') ?? '—'),
            'posaju_ditutup'        => "Pos Aju {$posaju->nama_posaju} ditutup pada " . now()->format('d/m/Y H:i') . ". Alasan: " . ($posaju->alasan_penutupan ?? '—'),
            'komandan_ditunjuk'     => "Komandan baru ditunjuk untuk Pos Aju {$posaju->nama_posaju}",
            'komandan_berakhir'     => "Masa tugas komandan Pos Aju {$posaju->nama_posaju} berakhir",
            'distribusi_dibuat'     => "Rencana distribusi bantuan baru dibuat di Pos Aju {$posaju->nama_posaju}",
            'distribusi_dikirim'    => "Distribusi bantuan sudah dikirim dari Pos Aju {$posaju->nama_posaju}",
            'distribusi_direview'   => "Feedback distribusi bantuan sudah diisi dan terkunci",
            default                 => "Event {$kategoriEvent} pada Pos Aju {$posaju->nama_posaju}",
        };
    }
}