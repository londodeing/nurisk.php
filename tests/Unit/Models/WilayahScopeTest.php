<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\WilayahScope;
use App\Models\OrganisasiUnit;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiMwc;
use App\Models\OrganisasiRanting;
use InvalidArgumentException;

class WilayahScopeTest extends TestCase
{
    public function test_wilayah_scope_is_valid_works(): void
    {
        $this->assertTrue(WilayahScope::isValid('pwnu'));
        $this->assertTrue(WilayahScope::isValid('pcnu'));
        $this->assertTrue(WilayahScope::isValid('mwc'));
        $this->assertTrue(WilayahScope::isValid('ranting'));
        $this->assertTrue(WilayahScope::isValid('lembaga'));
        $this->assertTrue(WilayahScope::isValid('banom'));
        $this->assertFalse(WilayahScope::isValid('unknown'));
    }

    public function test_wilayah_scope_is_hierarchical_works(): void
    {
        $this->assertTrue(WilayahScope::isHierarchical('pwnu'));
        $this->assertTrue(WilayahScope::isHierarchical('pcnu'));
        $this->assertTrue(WilayahScope::isHierarchical('mwc'));
        $this->assertTrue(WilayahScope::isHierarchical('ranting'));
        $this->assertFalse(WilayahScope::isHierarchical('lembaga'));
        $this->assertFalse(WilayahScope::isHierarchical('banom'));
    }

    public function test_wilayah_scope_model_class_mapping_works(): void
    {
        $this->assertEquals(OrganisasiUnit::class, WilayahScope::modelClass('pwnu'));
        $this->assertEquals(OrganisasiPcnu::class, WilayahScope::modelClass('pcnu'));
        $this->assertEquals(OrganisasiMwc::class, WilayahScope::modelClass('mwc'));
        $this->assertEquals(OrganisasiRanting::class, WilayahScope::modelClass('ranting'));
    }

    public function test_wilayah_scope_model_class_throws_exception_on_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        WilayahScope::modelClass('lembaga');
    }

    public function test_wilayah_scope_primary_key_mapping_works(): void
    {
        $this->assertEquals('id_unit', WilayahScope::primaryKey('pwnu'));
        $this->assertEquals('id_pcnu', WilayahScope::primaryKey('pcnu'));
        $this->assertEquals('id_mwc', WilayahScope::primaryKey('mwc'));
        $this->assertEquals('id_ranting', WilayahScope::primaryKey('ranting'));
    }

    public function test_wilayah_scope_primary_key_throws_exception_on_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        WilayahScope::primaryKey('banom');
    }

    public function test_wilayah_scope_label_returns_correct_string(): void
    {
        $this->assertEquals('PWNU Jawa Tengah', WilayahScope::label('pwnu'));
        $this->assertEquals('PCNU (Cabang)', WilayahScope::label('pcnu'));
        $this->assertEquals('MWC (Majelis Wakil Cabang)', WilayahScope::label('mwc'));
        $this->assertEquals('Ranting', WilayahScope::label('ranting'));
        $this->assertEquals('Lembaga', WilayahScope::label('lembaga'));
        $this->assertEquals('Banom', WilayahScope::label('banom'));
    }

    public function test_wilayah_scope_constants_count(): void
    {
        $this->assertCount(6, WilayahScope::ALL_TYPES);
        $this->assertCount(4, WilayahScope::HIERARCHY);
    }
}
