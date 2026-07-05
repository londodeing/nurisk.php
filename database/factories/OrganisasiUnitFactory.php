<?php

namespace Database\Factories;

use App\Models\OrganisasiUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisasiUnitFactory extends Factory
{
    protected $model = OrganisasiUnit::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'nama_unit' => $this->faker->company(),
            'tipe_unit' => 'pcnu',
            'id_wilayah' => null,
        ];
    }
}
