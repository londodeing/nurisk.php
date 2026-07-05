<?php

namespace Database\Factories;

use App\Models\BencanaMasterJenis;
use App\Models\LaporanKejadian;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaporanKejadianFactory extends Factory
{
    protected $model = LaporanKejadian::class;

    public function definition(): array
    {
        return [
            'kode_kejadian' => LaporanKejadian::generateKodeKejadian(),
            'id_pengguna' => null,
            'id_jenis_bencana' => BencanaMasterJenis::inRandomOrder()->first()?->id_jenis ?? 1,
            'nama_pelapor' => fake()->name(),
            'hp_pelapor' => fake()->phoneNumber(),
            'keterangan_situasi' => fake()->paragraph(),
            'titik_kenal' => fake()->address(),
            'waktu_kejadian' => fake()->dateTimeThisMonth(),
            'latitude' => fake()->latitude(-8, -6),
            'longitude' => fake()->longitude(109, 112),
            'photo_path' => null,
            'is_valid' => 'menunggu',
            'catatan_validasi' => null,
        ];
    }

    public function valid(): static
    {
        return $this->state(fn () => ['is_valid' => 'ya']);
    }

    public function ditolak(?string $alasan = null): static
    {
        return $this->state(fn () => [
            'is_valid'     => 'tidak',
            'alasan_tolak' => $alasan,
            'catatan_validasi' => fake()->sentence(),
        ]);
    }
}
