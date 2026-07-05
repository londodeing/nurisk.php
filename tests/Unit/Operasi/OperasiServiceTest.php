<?php

namespace Tests\Unit\Operasi;

use Tests\TestCase;
use Tests\Support\CreatesOperasiSchema;
use App\Models\OperasiPosaju;
use App\Models\OperasiKlaster;
use App\Models\OperasiTugas;
use App\Services\Operasi\OperasiPosajuService;
use App\Services\Operasi\OperasiKlasterService;
use App\Services\Operasi\OperasiTugasService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OperasiServiceTest extends TestCase
{
    use DatabaseTransactions, CreatesOperasiSchema;

    protected $posajuService;
    protected $klasterService;
    protected $tugasService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createOperasiSchema();
        $this->seed(\Database\Seeders\MasterKlasterSeeder::class);
        
        // Create required master data to satisfy foreign keys
        \Illuminate\Support\Facades\DB::table('auth_roles')->insertOrIgnore([
            'id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1
        ]);
        \Illuminate\Support\Facades\DB::table('auth_users')->insertOrIgnore([
            'id_pengguna' => 1, 'id_peran' => 1, 'no_hp' => '081234', 'kata_sandi' => 'xxx'
        ]);
        \Illuminate\Support\Facades\DB::table('organisasi_unit')->insertOrIgnore([
            'id_unit' => 1, 'nama_unit' => 'Unit PCNU', 'tipe_unit' => 'pcnu'
        ]);
        \Illuminate\Support\Facades\DB::table('organisasi_pcnu')->insertOrIgnore([
            'id_pcnu' => 1, 'id_unit' => 1, 'nama_pcnu' => 'PCNU'
        ]);
        \Illuminate\Support\Facades\DB::table('bencana_master_jenis')->insertOrIgnore([
            'id_jenis' => 1, 'nama_bencana' => 'Gempa', 'slug' => 'gempa', 'deskripsi' => 'Gempa Bumi'
        ]);
        \Illuminate\Support\Facades\DB::table('operasi_insiden')->insertOrIgnore([
            'id_insiden' => 1, 'uuid_insiden' => '00000000-0000-0000-0000-000000000001', 'kode_kejadian' => 'INS-1', 'id_jenis_bencana' => 1, 'id_pcnu' => 1, 'prioritas' => 'tinggi', 'waktu_mulai' => now()
        ]);
        
        $this->posajuService = new OperasiPosajuService();
        $this->klasterService = new OperasiKlasterService();
        $this->tugasService = new OperasiTugasService();
    }

    public function test_posaju_state_machine_happy_path()
    {
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => 1,
            'pj_posaju' => 1,
            'nama_posaju' => 'Pos Aju Utama',
            'status_alur' => 'direncanakan'
        ]);

        // direncanakan -> aktif
        $posaju = $this->posajuService->activate($posaju);
        $this->assertEquals('aktif', $posaju->status_alur);

        // aktif -> diperpanjang
        $posaju = $this->posajuService->extend($posaju, now()->addDays(7));
        $this->assertEquals('diperpanjang', $posaju->status_alur);

        // diperpanjang -> ditutup
        $posaju = $this->posajuService->close($posaju, 'Operasi selesai');
        $this->assertEquals('ditutup', $posaju->status_alur);
    }

    public function test_posaju_state_machine_invalid_transitions()
    {
        $posaju = OperasiPosaju::create(['alamat_lokasi' => 'Alamat Default', 'id_insiden' => 1,
            'pj_posaju' => 1,
            'nama_posaju' => 'Pos Aju Utama',
            'status_alur' => 'direncanakan'
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->posajuService->close($posaju); // direncanakan -> ditutup (HARUS GAGAL)
    }

    public function test_klaster_state_machine_happy_path()
    {
        $klaster = OperasiKlaster::create(['id_insiden' => 1, 'id_master_klaster' => 1, 'status_klaster' => 'nonaktif']);

        // nonaktif -> aktif
        $klaster = $this->klasterService->activate($klaster);
        $this->assertEquals('aktif', $klaster->status_klaster);
        $this->assertNotNull($klaster->waktu_aktivasi);

        // Update progress
        $klaster = $this->klasterService->updateProgress($klaster, 50.5);
        $this->assertEquals(50.5, $klaster->progres_persen);

        // aktif -> selesai
        $klaster = $this->klasterService->complete($klaster);
        $this->assertEquals('selesai', $klaster->status_klaster);
        $this->assertEquals(100.0, $klaster->progres_persen);
        $this->assertNotNull($klaster->waktu_ditutup);
    }

    public function test_klaster_cannot_complete_if_has_active_tasks()
    {
        $klaster = OperasiKlaster::create(['id_insiden' => 1, 'id_master_klaster' => 1, 'status_klaster' => 'aktif']);
        
        // Add active task
        OperasiTugas::create([
            'id_operasi_klaster' => $klaster->id_klaster_operasi,
            'judul_tugas' => 'Tugas A',
            'status_tugas' => 'berjalan'
        ]);

        $this->expectException(\LogicException::class);
        $this->klasterService->complete($klaster); // Harus gagal karena ada tugas berjalan
    }

    public function test_tugas_state_machine_happy_path()
    {
        $klaster = OperasiKlaster::create(['id_insiden' => 1, 'id_master_klaster' => 1, 'status_klaster' => 'aktif']);
        $tugas = OperasiTugas::create([
            'id_operasi_klaster' => $klaster->id_klaster_operasi,
            'judul_tugas' => 'Tugas B',
            'status_tugas' => 'rencana'
        ]);

        // rencana -> berjalan
        $tugas = $this->tugasService->start($tugas);
        $this->assertEquals('berjalan', $tugas->status_tugas);

        // berjalan -> tertunda
        $tugas = $this->tugasService->pause($tugas);
        $this->assertEquals('tertunda', $tugas->status_tugas);

        // tertunda -> berjalan
        $tugas = $this->tugasService->start($tugas);
        $this->assertEquals('berjalan', $tugas->status_tugas);

        // berjalan -> selesai
        $tugas = $this->tugasService->complete($tugas);
        $this->assertEquals('selesai', $tugas->status_tugas);
        $this->assertEquals(100.0, $tugas->progres_persen);
    }
}
