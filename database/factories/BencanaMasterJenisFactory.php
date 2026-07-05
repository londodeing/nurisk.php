<?php

namespace Database\Factories;

use App\Models\BencanaMasterJenis;
use Illuminate\Database\Eloquent\Factories\Factory;

class BencanaMasterJenisFactory extends Factory
{
    protected $model = BencanaMasterJenis::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'nama_bencana' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'kategori' => $this->faker->randomElement(['alam', 'non_alam', 'sosial']),
            'deskripsi' => $this->faker->sentence(),
            'ikon_map' => 'default.png',
        ];
    }
}
