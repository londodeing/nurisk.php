<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\PenggunaJabatan;
use App\Models\JabatanPosisi;
use App\Models\AuthUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class PenggunaJabatanModelTest extends TestCase
{
    use DatabaseTransactions;



    public function test_model_maps_to_pengguna_jabatan_table_correctly(): void
    {
        $model = new PenggunaJabatan();
        $this->assertEquals('pengguna_jabatan', $model->getTable());
    }

    public function test_primary_key_is_id_pengguna_jabatan(): void
    {
        $model = new PenggunaJabatan();
        $this->assertEquals('id_pengguna_jabatan', $model->getKeyName());
    }

    public function test_status_aktif_is_cast_to_boolean(): void
    {
        $model = new PenggunaJabatan();
        $this->assertEquals('boolean', $model->getCasts()['status_aktif']);
    }

    public function test_berakhir_pada_is_cast_to_datetime(): void
    {
        $model = new PenggunaJabatan();
        $this->assertEquals('datetime', $model->getCasts()['berakhir_pada']);
    }

    public function test_pengguna_relation_returns_belongs_to_auth_user(): void
    {
        $model = new PenggunaJabatan();
        $relation = $model->pengguna();
        
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('id_pengguna', $relation->getForeignKeyName());
    }

    public function test_jabatan_relation_returns_belongs_to_jabatan_posisi(): void
    {
        $model = new PenggunaJabatan();
        $relation = $model->jabatan();
        
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('id_jabatan_posisi', $relation->getForeignKeyName());
    }

    public function test_scope_aktif_only_returns_active_jobs(): void
    {
        $activeJob = PenggunaJabatan::factory()->create([
            'status_aktif' => true,
            'berakhir_pada' => null,
        ]);

        $futureJob = PenggunaJabatan::factory()->create([
            'status_aktif' => true,
            'berakhir_pada' => now()->addDays(10),
        ]);

        $expiredJob = PenggunaJabatan::factory()->create([
            'status_aktif' => false,
            'berakhir_pada' => now()->subDays(5),
        ]);

        $results = PenggunaJabatan::aktif()->get();

        $this->assertTrue($results->contains($activeJob));
        $this->assertTrue($results->contains($futureJob));
        $this->assertFalse($results->contains($expiredJob));
    }

    public function test_scope_by_lingkup_filters_correctly(): void
    {
        $job1 = PenggunaJabatan::factory()->create([
            'tipe_lingkup' => 'pcnu',
            'id_lingkup' => 10,
        ]);

        $job2 = PenggunaJabatan::factory()->create([
            'tipe_lingkup' => 'mwc',
            'id_lingkup' => 20,
        ]);

        $results = PenggunaJabatan::byLingkup('pcnu', 10)->get();

        $this->assertTrue($results->contains($job1));
        $this->assertFalse($results->contains($job2));
    }

    public function test_sudah_berakhir_factory_state_works(): void
    {
        $expiredJob = PenggunaJabatan::factory()->sudahBerakhir()->create();

        $this->assertFalse($expiredJob->status_aktif);
        $this->assertNotNull($expiredJob->berakhir_pada);
        $this->assertTrue($expiredJob->berakhir_pada->isPast());
    }
}
