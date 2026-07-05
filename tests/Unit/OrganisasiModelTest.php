<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\OrganisasiUnit;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiMwc;
use App\Models\OrganisasiRanting;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrganisasiModelTest extends TestCase
{
    /**
     * Test konfigurasi model dan relasi OrganisasiUnit.
     */
    public function test_organisasi_unit_model_configuration_and_relations(): void
    {
        $model = new OrganisasiUnit();

        $this->assertEquals('organisasi_unit', $model->getTable());
        $this->assertEquals('id_unit', $model->getKeyName());
        $this->assertTrue($model->incrementing);
        $this->assertEquals('int', $model->getKeyType());
        $this->assertFalse($model->timestamps);

        $this->assertInstanceOf(BelongsTo::class, $model->parent());
        $this->assertEquals('parent_id', $model->parent()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $model->children());
        $this->assertEquals('parent_id', $model->children()->getForeignKeyName());

        $this->assertInstanceOf(HasOne::class, $model->pcnu());
        $this->assertEquals('id_unit', $model->pcnu()->getForeignKeyName());
    }

    /**
     * Test konfigurasi model dan relasi OrganisasiPcnu.
     */
    public function test_organisasi_pcnu_model_configuration_and_relations(): void
    {
        $model = new OrganisasiPcnu();

        $this->assertEquals('organisasi_pcnu', $model->getTable());
        $this->assertEquals('id_pcnu', $model->getKeyName());
        $this->assertTrue($model->incrementing);
        $this->assertEquals('int', $model->getKeyType());
        $this->assertFalse($model->timestamps);

        $this->assertInstanceOf(BelongsTo::class, $model->unit());
        $this->assertEquals('id_unit', $model->unit()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $model->mwc());
        $this->assertEquals('id_pcnu', $model->mwc()->getForeignKeyName());
    }

    /**
     * Test konfigurasi model dan relasi OrganisasiMwc.
     */
    public function test_organisasi_mwc_model_configuration_and_relations(): void
    {
        $model = new OrganisasiMwc();

        $this->assertEquals('organisasi_mwc', $model->getTable());
        $this->assertEquals('id_mwc', $model->getKeyName());
        $this->assertTrue($model->incrementing);
        $this->assertEquals('int', $model->getKeyType());
        $this->assertFalse($model->timestamps);

        $this->assertInstanceOf(BelongsTo::class, $model->pcnu());
        $this->assertEquals('id_pcnu', $model->pcnu()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $model->unit());
        $this->assertEquals('id_unit', $model->unit()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $model->ranting());
        $this->assertEquals('id_mwc', $model->ranting()->getForeignKeyName());
    }

    /**
     * Test konfigurasi model dan relasi OrganisasiRanting.
     */
    public function test_organisasi_ranting_model_configuration_and_relations(): void
    {
        $model = new OrganisasiRanting();

        $this->assertEquals('organisasi_ranting', $model->getTable());
        $this->assertEquals('id_ranting', $model->getKeyName());
        $this->assertTrue($model->incrementing);
        $this->assertEquals('int', $model->getKeyType());
        $this->assertFalse($model->timestamps);

        $this->assertInstanceOf(BelongsTo::class, $model->mwc());
        $this->assertEquals('id_mwc', $model->mwc()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $model->unit());
        $this->assertEquals('id_unit', $model->unit()->getForeignKeyName());
    }
}
