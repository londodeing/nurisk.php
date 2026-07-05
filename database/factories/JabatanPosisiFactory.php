<?php

namespace Database\Factories;

use App\Models\JabatanPosisi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JabatanPosisi>
 */
class JabatanPosisiFactory extends Factory
{
    protected $model = JabatanPosisi::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_jabatan' => fake()->words(3, true),
            'slug'         => fake()->unique()->slug(3),
            'deskripsi'    => fake()->sentence(),
        ];
    }
}
