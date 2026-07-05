<?php

namespace Tests\Unit\Operasi;

use Tests\TestCase;
use Tests\Support\CreatesOperasiSchema;
use App\Models\OperasiPosaju;
use App\Models\OperasiKlaster;
use App\Models\OperasiPenugasan;
use App\Models\OperasiTugas;
use App\Models\OperasiSuratKeluar;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OperasiModelsTest extends TestCase
{
    use DatabaseTransactions, CreatesOperasiSchema;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat skema SQLite Memory untuk tabel-tabel operasi
        $this->createOperasiSchema();
    }

    public function test_instantiate_operasi_posaju()
    {
        $posaju = new OperasiPosaju([
            'id_insiden' => 1,
            'status_alur' => 'aktif',
        ]);

        $this->assertEquals(1, $posaju->id_insiden);
        $this->assertEquals('aktif', $posaju->status_alur);

        // Test relasi tidak melempar error
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $posaju->insiden());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $posaju->pj());
    }

    public function test_instantiate_operasi_klaster()
    {
        $klaster = new OperasiKlaster([
            'id_insiden' => 1,
            'id_master_klaster' => 5,
            'status_klaster' => 'aktif',
        ]);

        $this->assertEquals(5, $klaster->id_master_klaster);

        // Test relasi
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $klaster->insiden());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $klaster->tugas());
    }

    public function test_instantiate_operasi_penugasan()
    {
        $penugasan = new OperasiPenugasan([
            'id_insiden' => 1,
            'id_pengguna' => 2,
            'peran_otoritas' => 'trc',
        ]);

        $this->assertEquals('trc', $penugasan->peran_otoritas);
        
        // Test relasi
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $penugasan->pengguna());
    }

    public function test_instantiate_operasi_tugas()
    {
        $tugas = new OperasiTugas([
            'id_operasi_klaster' => 1,
            'judul_tugas' => 'Distribusi Logistik',
        ]);

        $this->assertEquals('Distribusi Logistik', $tugas->judul_tugas);
        
        // Test relasi
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $tugas->klaster());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $tugas->posaju());
    }

    public function test_instantiate_operasi_surat_keluar()
    {
        $surat = new OperasiSuratKeluar([
            'id_insiden' => 1,
            'id_jenis_surat' => 1,
            'nomor_surat_resmi' => '123/NURISK/2026',
        ]);

        $this->assertEquals('123/NURISK/2026', $surat->nomor_surat_resmi);
        
        // Test relasi
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $surat->insiden());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $surat->penandatangan());
    }
}
