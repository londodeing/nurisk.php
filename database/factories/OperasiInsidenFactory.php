<?php

namespace Database\Factories;

use App\Models\OperasiInsiden;
use App\Models\BencanaMasterJenis;
use App\Models\OrganisasiPcnu;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperasiInsidenFactory extends Factory
{
    protected $model = OperasiInsiden::class;

    public function definition(): array
    {
        return [
            'uuid_insiden'     => (string) \Illuminate\Support\Str::uuid(),
            'kode_kejadian'    => 'INS-' . $this->faker->unique()->numerify('#####'),
            'id_jenis_bencana' => BencanaMasterJenis::inRandomOrder()->first()?->id_jenis ?? BencanaMasterJenis::factory(),
            'id_pcnu'          => OrganisasiPcnu::inRandomOrder()->first()?->id_pcnu ?? OrganisasiPcnu::factory(),
            'status_insiden'   => $this->faker->randomElement(['draft', 'terverifikasi', 'respon']),
            'prioritas'        => $this->faker->randomElement(['rendah', 'sedang', 'tinggi']),
            'waktu_mulai'      => $this->faker->dateTimeBetween('-1 year', 'now'),
            'waktu_selesai'    => null,
            'is_locked'        => false,
        ];
    }

    /**
     * State for completed and locked incidents.
     */
    public function selesaiDanTerkunci(): static
    {
        return $this->state(fn() => [
            'status_insiden' => 'selesai',
            'is_locked'      => true,
            'waktu_ditutup'  => now(),
        ]);
    }

    /**
     * State for active incidents currently in response.
     */
    public function sedangRespon(): static
    {
        return $this->state(fn() => [
            'status_insiden'       => 'respon',
            'waktu_respon_dimulai' => now()->subHours(2),
        ]);
    }
}
