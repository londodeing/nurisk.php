<?php

namespace Database\Seeders\Operasi;

use Illuminate\Database\Seeder;
use App\Models\OperasiPosaju;
use App\Models\OperasiInsiden;
use App\Models\OperasiPlenoKeputusan;
use App\Models\AuthUser;

class PosAjuSeeder extends Seeder
{
    public function run(): void
    {
        $insiden = OperasiInsiden::where('is_locked', false)->first();
        if (!$insiden) {
            $this->command->warn('No unlocked insiden found, skipping Pos Aju seeder.');
            return;
        }

        $plenoKeputusan = OperasiPlenoKeputusan::where('kategori_objek', 'aktivasi_posko')
            ->whereNull('referensi_tabel')
            ->whereIn('status_pelaksanaan', ['pending', 'dijadwalkan'])
            ->first();

        $pj = AuthUser::where('status_akun', 'aktif')->first();
        if (!$pj) {
            $this->command->warn('No active user found for PJ, skipping Pos Aju seeder.');
            return;
        }

        $posajus = [
            [
                'id_insiden' => $insiden->id_insiden,
                'id_pleno_keputusan' => $plenoKeputusan?->id_keputusan,
                'nama_posaju' => 'Pos Aju Pusat ' . $insiden->kode_kejadian,
                'id_surat_pendirian' => null,
                'alamat_lokasi' => 'Gedung PCNU, Jl. Raya Kudus No. 123',
                'latitude' => -6.8114,
                'longitude' => 110.8440,
                'pj_posaju' => $pj->id_pengguna,
                'status_alur' => 'aktif',
                'waktu_diaktifkan' => now(),
            ],
            [
                'id_insiden' => $insiden->id_insiden,
                'id_pleno_keputusan' => $plenoKeputusan?->id_keputusan,
                'nama_posaju' => 'Pos Aju Rayon ' . $insiden->kode_kejadian,
                'id_surat_pendirian' => null,
                'alamat_lokasi' => 'Balai Desa, Jl. Raya No. 456',
                'latitude' => -6.8150,
                'longitude' => 110.8500,
                'pj_posaju' => $pj->id_pengguna,
                'status_alur' => 'direncanakan',
            ],
            [
                'id_insiden' => $insiden->id_insiden,
                'id_pleno_keputusan' => $plenoKeputusan?->id_keputusan,
                'nama_posaju' => 'Pos Aju Kelompok ' . $insiden->kode_kejadian,
                'id_surat_pendirian' => null,
                'alamat_lokasi' => 'Posko Kec. Gebog, Jl. Sudirman No. 789',
                'latitude' => -6.8080,
                'longitude' => 110.8380,
                'pj_posaju' => $pj->id_pengguna,
                'status_alur' => 'ditutup',
                'waktu_diaktifkan' => now()->subDays(10),
                'waktu_ditutup' => now()->subDay(),
                'alasan_penutupan' => 'Bencana dinyatakan selesai, evakuasi selesai',
            ],
        ];

        foreach ($posajus as $data) {
            OperasiPosaju::create($data);
        }

        $this->command->info('Created ' . count($posajus) . ' Pos Aju records for insiden ' . $insiden->kode_kejadian);
    }
}