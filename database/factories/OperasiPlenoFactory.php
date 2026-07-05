<?php

namespace Database\Factories;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPleno;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperasiPlenoFactory extends Factory
{
    protected $model = OperasiPleno::class;

    public function definition(): array
    {
        return [
            'id_insiden' => OperasiInsiden::factory(),
            'nomor_pleno' => 'TEST/PLENO/PCNU-TEST/' . fake()->monthName() . '/' . now()->year,
            'waktu_pleno' => now()->subHours(2),
            'jenis_pleno' => fake()->randomElement(['aktivasi_operasi', 'evaluasi_rutin', 'khusus']),
            'pimpinan_pleno' => AuthUser::factory(),
            'notulis_pleno' => AuthUser::factory(),
            'lokasi_pleno' => 'Posko Utama',
            'status_pleno' => 'draft',
        ];
    }

    public function sudahDitinjau(): static
    {
        return $this->state(['status_pleno' => 'ditinjau']);
    }

    public function sudahFinal(): static
    {
        return $this->state(['status_pleno' => 'final']);
    }
}
