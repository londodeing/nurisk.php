<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WilayahModelTest extends TestCase
{
    /**
     * Test konfigurasi model dan relasi WilayahKabupaten.
     */
    public function test_wilayah_kabupaten_model_configuration_and_relations(): void
    {
        $model = new WilayahKabupaten();

        $this->assertEquals('wilayah_kabupaten', $model->getTable());
        $this->assertEquals('id_kab', $model->getKeyName());
        $this->assertFalse($model->incrementing);
        $this->assertEquals('string', $model->getKeyType());
        $this->assertFalse($model->timestamps);

        $this->assertInstanceOf(HasMany::class, $model->kecamatan());
        $this->assertEquals('id_kab', $model->kecamatan()->getForeignKeyName());
    }

    /**
     * Test konfigurasi model dan relasi WilayahKecamatan.
     */
    public function test_wilayah_kecamatan_model_configuration_and_relations(): void
    {
        $model = new WilayahKecamatan();

        $this->assertEquals('wilayah_kecamatan', $model->getTable());
        $this->assertEquals('id_kec', $model->getKeyName());
        $this->assertFalse($model->incrementing);
        $this->assertEquals('string', $model->getKeyType());
        $this->assertFalse($model->timestamps);

        $this->assertInstanceOf(BelongsTo::class, $model->kabupaten());
        $this->assertEquals('id_kab', $model->kabupaten()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $model->desa());
        $this->assertEquals('id_kec', $model->desa()->getForeignKeyName());
    }

    /**
     * Test konfigurasi model dan relasi WilayahDesa.
     */
    public function test_wilayah_desa_model_configuration_and_relations(): void
    {
        $model = new WilayahDesa();

        $this->assertEquals('wilayah_desa', $model->getTable());
        $this->assertEquals('id_desa', $model->getKeyName());
        $this->assertFalse($model->incrementing);
        $this->assertEquals('string', $model->getKeyType());
        $this->assertFalse($model->timestamps);

        $this->assertInstanceOf(BelongsTo::class, $model->kecamatan());
        $this->assertEquals('id_kec', $model->kecamatan()->getForeignKeyName());
    }
}
