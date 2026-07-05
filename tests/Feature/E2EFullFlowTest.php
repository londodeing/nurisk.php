<?php
namespace Tests\Feature;

use App\Models\AuthUser;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;

class E2EFullFlowTest extends TestCase
{
    private string $noPcnu = '085712345678';
    private string $noTrc = '082222222222';

    private ?int $idInsiden = null;
    private ?int $idPleno = null;
    private ?int $idPosAju = null;
    private ?int $idSitrep = null;
    private array $idKlaster = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // Activate and scope test users
        DB::table('auth_users')
            ->whereIn('no_hp', [$this->noPcnu, $this->noTrc, '08111111111', '081111111111'])
            ->update(['status_akun' => 'aktif']);
        DB::table('auth_users')
            ->where('no_hp', $this->noTrc)
            ->update(['default_scope_type' => 'pcnu', 'default_scope_id' => '1']);
    }

    private function loginAs(string $noHp): void
    {
        $user = AuthUser::with('peran')->where('no_hp', $noHp)->firstOrFail();
        $this->actingAs($user, 'web');
    }

    public function test_full_e2e_flow(): void
    {
        $this->e2e("=== NURISK FULL E2E FLOW ===");

        // ===== 1. LOGIN PCNU =====
        $this->e2e("--- 1. LAPOR & VERIFY (as PCNU) ---");
        $this->loginAs($this->noPcnu);
        $this->get('/dashboard/pcnu');

        // ===== 2. LAPOR =====
        $ts = time();
        $response = $this->post('/lapor', [
            'nama' => "Test {$ts}",
            'no_hp' => '08111111111',
            'id_jenis_bencana' => '1',
            'lokasi' => 'Desa E2E, Kudus',
            'deskripsi' => "Banjir E2E {$ts}",
            'latitude' => '-6.8048',
            'longitude' => '110.8395',
            'waktu_kejadian' => '2026-06-21T10:00',
        ]);
        $this->assertTrue($response->isRedirection(), 'Lapor submit harus redirect');
        $this->pass("Lapor submit");

        // ===== 3. VERIFY =====
        $response = $this->get('/dashboard/laporan-masuk');
        $response->assertStatus(200);
        $this->pass("Laporan index");

        // Get laporan ID
        preg_match('/href="[^"]*\/laporan-masuk\/(\d+)"/', $response->content(), $m);
        $idLap = $m[1] ?? null;
        $this->assertNotNull($idLap, 'Laporan ID harus ditemukan');
        $this->pass("Laporan ID={$idLap}");

        $response = $this->get("/dashboard/laporan-masuk/{$idLap}");
        $response->assertStatus(200);

        $response = $this->post("/dashboard/laporan-masuk/{$idLap}/verify", [
            'id_pcnu' => '1',
            'prioritas' => 'tinggi',
            'status_insiden' => 'terverifikasi',
        ]);
        $this->assertTrue($response->isRedirection(), 'Verify harus redirect');
        $this->pass("Verify laporan");

        // Save insiden ID
        $insiden = DB::table('operasi_insiden')->orderBy('dibuat_pada', 'desc')->first();
        $this->idInsiden = $insiden->id_insiden ?? null;
        $this->assertNotNull($this->idInsiden, 'Insiden harus dibuat');
        $this->pass("Insiden ID={$this->idInsiden}");

        // ===== 4. LOGIN TRC =====
        $this->e2e("--- 2. ASSESSMENT (as TRC) ---");
        $this->loginAs($this->noTrc);

        // ===== 5. ASSESSMENT =====
        $uuidIns = DB::table('operasi_insiden')->where('id_insiden', $this->idInsiden)->value('uuid_insiden');

        $response = $this->get("/insiden/{$this->idInsiden}/assessment/create");
        $response->assertStatus(200);
        $this->pass("Assessment page");

        $response = $this->post("/insiden/{$this->idInsiden}/assessment", [
            'uuid_insiden' => $uuidIns,
            'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Desa E2E, Kecamatan Simulasi',
            'waktu_assesment' => '2026-06-22T08:00',
            'latitude' => '-6.8048',
            'longitude' => '110.8395',
            'dampak_manusia' => [
                'meninggal' => 2,
                'hilang' => 0,
                'luka_berat' => 5,
                'luka_ringan' => 15,
                'menderita_mengungsi' => 200,
            ],
            'kebutuhan_mendesak' => [
                ['nama_kebutuhan' => 'Makanan siap saji', 'jumlah' => 500, 'satuan' => 'pack'],
                ['nama_kebutuhan' => 'Air bersih', 'jumlah' => 1000, 'satuan' => 'liter'],
            ],
        ]);
        $this->assertTrue($response->isRedirection(), 'Assessment harus redirect');
        $this->pass("Assessment submit");

        // ===== 6. LOGIN PWNU =====
        $this->e2e("--- 3. PLENO (as PWNU) ---");
        $this->loginAs('08111111111');

        // ===== 7. PLENO =====
        $response = $this->get("/insiden/{$this->idInsiden}/pleno/create");
        $response->assertStatus(200);
        $this->pass("Pleno page");

        $response = $this->post("/insiden/{$this->idInsiden}/pleno", [
            'id_insiden' => $this->idInsiden,
            'nomor_pleno' => '',
            'waktu_pleno' => '2026-06-22T10:00',
            'jenis_pleno' => 'aktivasi_operasi',
            'pimpinan_pleno' => '8',
            'notulis_pleno' => '8',
            'lokasi_pleno' => 'Posko Utama',
            'hasil_umum' => 'Aktivasi tanggap darurat',
        ]);
        $this->assertTrue($response->isRedirection(), 'Pleno harus redirect');
        $this->pass("Pleno store");

        $pleno = DB::table('operasi_pleno')->where('id_insiden', $this->idInsiden)->orderBy('dibuat_pada', 'desc')->first();
        $this->idPleno = $pleno->id_pleno ?? null;
        $this->assertNotNull($this->idPleno, 'Pleno harus dibuat');
        $this->pass("Pleno ID={$this->idPleno}");

        // Keputusan
        $this->get("/insiden/{$this->idInsiden}/pleno/{$this->idPleno}")->assertStatus(200);
        $response = $this->post("/insiden/{$this->idInsiden}/pleno/{$this->idPleno}/keputusan", [
            'keputusan' => 'Aktivasi operasi tanggap darurat',
            'id_pembuat' => '8',
        ]);
        $this->assertTrue($response->isRedirection(), 'Keputusan harus redirect');
        $this->pass("Keputusan");

        // Tinjau
        $this->get("/insiden/{$this->idInsiden}/pleno/{$this->idPleno}")->assertStatus(200);
        $response = $this->patch("/insiden/{$this->idInsiden}/pleno/{$this->idPleno}/tinjau");
        $this->assertTrue($response->isRedirection() || $response->isSuccessful(), 'Tinjau harus sukses');
        $this->pass("Pleno tinjau");

        // Finalisasi
        $this->get("/insiden/{$this->idInsiden}/pleno/{$this->idPleno}")->assertStatus(200);
        $response = $this->patch("/insiden/{$this->idInsiden}/pleno/{$this->idPleno}/finalisasi");
        $this->assertTrue($response->isRedirection() || $response->isSuccessful(), 'Finalisasi harus sukses');
        $this->pass("Pleno finalisasi");

        // ===== 8. POS AJU =====
        $this->e2e("--- 4. POS AJU ---");
        $response = $this->get('/posaju/create');
        $response->assertStatus(200);
        $this->pass("PosAju page");

        $response = $this->post('/posaju', [
            'id_insiden' => $this->idInsiden,
            'nama_posaju' => 'Pos Aju E2E',
            'alamat_lokasi' => 'Desa E2E, Kudus',
            'pj_posaju' => '2',
        ]);
        $this->assertTrue($response->isRedirection(), 'PosAju harus redirect');
        $this->pass("PosAju store");

        $pos = DB::table('operasi_posaju')->where('id_insiden', $this->idInsiden)->orderBy('dibuat_pada', 'desc')->first();
        $this->idPosAju = $pos->id_posaju ?? null;
        $this->assertNotNull($this->idPosAju, 'PosAju harus dibuat');
        $this->pass("PosAju ID={$this->idPosAju}");

        // Activate
        $response = $this->post("/posaju/{$this->idPosAju}/activate");
        $this->assertTrue($response->isRedirection(), 'Activate harus redirect');
        $this->pass("PosAju activate");

        // ===== 9. KLASTER =====
        $this->e2e("--- 5. KLASTER ---");
        foreach ([1 => 'Kesehatan', 2 => 'SAR', 3 => 'Logistik'] as $mid => $nama) {
            $response = $this->get('/klaster/create');
            $response->assertStatus(200);

            $response = $this->post('/klaster', [
                'id_insiden' => $this->idInsiden,
                'id_master_klaster' => $mid,
                'koordinator' => "Koordinator {$nama}",
                'no_hp_koordinator' => "08111111{$mid}",
                'deskripsi' => "Klaster {$nama}",
            ]);
            $this->assertTrue($response->isRedirection(), "Klaster {$nama} harus redirect");
            $this->pass("Klaster {$nama}");

            $k = DB::table('operasi_klaster')
                ->where('id_insiden', $this->idInsiden)
                ->where('id_master_klaster', $mid)
                ->first();
            if ($k) $this->idKlaster[$mid] = $k->id_klaster_operasi;
        }

        // ===== 10. SITREP =====
        $this->e2e("--- 6. SITREP ---");
        $response = $this->get('/sitrep/create');
        $response->assertStatus(200);
        $this->pass("Sitrep page");

        $response = $this->post('/sitrep', [
            'id_insiden' => $this->idInsiden,
            'periode_sitrep' => 'Periode 1',
            'catatan' => 'Banjir masih menggenang',
        ]);
        $this->assertTrue($response->isRedirection(), 'Sitrep harus redirect');
        $this->pass("Sitrep store");

        $sit = DB::table('operasi_sitrep')->where('id_insiden', $this->idInsiden)->orderBy('dibuat_pada', 'desc')->first();
        $this->idSitrep = $sit->id_sitrep ?? null;
        $this->assertNotNull($this->idSitrep, 'Sitrep harus dibuat');
        $this->pass("Sitrep ID={$this->idSitrep}");

        // View & PDF
        $response = $this->get("/sitrep/{$this->idSitrep}");
        $response->assertStatus(200);
        $this->pass("Sitrep show");

        $response = $this->get("/sitrep/{$this->idSitrep}/pdf");
        $response->assertStatus(200);
        $this->pass("Sitrep PDF");

        // ===== 11. CLOSURE =====
        $this->e2e("--- 7. CLOSURE ---");
        foreach ($this->idKlaster as $mid => $idk) {
            $response = $this->post("/klaster/{$idk}/complete");
            $this->assertTrue($response->isRedirection(), "Klaster {$mid} close harus redirect");
            $this->pass("Klaster {$mid} close");
        }

        $response = $this->post("/posaju/{$this->idPosAju}/close");
        $this->assertTrue($response->isRedirection(), 'PosAju close harus redirect');
        $this->pass("PosAju close");

        $response = $this->put("/insiden/{$this->idInsiden}/status", [
            'status_insiden' => 'selesai',
        ]);
        $this->assertTrue($response->isRedirection() || $response->isSuccessful(), 'Insiden close harus sukses');
        $this->pass("Insiden close");

        // ===== SUMMARY =====
        echo "\n========================================\n";
        echo "  ALL STEPS PASSED!\n";
        echo "========================================\n";
    }

    // === HELPERS ===

    private int $stepCount = 0;

    private function pass(string $label): void
    {
        $this->stepCount++;
        echo "  [PASS] {$label}\n";
    }

    private function e2e(string $label): void
    {
        echo "\n{$label}\n";
    }

    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }
}
