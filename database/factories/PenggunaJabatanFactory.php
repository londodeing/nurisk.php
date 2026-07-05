<?php

namespace Database\Factories;

use App\Models\PenggunaJabatan;
use App\Models\AuthUser;
use App\Models\JabatanPosisi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PenggunaJabatan>
 */
class PenggunaJabatanFactory extends Factory
{
    protected $model = PenggunaJabatan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pengguna'       => AuthUser::factory(),
            'id_jabatan_posisi' => JabatanPosisi::factory(),
            'tipe_lingkup'      => fake()->randomElement(['pwnu','pcnu','mwc','ranting']),
            'id_lingkup'        => fake()->numberBetween(1, 10),
            'ditugaskan_pada'   => now(),
            'berakhir_pada'     => null,
            'status_aktif'      => true,
        ];
    }

    /**
     * State untuk jabatan yang sudah berakhir.
     */
    public function sudahBerakhir(): static
    {
        return $this->state(fn() => [
            'berakhir_pada' => now()->subDays(30),
            'status_aktif'  => false,
        ]);
    }
}
