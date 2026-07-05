<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Database\Seeders\WilayahSeeder;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use App\Models\OrganisasiPcnu;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class WilayahApiTest extends TestCase
{
    use DatabaseTransactions;



    // === KABUPATEN ===

    public function test_get_kabupaten_returns_200_ok(): void
    {
        $this->seed(WilayahSeeder::class);

        $response = $this->getJson('/api/wilayah/kabupaten');
        
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json');

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertCount(35, $data);
        $first = $data[0] ?? [];
        $this->assertArrayHasKey('id_kab', $first);
        $this->assertArrayHasKey('nama_kab', $first);
    }

    // === KECAMATAN ===

    public function test_get_kecamatan_by_kabupaten_returns_200_ok(): void
    {
        $this->seed(WilayahSeeder::class);

        $response = $this->getJson('/api/wilayah/kecamatan?id_kab=3301');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json');

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $first = $data[0] ?? [];
        $this->assertArrayHasKey('id_kec', $first);
        $this->assertArrayHasKey('nama_kec', $first);
    }

    public function test_get_kecamatan_without_param_returns_422(): void
    {
        $response = $this->getJson('/api/wilayah/kecamatan');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_kab']);
    }

    public function test_get_kecamatan_with_invalid_id_kab_returns_422(): void
    {
        $response = $this->getJson('/api/wilayah/kecamatan?id_kab=9999');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_kab']);
    }

    // === DESA ===

    public function test_get_desa_by_kecamatan_returns_200_ok(): void
    {
        $this->seed(WilayahSeeder::class);

        $response = $this->getJson('/api/wilayah/desa?id_kec=330101');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json');

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $first = $data[0] ?? [];
        $this->assertArrayHasKey('id_desa', $first);
        $this->assertArrayHasKey('nama_desa', $first);
    }

    public function test_get_desa_without_param_returns_422(): void
    {
        $response = $this->getJson('/api/wilayah/desa');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_kec']);
    }

    public function test_get_desa_with_invalid_id_kec_returns_422(): void
    {
        $response = $this->getJson('/api/wilayah/desa?id_kec=999999');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_kec']);
    }

    // === PCNU ===

    public function test_get_pcnu_returns_200_ok(): void
    {
        $unit = \App\Models\OrganisasiUnit::create([
            'nama_unit' => 'Unit PCNU',
            'tipe_unit' => 'pcnu'
        ]);

        OrganisasiPcnu::create([
            'id_unit' => $unit->id_unit,
            'nama_pcnu' => 'PCNU Cilacap'
        ]);

        $response = $this->getJson('/api/wilayah/pcnu');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'nama']]]);
        
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }
}
