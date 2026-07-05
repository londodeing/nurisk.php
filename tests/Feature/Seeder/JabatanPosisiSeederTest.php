<?php

namespace Tests\Feature\Seeder;

use Tests\TestCase;
use Database\Seeders\JabatanPosisiSeeder;
use App\Models\JabatanPosisi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class JabatanPosisiSeederTest extends TestCase
{
    use DatabaseTransactions;



    public function test_seeder_imports_exactly_15_positions(): void
    {
        $this->seed(JabatanPosisiSeeder::class);

        $this->assertEquals(15, JabatanPosisi::count());
    }

    public function test_all_slugs_are_unique(): void
    {
        $this->seed(JabatanPosisiSeeder::class);

        $count = JabatanPosisi::count();
        $uniqueSlugCount = JabatanPosisi::distinct()->count('slug');

        $this->assertEquals($count, $uniqueSlugCount);
    }

    public function test_slug_ketua_pwnu_exists_with_correct_name(): void
    {
        $this->seed(JabatanPosisiSeeder::class);

        $this->assertDatabaseHas('master_jabatan', [
            'nama_jabatan' => 'Ketua PWNU',
            'slug' => 'ketua-pwnu',
        ]);
    }

    public function test_slug_relawan_umum_exists_at_last_record(): void
    {
        $this->seed(JabatanPosisiSeeder::class);

        $last = JabatanPosisi::orderBy('id_jabatan_posisi', 'desc')->first();

        $this->assertEquals(15, $last->id_jabatan_posisi);
        $this->assertEquals('Relawan Umum', $last->nama_jabatan);
        $this->assertEquals('relawan-umum', $last->slug);
    }

    public function test_seeder_is_idempotent(): void
    {
        // Jalankan seeder pertama kali
        $this->seed(JabatanPosisiSeeder::class);
        $this->assertEquals(15, JabatanPosisi::count());

        // Jalankan seeder kedua kali
        $this->seed(JabatanPosisiSeeder::class);
        $this->assertEquals(15, JabatanPosisi::count());
    }
}
