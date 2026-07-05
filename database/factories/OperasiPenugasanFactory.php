<?php

namespace Database\Factories;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiPenugasan;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperasiPenugasanFactory extends Factory
{
    protected $model = OperasiPenugasan::class;

    public function definition(): array
    {
        return [
            'id_insiden' => OperasiInsiden::factory(),
            'id_pengguna' => AuthUser::factory(),
            'peran_otoritas' => 'relawan',
            'ditugaskan_oleh' => AuthUser::factory(),
            'waktu_mulai' => now()->subDays(2),
            'waktu_selesai' => null,
            'status_penugasan' => 'aktif',
            'uuid_penugasan' => $this->faker->uuid,
        ];
    }
}
