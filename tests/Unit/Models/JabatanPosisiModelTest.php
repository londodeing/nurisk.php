<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\JabatanPosisi;
use App\Models\PenggunaJabatan;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class JabatanPosisiModelTest extends TestCase
{
    use DatabaseTransactions;



    public function test_model_maps_to_master_jabatan_table_correctly(): void
    {
        $model = new JabatanPosisi();
        $this->assertEquals('master_jabatan', $model->getTable());
    }

    public function test_primary_key_is_id_jabatan_posisi(): void
    {
        $model = new JabatanPosisi();
        $this->assertEquals('id_jabatan_posisi', $model->getKeyName());
    }

    public function test_timestamps_use_dibuat_pada_and_diperbarui_pada(): void
    {
        $this->assertEquals('dibuat_pada', JabatanPosisi::CREATED_AT);
        $this->assertEquals('diperbarui_pada', JabatanPosisi::UPDATED_AT);
    }

    public function test_fillable_attributes_are_correct(): void
    {
        $model = new JabatanPosisi();
        $this->assertEquals(['nama_jabatan', 'slug', 'deskripsi'], $model->getFillable());
    }

    public function test_pengguna_jabatan_relation_returns_has_many(): void
    {
        $model = new JabatanPosisi();
        $relation = $model->penggunaJabatan();
        
        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('id_jabatan_posisi', $relation->getForeignKeyName());
    }

    public function test_jabatan_posisi_can_be_created_via_factory(): void
    {
        $jabatan = JabatanPosisi::factory()->create([
            'nama_jabatan' => 'Ketua PWNU Test',
            'slug' => 'ketua-pwnu-test',
        ]);

        $this->assertDatabaseHas('master_jabatan', [
            'id_jabatan_posisi' => $jabatan->id_jabatan_posisi,
            'nama_jabatan' => 'Ketua PWNU Test',
            'slug' => 'ketua-pwnu-test',
        ]);
    }

    public function test_slug_must_be_unique(): void
    {
        JabatanPosisi::factory()->create(['slug' => 'duplikat-slug']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        JabatanPosisi::factory()->create(['slug' => 'duplikat-slug']);
    }
}
