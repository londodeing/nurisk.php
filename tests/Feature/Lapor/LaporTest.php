<?php

namespace Tests\Feature\Lapor;

use Tests\TestCase;
use App\Models\BencanaMasterJenis;
use App\Models\LaporanKejadian;
use Database\Seeders\BencanaMasterJenisSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LaporTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BencanaMasterJenisSeeder::class);
    }

    public function test_halaman_lapor_tersedia()
    {
        $response = $this->get(route('public.lapor'));
        $response->assertStatus(200);
        $response->assertSee('Laporkan Kejadian');
    }

    public function test_halaman_lapor_menampilkan_daftar_jenis_bencana()
    {
        $response = $this->get(route('public.lapor'));
        $response->assertStatus(200);
        $response->assertSee('Banjir');
        $response->assertSee('Tanah Longsor');
        $response->assertSee('Gempa Bumi');
    }

    private function validPayload(array $merge = []): array
    {
        return array_merge([
            'nama'              => 'Budi Santoso',
            'no_hp'             => '081234567890',
            'id_jenis_bencana'  => BencanaMasterJenis::first()->id_jenis,
            'lokasi'            => 'Depan Masjid Al-Akbar, Semarang',
            'deskripsi'         => 'Terjadi banjir setinggi 1 meter.',
            'latitude'          => -7.005145,
            'longitude'         => 110.438125,
            'waktu_kejadian'    => now()->format('Y-m-d\TH:i'),
        ], $merge);
    }

    public function test_laporan_publik_tersimpan_ke_database()
    {
        $payload = $this->validPayload();
        $response = $this->post(route('public.lapor.store'), $payload);

        $response->assertRedirect(route('public.lapor'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('laporan_kejadian', [
            'nama_pelapor'       => 'Budi Santoso',
            'hp_pelapor'         => '081234567890',
            'id_jenis_bencana'   => $payload['id_jenis_bencana'],
            'keterangan_situasi' => 'Terjadi banjir setinggi 1 meter.',
            'latitude'           => -7.005145,
            'longitude'          => 110.438125,
        ]);
    }

    public function test_titik_kenal_menggabungkan_geocoding_dan_input_user()
    {
        $this->app->bind(
            \App\Services\Location\ReverseGeocodingService::class,
            function () {
                $mock = $this->createMock(\App\Services\Location\ReverseGeocodingService::class);
                $mock->method('getAddress')->willReturn('Jl. Merdeka No.10, Semarang');
                return $mock;
            }
        );

        $payload = $this->validPayload(['lokasi' => 'Depan Masjid Al-Akbar']);
        $this->post(route('public.lapor.store'), $payload);

        $this->assertDatabaseHas('laporan_kejadian', [
            'nama_pelapor' => 'Budi Santoso',
        ]);

        $laporan = LaporanKejadian::first();
        $this->assertStringContainsString('Jl. Merdeka No.10, Semarang', $laporan->titik_kenal);
        $this->assertStringContainsString('Depan Masjid Al-Akbar', $laporan->titik_kenal);
    }

    public function test_titik_kenal_tanpa_geocoding_tetap_pakai_input_user()
    {
        $this->app->bind(
            \App\Services\Location\ReverseGeocodingService::class,
            function () {
                $mock = $this->createMock(\App\Services\Location\ReverseGeocodingService::class);
                $mock->method('getAddress')->willReturn(null);
                return $mock;
            }
        );

        $payload = $this->validPayload(['lokasi' => 'Depan Masjid Al-Akbar']);
        $this->post(route('public.lapor.store'), $payload);

        $laporan = LaporanKejadian::first();
        $this->assertEquals('Depan Masjid Al-Akbar', $laporan->titik_kenal);
    }

    public function test_kode_kejadian_dihasilkan_otomatis()
    {
        $this->post(route('public.lapor.store'), $this->validPayload());

        $laporan = LaporanKejadian::first();
        $this->assertNotNull($laporan->kode_kejadian);
        $this->assertStringStartsWith('LAP-', $laporan->kode_kejadian);
    }

    public function test_laporan_gagal_tanpa_gps()
    {
        $response = $this->post(route('public.lapor.store'), $this->validPayload([
            'latitude' => null,
            'longitude' => null,
        ]));

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_laporan_gagal_tanpa_field_wajib()
    {
        $response = $this->post(route('public.lapor.store'), []);

        $response->assertSessionHasErrors([
            'nama', 'no_hp', 'id_jenis_bencana', 'lokasi', 'deskripsi', 'waktu_kejadian',
        ]);
    }

    public function test_laporan_gagal_dengan_koordinat_diluar_jawa_tengah()
    {
        $response = $this->post(route('public.lapor.store'), $this->validPayload([
            'latitude'  => 40.712776,
            'longitude' => -74.005974,
        ]));

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_kode_kejadian_unik_setiap_laporan()
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('public.lapor.store'), $this->validPayload([
                'nama'      => "User{$i}",
                'no_hp'     => "08123456789{$i}",
                'latitude'  => -7.0 + ($i * 0.01),
                'longitude' => 110.4 + ($i * 0.01),
            ]));
        }

        $codes = LaporanKejadian::pluck('kode_kejadian')->toArray();
        $this->assertCount(3, array_unique($codes));
    }

    public function test_laporan_default_is_valid_menunggu()
    {
        $this->post(route('public.lapor.store'), $this->validPayload());

        $this->assertDatabaseHas('laporan_kejadian', [
            'nama_pelapor' => 'Budi Santoso',
            'is_valid'     => 'menunggu',
        ]);
    }

    public function test_id_jenis_bencana_wajib_valid()
    {
        $response = $this->post(route('public.lapor.store'), $this->validPayload([
            'id_jenis_bencana' => 9999,
        ]));

        $response->assertSessionHasErrors('id_jenis_bencana');
    }

    public function test_nama_pelapor_maks_150_karakter()
    {
        $response = $this->post(route('public.lapor.store'), $this->validPayload([
            'nama' => str_repeat('A', 151),
        ]));

        $response->assertSessionHasErrors('nama');
    }

    public function test_no_hp_minimal_8_karakter()
    {
        $response = $this->post(route('public.lapor.store'), $this->validPayload([
            'no_hp' => '081',
        ]));

        $response->assertSessionHasErrors('no_hp');
    }

    public function test_no_hp_tidak_boleh_alfabet()
    {
        $response = $this->post(route('public.lapor.store'), $this->validPayload([
            'no_hp' => 'abcdefghijk',
        ]));

        $response->assertSessionHasErrors('no_hp');
    }

    public function test_waktu_kejadian_tidak_boleh_masa_depan()
    {
        $response = $this->post(route('public.lapor.store'), $this->validPayload([
            'waktu_kejadian' => now()->addDay()->format('Y-m-d\TH:i'),
        ]));

        $response->assertSessionHasErrors('waktu_kejadian');
    }

    public function test_waktu_kejadian_tersimpan_benar()
    {
        $waktu = now()->subHours(3)->format('Y-m-d\TH:i');

        $this->post(route('public.lapor.store'), $this->validPayload([
            'waktu_kejadian' => $waktu,
        ]));

        $laporan = LaporanKejadian::first();
        $this->assertEquals($waktu, $laporan->waktu_kejadian->format('Y-m-d\TH:i'));
    }
}
