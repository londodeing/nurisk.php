<?php

namespace Database\Factories;

use App\Models\WilayahKabupaten;
use Illuminate\Database\Eloquent\Factories\Factory;

class WilayahKabupatenFactory extends Factory
{
    protected $model = WilayahKabupaten::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_kab' => $this->faker->unique()->numerify('33##'),
            'nama_kab' => $this->faker->city(),
            'tipe' => $this->faker->randomElement(['Kabupaten', 'Kota']),
        ];
    }
}
