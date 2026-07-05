<?php

namespace Database\Factories;

use App\Models\AssessmentUtama;
use App\Models\OperasiInsiden;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentUtamaFactory extends Factory
{
    protected $model = AssessmentUtama::class;

    public function definition(): array
    {
        return [
            'id_insiden' => OperasiInsiden::factory(),
            'jenis_laporan' => $this->faker->randomElement(['kaji_cepat', 'pendataan_lanjutan']),
            'cakupan_wilayah_deskripsi' => $this->faker->address,
            'latitude' => $this->faker->randomFloat(8, -11, 6),
            'longitude' => $this->faker->randomFloat(8, 95, 141),
            'is_latest' => false,
            'waktu_assesment' => $this->faker->dateTimeThisYear(),
            'dibuat_pada' => now(),
            'diperbarui_pada' => now(),
        ];
    }
}
