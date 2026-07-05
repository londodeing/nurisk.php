<?php

namespace Database\Factories;

use App\Models\OperasiPosaju;
use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperasiPosajuFactory extends Factory
{
    protected $model = OperasiPosaju::class;

    public function definition(): array
    {
        return [
            'id_insiden' => OperasiInsiden::factory(),
            'nama_posaju' => $this->faker->company() . ' Posaju',
            'pj_posaju' => AuthUser::factory(),
            'status_alur' => 'aktif',
            'waktu_diaktifkan' => now(),
            'alamat_lokasi' => $this->faker->address(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
        ];
    }
}
