<?php

namespace Tests\Feature\Api\Logistik;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\LogistikKategori;
use App\Models\LogistikBarangKatalog;
use App\Models\LogistikStok;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LogistikMutasiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mutasi_keluar_berhasil()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran]);
        $kategori = LogistikKategori::create(['nama_kategori' => 'Mutasi Test']);
        $katalog = LogistikBarangKatalog::create(['id_kategori' => $kategori->id_kategori, 'id_satuan' => 1, 'nama_barang_standar' => 'Barang Mutasi 1']);

        $stokId = LogistikStok::insertGetId([
            'id_katalog' => $katalog->id_katalog,
            'id_posaju' => 1,
            'id_gudang' => null,
            'jumlah_tersedia' => 50,
        ]);

        $response = $this->actingAs($user)->postJson('/api/logistik/mutasi', [
            'id_stok' => $stokId,
            'tipe_mutasi' => 'keluar',
            'jumlah' => 10,
            'asal_tujuan' => 'Distribusi ke Posko B',
            'keterangan' => 'Test'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('logistik_stok', [
            'id_stok' => $stokId,
            'jumlah_tersedia' => 40
        ]);
        $this->assertDatabaseHas('logistik_mutasi', [
            'id_stok' => $stokId,
            'tipe_mutasi' => 'keluar',
            'jumlah' => 10
        ]);
    }

    public function test_mutasi_keluar_kurang_stok_ditolak()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran]);
        $kategori = LogistikKategori::create(['nama_kategori' => 'Mutasi Test 2']);
        $katalog = LogistikBarangKatalog::create(['id_kategori' => $kategori->id_kategori, 'id_satuan' => 1, 'nama_barang_standar' => 'Barang Mutasi 2']);

        $stokId = LogistikStok::insertGetId([
            'id_katalog' => $katalog->id_katalog,
            'id_posaju' => 1,
            'id_gudang' => null,
            'jumlah_tersedia' => 5,
        ]);

        $response = $this->actingAs($user)->postJson('/api/logistik/mutasi', [
            'id_stok' => $stokId,
            'tipe_mutasi' => 'keluar',
            'jumlah' => 10,
            'asal_tujuan' => 'Distribusi ke Posko B',
            'keterangan' => 'Test'
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('logistik_stok', [
            'id_stok' => $stokId,
            'jumlah_tersedia' => 5
        ]);
    }
}
