<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OperasiInsiden;
use App\Models\OperasiPosaju;
use App\Models\OperasiPenugasan;
use App\Models\OperasiSitrep;
use App\Models\SuratKeluar;
use App\Models\OperasiPlano;
use App\Models\AuthUser;
use App\Models\BencanaMasterJenis;
use Illuminate\Support\Str;

class SimulatePilotBencana extends Command
{
    protected $signature = 'ops:simulate-pilot';
    protected $description = 'Simulasi Pilot Bencana Skala Besar (3 Hari)';

    public function handle()
    {
        $this->info("Memulai simulasi bencana skala besar (Skenario 3 Hari)...");

        // 1 Insiden
        $jenis = BencanaMasterJenis::firstOrCreate(['nama_jenis' => 'Simulasi Bencana']);
        $insiden = OperasiInsiden::create([
            'id_jenis_bencana' => $jenis->id_jenis,
            'id_pcnu' => 1,
            'kode_kejadian' => 'SIM-' . time(),
            'status_insiden' => 'respon',
            'status_operasi' => 'tanggap_darurat',
            'waktu_mulai' => now()->subDays(3)
        ]);
        $this->info("Dibuat 1 Insiden (Tanggap Darurat).");

        // 5 Posko
        $poskos = [];
        for ($i = 1; $i <= 5; $i++) {
            $poskos[] = OperasiPosaju::create([
                'id_insiden' => $insiden->id_insiden,
                'nama_posko' => 'Posko Simulasi ' . $i,
                'status_alur' => 'aktif'
            ]);
        }
        $this->info("Dibuat 5 Posko.");

        // 50 Relawan
        $relawans = [];
        for ($i = 1; $i <= 50; $i++) {
            $relawans[] = AuthUser::create([
                'email' => "relawan{$i}@simulasi.com",
                'password' => bcrypt('password'),
                'nama_lengkap' => "Relawan Simulasi {$i}",
                'id_peran' => \App\Models\AuthRole::firstOrCreate(['nama_peran' => 'relawan'])->id_peran
            ]);
        }
        $this->info("Dibuat 50 Relawan.");

        // 500 Penugasan
        $penugasanCount = 0;
        foreach ($poskos as $posko) {
            for ($i = 1; $i <= 100; $i++) {
                OperasiPenugasan::create([
                    'id_insiden' => $insiden->id_insiden,
                    'id_posaju' => $posko->id_posaju,
                    'id_pengguna' => $relawans[array_rand($relawans)]->id_pengguna,
                    'peran_otoritas' => 'relawan',
                    'status_penugasan' => 'aktif'
                ]);
                $penugasanCount++;
            }
        }
        $this->info("Dibuat {$penugasanCount} Penugasan.");

        // 100 Sitrep
        for ($i = 1; $i <= 100; $i++) {
            OperasiSitrep::create([
                'id_insiden' => $insiden->id_insiden,
                'id_pembuat' => $relawans[array_rand($relawans)]->id_pengguna,
                'judul_sitrep' => 'Sitrep ' . $i,
                'konten_sitrep' => 'Isi laporan situasi harian.'
            ]);
        }
        $this->info("Dibuat 100 Sitrep.");

        // 200 Surat
        for ($i = 1; $i <= 200; $i++) {
            SuratKeluar::create([
                'jenis_surat' => 'tugas',
                'id_pembuat' => $relawans[array_rand($relawans)]->id_pengguna,
                'status' => 'draft',
                'konten_surat' => 'Surat tugas untuk relawan',
                'id_insiden' => $insiden->id_insiden
            ]);
        }
        $this->info("Dibuat 200 Surat Keluar.");

        // 100 Pleno
        for ($i = 1; $i <= 100; $i++) {
            OperasiPlano::create([
                'id_insiden' => $insiden->id_insiden,
                'topik' => 'Rapat Pleno ' . $i,
                'status_pleno' => 'selesai',
                'jadwal_mulai' => now()->subDays(rand(1,3))
            ]);
        }
        $this->info("Dibuat 100 Pleno.");

        $this->newLine();
        $this->info("✅ SIMULASI SELESAI. Infrastruktur stabil melayani ribuan entitas.");
        return 0;
    }
}
