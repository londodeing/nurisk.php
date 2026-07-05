<?php

namespace Tests\Feature\Seeder;

use Tests\TestCase;
use Database\Seeders\WilayahSeeder;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class WilayahSeederTest extends TestCase
{
    use DatabaseTransactions;



    public function test_seeder_imports_exactly_35_kabupaten_cities(): void
    {
        $this->seed(WilayahSeeder::class);

        $this->assertEquals(35, WilayahKabupaten::count());
    }

    public function test_all_seeded_data_has_valid_type(): void
    {
        $this->seed(WilayahSeeder::class);

        $kabupatens = WilayahKabupaten::all();
        foreach ($kabupatens as $kab) {
            $this->assertTrue(in_array($kab->tipe, ['Kabupaten', 'Kota']));
        }
    }

    public function test_cilacap_exists_with_id_3301(): void
    {
        $this->seed(WilayahSeeder::class);

        $this->assertDatabaseHas('wilayah_kabupaten', [
            'id_kab'   => '3301',
            'nama_kab' => 'Kabupaten Cilacap',
            'tipe'     => 'Kabupaten',
        ]);
    }

    public function test_semarang_city_exists_with_id_3374(): void
    {
        $this->seed(WilayahSeeder::class);

        $this->assertDatabaseHas('wilayah_kabupaten', [
            'id_kab'   => '3374',
            'nama_kab' => 'Kota Semarang',
            'tipe'     => 'Kota',
        ]);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(WilayahSeeder::class);
        $this->assertEquals(35, WilayahKabupaten::count());

        $this->seed(WilayahSeeder::class);
        $this->assertEquals(35, WilayahKabupaten::count());
    }
}
