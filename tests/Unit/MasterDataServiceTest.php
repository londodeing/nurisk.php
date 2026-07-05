<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use App\Models\OrganisasiUnit;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiMwc;
use App\Models\OrganisasiRanting;
use App\Services\MasterData\MasterDataService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MasterDataServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected MasterDataService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new MasterDataService();

        \Illuminate\Support\Facades\DB::table('organisasi_unit')->insertOrIgnore([
            ['id_unit' => 1, 'nama_unit' => 'Unit 1', 'tipe_unit' => 'ranting'],
            ['id_unit' => 2, 'nama_unit' => 'Unit 2', 'tipe_unit' => 'pcnu'],
            ['id_unit' => 3, 'nama_unit' => 'Unit 3', 'tipe_unit' => 'pcnu'],
            ['id_unit' => 4, 'nama_unit' => 'Unit 4', 'tipe_unit' => 'mwc'],
        ]);

        // Bangun skema tabel wilayah di SQLite in-memory untuk testing






        // Bangun skema tabel organisasi







    }

    /**
     * Test 1: Kabupaten list berhasil dimuat.
     */
    public function test_get_kabupaten_list_returns_collection(): void
    {
        WilayahKabupaten::factory()->create(['id_kab' => '9901', 'nama_kab' => 'Cilacap']);
        WilayahKabupaten::factory()->create(['id_kab' => '9902', 'nama_kab' => 'Banyumas']);

        $list = $this->service->getKabupatenList();

        $this->assertGreaterThanOrEqual(2, $list->count());
        $this->assertTrue($list->contains('nama_kab', 'Banyumas'));
    }

    /**
     * Test 2: Kecamatan berdasarkan kabupaten.
     */
    public function test_get_kecamatan_by_kabupaten_returns_correct_data(): void
    {
        WilayahKabupaten::factory()->create(['id_kab' => '9901']);
        WilayahKabupaten::factory()->create(['id_kab' => '9902']);
        WilayahKecamatan::factory()->create(['id_kec' => '990101', 'id_kab' => '9901', 'nama_kec' => 'Kedungreja']);
        WilayahKecamatan::factory()->create(['id_kec' => '990201', 'id_kab' => '9902']); // Luar kabupaten

        $list = $this->service->getKecamatanByKabupaten('9901');

        $this->assertCount(1, $list);
        $this->assertEquals('990101', $list->first()->id_kec);
    }

    /**
     * Test 3: Desa berdasarkan kecamatan.
     */
    public function test_get_desa_by_kecamatan_returns_correct_data(): void
    {
        WilayahKabupaten::factory()->create(['id_kab' => '9901']);
        $kec = WilayahKecamatan::factory()->create(['id_kec' => '990101', 'id_kab' => '9901']);
        WilayahDesa::factory()->create(['id_desa' => '9901012001', 'id_kec' => '990101', 'nama_desa' => 'Tambakreja']);

        $list = $this->service->getDesaByKecamatan('990101');

        $this->assertCount(1, $list);
        $this->assertEquals('9901012001', $list->first()->id_desa);
    }

    /**
     * Test 4: PCNU list berhasil dimuat.
     */
    public function test_get_pcnu_list_returns_collection(): void
    {
        OrganisasiPcnu::factory()->create(['id_pcnu' => 1, 'nama_pcnu' => 'PCNU Cilacap', 'id_unit' => 2]);
        OrganisasiPcnu::factory()->create(['id_pcnu' => 2, 'nama_pcnu' => 'PCNU Banyumas', 'id_unit' => 3]);

        $list = $this->service->getPcnuList();

        $this->assertGreaterThanOrEqual(2, $list->count());
        $this->assertEquals('PCNU Banyumas', $list->firstWhere('nama_pcnu', 'PCNU Banyumas')->nama_pcnu ?? null);
    }

    /**
     * Test 5: MWC berdasarkan PCNU.
     */
    public function test_get_mwc_by_pcnu_returns_correct_data(): void
    {
        OrganisasiPcnu::factory()->create(['id_pcnu' => 1, 'id_unit' => 2, 'nama_pcnu' => 'PCNU 1']);
        OrganisasiPcnu::factory()->create(['id_pcnu' => 2, 'id_unit' => 3, 'nama_pcnu' => 'PCNU 2']);

        OrganisasiMwc::factory()->create(['id_mwc' => 1, 'id_pcnu' => 1, 'nama_mwc' => 'MWC Kedungreja']);
        OrganisasiMwc::factory()->create(['id_mwc' => 2, 'id_pcnu' => 2, 'nama_mwc' => 'MWC Luar']);

        $list = $this->service->getMwcByPcnu(1);

        $this->assertCount(1, $list);
        $this->assertEquals('MWC Kedungreja', $list->first()->nama_mwc);
    }

    /**
     * Test 6: Ranting berdasarkan MWC.
     */
    public function test_get_ranting_by_mwc_returns_correct_data(): void
    {
        OrganisasiPcnu::factory()->create(['id_pcnu' => 1, 'id_unit' => 2, 'nama_pcnu' => 'PCNU 1']);
        OrganisasiMwc::factory()->create(['id_mwc' => 1, 'id_pcnu' => 1, 'id_unit' => 4, 'nama_mwc' => 'MWC 1']);

        OrganisasiRanting::factory()->create(['id_ranting' => 1, 'id_mwc' => 1, 'nama_ranting' => 'Ranting Tambakreja']);

        $list = $this->service->getRantingByMwc(1);

        $this->assertCount(1, $list);
        $this->assertEquals('Ranting Tambakreja', $list->first()->nama_ranting);
    }

    /**
     * Test 7: Find method mengembalikan object yang benar.
     */
    public function test_find_methods_return_correct_model_instances(): void
    {
        WilayahKabupaten::factory()->create(['id_kab' => '9901']);
        OrganisasiPcnu::factory()->create(['id_pcnu' => 1, 'id_unit' => 2, 'nama_pcnu' => 'PCNU Cilacap']);

        $kab = $this->service->findKabupaten('9901');
        $pcnu = $this->service->findPcnu(1);

        $this->assertInstanceOf(WilayahKabupaten::class, $kab);
        $this->assertInstanceOf(OrganisasiPcnu::class, $pcnu);
    }

    /**
     * Test 8: Find method mengembalikan null jika id tidak terdaftar.
     */
    public function test_find_methods_return_null_on_missing_id(): void
    {
        $this->assertNull($this->service->findKabupaten('9999'));
        $this->assertNull($this->service->findPcnu(9999));
    }

    /**
     * Test 9 & 10: Cache internal level request bekerja dan tidak memicu query duplikat.
     */
    public function test_request_level_cache_avoids_duplicate_queries(): void
    {
        WilayahKabupaten::factory()->create(['id_kab' => '9901', 'nama_kab' => 'Cilacap']);

        // Kueri pertama memicu load ke DB
        $kab1 = $this->service->findKabupaten('9901');

        // Modifikasi data di DB secara diam-diam (bypass Eloquent)
        DB::table('wilayah_kabupaten')->where('id_kab', '9901')->update(['nama_kab' => 'Banyumas']);

        // Kueri kedua harus mengembalikan value dari Cache (Cilacap), bukan DB (Banyumas)
        $kab2 = $this->service->findKabupaten('9901');

        $this->assertEquals('Cilacap', $kab2->nama_kab);
    }
}
