<?php

namespace Tests\Feature\Api\Logistik;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\LogistikKategori;
use App\Models\LogistikBarangKatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LogistikKategoriTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_bisa_buat_kategori()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran]);
        $response = $this->actingAs($user)->postJson('/api/logistik/kategori', [
            'nama_kategori' => 'Kategori Baru'
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('logistik_kategori', ['nama_kategori' => 'Kategori Baru']);
    }

    public function test_relawan_tidak_bisa_buat_kategori()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'relawan']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran]);
        $response = $this->actingAs($user)->postJson('/api/logistik/kategori', [
            'nama_kategori' => 'Kategori Relawan'
        ]);
        $response->assertStatus(403);
    }

    public function test_get_kategori_berhasil()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran]);
        LogistikKategori::create(['nama_kategori' => 'Beras']);
        $response = $this->actingAs($user)->getJson('/api/logistik/kategori');
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_delete_kategori_digunakan_ditolak()
    {
        $role = \App\Models\AuthRole::factory()->create(['nama_peran' => 'super_admin']);
        $user = AuthUser::factory()->create(['id_peran' => $role->id_peran]);
        $kategori = LogistikKategori::create(['nama_kategori' => 'Kategori Uji']);
        LogistikBarangKatalog::create([
            'id_kategori' => $kategori->id_kategori,
            'id_satuan' => 1,
            'nama_barang_standar' => 'Barang Uji'
        ]);
        
        $response = $this->actingAs($user)->deleteJson("/api/logistik/kategori/{$kategori->id_kategori}");
        $response->assertStatus(422);
    }
}
