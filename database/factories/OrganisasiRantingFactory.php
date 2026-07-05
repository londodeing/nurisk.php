<?php

namespace Database\Factories;

use App\Models\OrganisasiRanting;
use App\Models\OrganisasiMwc;
use App\Models\OrganisasiUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisasiRantingFactory extends Factory
{
    protected $model = OrganisasiRanting::class;

    public function definition(): array
    {
        return [
            'id_mwc' => OrganisasiMwc::factory(),
            'nama_ranting' => $this->faker->city(),
            'id_unit' => OrganisasiUnit::factory(),
        ];
    }
}
