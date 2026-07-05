<?php

namespace Database\Factories;

use App\Models\AuthPenggunaProfil;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthPenggunaProfilFactory extends Factory
{
    protected $model = AuthPenggunaProfil::class;

    public function definition(): array
    {
        return [
            'id_pengguna' => null,
            'nik' => $this->faker->unique()->numerify('##########'),
            'nama_lengkap' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'id_desa_domisili' => null,
        ];
    }

    public function forUser(int $idPengguna): static
    {
        return $this->state(fn (array $attributes) => [
            'id_pengguna' => $idPengguna,
        ]);
    }
}