<?php

namespace Database\Factories;

use App\Models\OperasiKlaster;
use App\Models\OperasiInsiden;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OperasiKlasterFactory extends Factory
{
    protected $model = OperasiKlaster::class;

    public function definition(): array
    {
        return [
            'uuid_klaster_operasi' => Str::uuid(),
            'id_insiden' => OperasiInsiden::factory(),
            'id_master_klaster' => function () {
                return \Illuminate\Support\Facades\DB::table('master_klaster')->insertGetId([
                    'nama_klaster' => fake()->word(),
                    'is_aktif' => 1
                ]);
            },
            'id_klaster' => 1,
            'status_klaster' => 'aktif',
            'prioritas' => 'sedang',
            'target_cakupan' => $this->faker->sentence(),
            'catatan' => null,
            'waktu_aktivasi' => now(),
            'waktu_ditutup' => null,
            'progres_persen' => 0,
            'dibutuhkan' => true,
            'id_pembuat' => null,
        ];
    }
}
