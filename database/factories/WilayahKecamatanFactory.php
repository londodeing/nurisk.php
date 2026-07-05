<?php

namespace Database\Factories;

use App\Models\WilayahKecamatan;
use App\Models\WilayahKabupaten;
use Illuminate\Database\Eloquent\Factories\Factory;

class WilayahKecamatanFactory extends Factory
{
    protected $model = WilayahKecamatan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_kec' => $this->faker->unique()->numerify('33####'),
            'id_kab' => WilayahKabupaten::factory(),
            'nama_kec' => $this->faker->streetName(),
        ];
    }
}
