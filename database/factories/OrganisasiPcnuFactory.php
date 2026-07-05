<?php

namespace Database\Factories;

use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisasiPcnuFactory extends Factory
{
    protected $model = OrganisasiPcnu::class;

    public function definition(): array
    {
        return [
            'id_unit' => OrganisasiUnit::factory(),
            'nama_pcnu' => $this->faker->city(),
        ];
    }
}
