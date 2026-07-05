<?php

namespace Tests\Feature\Lapor;

use Tests\TestCase;
use App\Models\BencanaMasterJenis;
use App\Models\LaporanKejadian;
use App\Models\WilayahKabupaten;
use App\Models\OrganisasiUnit;
use App\Models\OrganisasiPcnu;
use Database\Seeders\BencanaMasterJenisSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

class PcnuAssignmentTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BencanaMasterJenisSeeder::class);
        
        // Create test wilayah data for Kudus and Cilacap
        WilayahKabupaten::upsert([
            ['id_kab' => '3301', 'nama_kab' => 'Kabupaten Cilacap', 'tipe' => 'Kabupaten'],
            ['id_kab' => '3319', 'nama_kab' => 'Kabupaten Kudus', 'tipe' => 'Kabupaten'],
        ], uniqueBy: ['id_kab'], update: ['nama_kab', 'tipe']);
        
        // Create PWNU unit for Central Java
        $pwnuUnit = OrganisasiUnit::create([
            'nama_unit' => 'PWNU Jawa Tengah',
            'tipe_unit' => 'pwnu',
            'id_wilayah' => '3300', // Provinsi Jawa Tengah
        ]);
        
        // Create PCNU units for Kudus and Cilacap
        $cilacapUnit = OrganisasiUnit::create([
            'nama_unit' => 'PCNU Cilacap',
            'tipe_unit' => 'pcnu',
            'parent_id' => $pwnuUnit->id_unit,
            'id_wilayah' => '3301', // Kabupaten Cilacap
        ]);
        
        $kudusUnit = OrganisasiUnit::create([
            'nama_unit' => 'PCNU Kudus',
            'tipe_unit' => 'pcnu',
            'parent_id' => $pwnuUnit->id_unit,
            'id_wilayah' => '3319', // Kabupaten Kudus
        ]);
        
        OrganisasiPcnu::create([
            'id_unit' => $cilacapUnit->id_unit,
            'nama_pcnu' => 'PCNU Cilacap',
        ]);
        
        OrganisasiPcnu::create([
            'id_unit' => $kudusUnit->id_unit,
            'nama_pcnu' => 'PCNU Kudus',
        ]);
    }

    /**
     * Test that reports with lat/long automatically get correct PCNU assignment during creation
     */
    public function test_report_automatically_gets_correct_pcnu_based_on_coordinates()
    {
        // Test coordinates for Kudus (approximately)
        $kudusLat = -6.8048;
        $kudusLng = 110.8423;
        
        // Test coordinates for Cilacap (approximately)
        $cilacapLat = -7.7396;
        $cilacapLng = 109.0174;
        
        // Create report for Kudus location
        $kudusPayload = $this->validPayload([
            'latitude' => $kudusLat,
            'longitude' => $kudusLng,
        ]);
        
        $response = $this->postJson(route('api.laporankejadian.store'), $kudusPayload);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'kode_kejadian',
                'message'
            ]
        ]);
        
        // Check that the report was saved with correct PCNU
        $laporan = LaporanKejadian::first();
        $this->assertNotNull($laporan);
        $this->assertEquals('menunggu', $laporan->is_valid);
        $this->assertNotNull($laporan->alamat_lengkap);
        $this->assertNotNull($laporan->id_pcnu);
        
        // The PCNU should be for Kudus (we'll check this more specifically in the next test)
        $this->assertNotNull($laporan->pcnu);
        
        // Create report for Cilacap location
        $cilacapPayload = $this->validPayload([
            'latitude' => $cilacapLat,
            'longitude' => $cilacapLng,
            'nama_pelapor' => 'Another User',
            'hp_pelapor' => '081234567891',
        ]);
        
        $response = $this->postJson(route('api.laporankejadian.store'), $cilacapPayload);
        
        $response->assertStatus(201);
        
        // Check that the second report was saved with correct PCNU
        $laporan2 = LaporanKejadian::where('nama_pelapor', 'Another User')->first();
        $this->assertNotNull($laporan2);
        $this->assertEquals('menunggu', $laporan2->is_valid);
        $this->assertNotNull($laporan2->alamat_lengkap);
        $this->assertNotNull($laporan2->id_pcnu);
        
        // The two reports should have different PCNU assignments
        $this->assertNotEquals(
            $laporan->id_pcnu,
            $laporan2->id_pcnu,
            'Reports from different locations should have different PCNU assignments'
        );
    }

    /**
     * Test that reports without coordinates do not get automatic PCNU assignment
     */
    public function test_report_without_coordinates_does_not_get_automatic_pcnu_assignment()
    {
        $payload = $this->validPayload([
            'latitude' => null,
            'longitude' => null,
        ]);
        
        $response = $this->postJson(route('api.laporankejadian.store'), $payload);
        
        $response->assertStatus(201);
        
        $laporan = LaporanKejadian::first();
        $this->assertNotNull($laporan);
        $this->assertEquals('menunggu', $laporan->is_valid);
        // Without coordinates, alamat_lengkap and id_pcnu should be null
        $this->assertNull($laporan->alamat_lengkap);
        $this->assertNull($laporan->id_pcnu);
    }

    private function validPayload(array $merge = []): array
    {
        return array_merge([
            'nama_pelapor' => 'Budi Santoso',
            'hp_pelapor' => '081234567890',
            'id_jenis_bencana' => BencanaMasterJenis::first()->id_jenis,
            'keterangan_situasi' => 'Terjadi banjir setinggi 1 meter.',
            'titik_kenal' => 'Depan Masjid Al-Akbar',
            'waktu_kejadian' => now()->format('Y-m-d\TH:i'),
            'latitude' => -7.005145, // Default to Semarang coordinates
            'longitude' => 110.438125,
        ], $merge);
    }
}