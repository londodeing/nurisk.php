<?php

namespace Database\Factories;

use App\Models\OperasiSuratKeluar;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperasiSuratKeluarFactory extends Factory
{
    protected $model = OperasiSuratKeluar::class;

    public function definition(): array
    {
        return [
            'id_jenis_surat' => function () {
                return \Illuminate\Support\Facades\DB::table('master_surat_jenis')->insertGetId([
                    'kode_jenis' => fake()->unique()->lexify('???'),
                    'nama_jenis' => fake()->word(),
                    'kategori' => 'UMUM'
                ]);
            },
            'nomor_surat_resmi' => $this->faker->unique()->numerify('SRT-####'),
            'perihal' => $this->faker->sentence(),
            'tgl_terbit' => now(),
            'id_pengguna_ttd' => \App\Models\AuthUser::factory(),
            'id_jabatan_ttd' => null,
            'isi_surat_snapshot' => $this->faker->paragraph(),
            'status_surat' => 'draft',
        ];
    }
}
