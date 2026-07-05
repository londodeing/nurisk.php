<?php

namespace Tests\Feature\Api\Logistik;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\LogistikKategori;
use App\Models\LogistikBarangKatalog;
use App\Models\LogistikStok;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LogistikStokTest extends TestCase
{
    use DatabaseTransactions;

    public function test_baca_stok_sukses()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran]);
        $kategori = LogistikKategori::create(['nama_kategori' => 'Stok Test']);
        $katalog = LogistikBarangKatalog::create(['id_kategori' => $kategori->id_kategori, 'id_satuan' => 1, 'nama_barang_standar' => 'Barang Stok']);

        $stokId = LogistikStok::insertGetId([
            'id_katalog' => $katalog->id_katalog,
            'id_posaju' => 1,
            'id_gudang' => null,
            'jumlah_tersedia' => 100,
        ]);

        $response = $this->actingAs($user)->getJson("/api/logistik/stok/{$stokId}");
        $response->assertStatus(200);
        $this->assertEquals(100, $response->json('data.jumlah_tersedia'));
    }

    public function test_koreksi_stok_berhasil()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran]);
        $kategori = LogistikKategori::create(['nama_kategori' => 'Stok Test 2']);
        $katalog = LogistikBarangKatalog::create(['id_kategori' => $kategori->id_kategori, 'id_satuan' => 1, 'nama_barang_standar' => 'Barang Stok 2']);

        $stokId = LogistikStok::insertGetId([
            'id_katalog' => $katalog->id_katalog,
            'id_posaju' => 1,
            'id_gudang' => null,
            'jumlah_tersedia' => 100,
        ]);

        // Koreksi dari 100 menjadi 80 (stok opname kurang 20)
        $response = $this->actingAs($user)->postJson("/api/logistik/stok/{$stokId}/koreksi", [
            'jumlah_tersedia' => 80,
            'keterangan' => 'Bocor digigit tikus'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('logistik_stok', [
            'id_stok' => $stokId,
            'jumlah_tersedia' => 80
        ]);
        $this->assertDatabaseHas('logistik_mutasi', [
            'id_stok' => $stokId,
            'tipe_mutasi' => 'penyesuaian',
            'jumlah' => -20, // 80 - 100
        ]);
    }
}
