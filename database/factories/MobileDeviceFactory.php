<?php

namespace Database\Factories;

use App\Models\AuthUser;
use App\Models\MobileDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

class MobileDeviceFactory extends Factory
{
    protected $model = MobileDevice::class;

    public function definition(): array
    {
        return [
            'uuid_device' => $this->faker->uuid(),
            'id_pengguna' => AuthUser::factory(),
            'platform' => $this->faker->randomElement(['android', 'ios']),
            'app_version' => $this->faker->randomElement(['1.0.0', '1.1.0', '1.2.0']),
            'status' => 'active',
            'trust_score' => 100,
            'dibuat_pada' => now(),
            'diperbarui_pada' => now(),
        ];
    }
}
