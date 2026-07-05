<?php

namespace Database\Factories;

use App\Models\AuthRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthRoleFactory extends Factory
{
    protected $model = AuthRole::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_peran' => $this->faker->unique()->word(),
            'deskripsi' => $this->faker->sentence(),
            'level_otoritas' => $this->faker->numberBetween(1, 5),
        ];
    }

    public function create($attributes = [], ?\Illuminate\Database\Eloquent\Model $parent = null)
    {
        if (isset($attributes['nama_peran'])) {
            $existing = \App\Models\AuthRole::where('nama_peran', $attributes['nama_peran'])->first();
            if ($existing) {
                return $existing;
            }
        }
        $role = parent::create($attributes, $parent);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role->nama_peran, 'guard_name' => 'api']);
        return $role;
    }
}
