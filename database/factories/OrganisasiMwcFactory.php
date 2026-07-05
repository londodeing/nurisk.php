<?php

namespace Database\Factories;

use App\Models\OrganisasiMwc;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisasiMwcFactory extends Factory
{
    protected $model = OrganisasiMwc::class;

    public function definition(): array
    {
        return [
            'id_pcnu' => OrganisasiPcnu::factory(),
            'nama_mwc' => $this->faker->city(),
            'id_unit' => OrganisasiUnit::factory(),
        ];
    }
}
