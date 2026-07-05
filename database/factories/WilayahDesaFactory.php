<?php

namespace Database\Factories;

use App\Models\WilayahDesa;
use App\Models\WilayahKecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

class WilayahDesaFactory extends Factory
{
    protected $model = WilayahDesa::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_desa' => $this->faker->unique()->numerify('33########'),
            'id_kec' => WilayahKecamatan::factory(),
            'nama_desa' => $this->faker->streetAddress(),
        ];
    }
}
