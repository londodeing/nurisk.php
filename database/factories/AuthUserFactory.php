<?php

namespace Database\Factories;

use App\Models\AuthUser;
use App\Models\AuthRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AuthUserFactory extends Factory
{
    protected $model = AuthUser::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_peran'    => AuthRole::where('nama_peran', 'relawan')->first()?->id_peran
                            ?? AuthRole::factory()->create(['nama_peran' => 'relawan', 'level_otoritas' => 40])->id_peran,
            'id_unit'     => null,
            'no_hp'       => '08' . str_pad(rand(0, 999999999), 10, '0', STR_PAD_LEFT),
            'kata_sandi'  => Hash::make('PasswordS1apS1aga!'),
            'status_akun' => AuthUser::STATUS_MENUNGGU,
            'is_tersedia' => 1,
            'terakhir_masuk' => null,
            'default_scope_type' => null,
            'default_scope_id' => null,
        ];
    }

    /**
     * State untuk akun aktif (sudah disetujui).
     */
    public function aktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_akun' => AuthUser::STATUS_AKTIF,
        ]);
    }

    /**
     * State untuk akun menunggu approval.
     */
    public function menunggu(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_akun' => AuthUser::STATUS_MENUNGGU,
        ]);
    }

    /**
     * State untuk akun suspend/diblokir.
     */
    public function suspend(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_akun' => AuthUser::STATUS_SUSPEND,
        ]);
    }
}
