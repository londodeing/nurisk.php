<?php

namespace Database\Factories;

use App\Models\OperasiTugas;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperasiTugasFactory extends Factory
{
    protected $model = OperasiTugas::class;

    public function definition(): array
    {
        return [
            'id_operasi_klaster' => \App\Models\OperasiKlaster::factory(),
            'id_posaju' => null,
            'ditugaskan_ke' => null,
            'id_surat_perintah' => null,
            'judul_tugas' => $this->faker->sentence(),
            'target_indikator' => $this->faker->word(),
            'status_tugas' => 'rencana',
            'progres_persen' => 0,
        ];
    }
}
