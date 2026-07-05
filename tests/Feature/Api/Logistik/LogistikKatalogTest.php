<?php

namespace Tests\Feature\Api\Logistik;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\LogistikKategori;
use App\Models\LogistikBarangKatalog;
use App\Models\LogistikStok;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LogistikKatalogTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_bisa_buat_katalog()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran]);
        $kategori = LogistikKategori::create(['nama_kategori' => 'Pangan']);
        
        $response = $this->actingAs($user)->postJson('/api/logistik/katalog', [
            'id_kategori' => $kategori->id_kategori,
            'id_satuan' => 1,
            'nama_barang_standar' => 'Sarden Kaleng Uji'
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('logistik_barang_katalog', ['nama_barang_standar' => 'Sarden Kaleng Uji']);
    }

    public function test_delete_katalog_digunakan_di_stok_ditolak()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran]);
        $kategori = LogistikKategori::create(['nama_kategori' => 'Pangan']);
        $katalog = LogistikBarangKatalog::create([
            'id_kategori' => $kategori->id_kategori,
            'id_satuan' => 1,
            'nama_barang_standar' => 'Mie Instan Uji'
        ]);

        LogistikStok::insert([
            'id_katalog' => $katalog->id_katalog,
            'id_posaju' => 1,
            'id_gudang' => null,
            'jumlah_tersedia' => 10,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/logistik/katalog/{$katalog->id_katalog}");
        $response->assertStatus(422);
    }
}
