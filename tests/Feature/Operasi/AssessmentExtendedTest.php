<?php

namespace Tests\Feature\Operasi;

use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\Assessment\AssessmentKebutuhanNumerikMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssessmentExtendedTest extends TestCase
{
    use DatabaseTransactions;

    private function createAuthUserWithRole($roleName)
    {
        $user = AuthUser::factory()->aktif()->create();
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);
        $user->id_peran = $role->id_peran;
        $user->save();
        return $user;
    }

    public function test_api_master_kebutuhan_numerik()
    {
        $user = $this->createAuthUserWithRole('super_admin');
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/master/kebutuhan-numerik');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'pangan',
                'sandang',
                'papan',
                'kesehatan',
                'peralatan'
            ]
        ]);
    }

    public function test_api_store_and_update_assessment_lengkap()
    {
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon', 'no_spk_assesment' => 'SPK-123']);
        $user = $this->createAuthUserWithRole('super_admin');
        $this->actingAs($user, 'sanctum');

        $payload = [
            'event_date' => '2026-06-25',
            'event_time' => '14:30',
            'jenis_laporan' => 'kaji_cepat',
            'id_kecamatan' => null,
            'id_desa' => null,
            'alamat_spesifik' => 'RT 02 RW 03, Kampung Baru',
            'region' => 'Kec. Cilacap Selatan, Desa Baru',
            'latitude' => -7.72,
            'longitude' => 109.01,
            'kondisi_mutakhir' => 'Banjir setinggi 1 meter',
            'upaya_penanganan' => 'Evakuasi warga menggunakan perahu karet',
            'sebaran_dampak' => 'Menggenangi 3 RT',
            'kendala_lapangan' => 'Arus deras',
            'rekomendasi_aksi' => 'Distribusi logistik makanan siap saji',
            'kebutuhan_lanjutan' => [
                'kebutuhan_relawan' => 'Relawan medis 10 orang',
                'kebutuhan_logistik' => 'Tenda dan selimut',
                'kebutuhan_peralatan' => 'Genset dan pompa air',
                'kebutuhan_medis' => 'Obat-obatan dasar',
                'kebutuhan_pangan' => 'Makanan siap saji',
                'kebutuhan_lainnya' => 'Dukungan psikososial',
            ],
            'needs_numeric' => [
                'sembako' => 150,
                'selimut' => 300,
                'genset' => 2,
            ],
            'dampak_manusia' => [
                'meninggal' => 0,
                'hilang' => 0,
                'luka_berat' => 1,
                'luka_ringan' => 5,
                'dampak_manusia' => 450, // menderita/mengungsi
                'pengungsi_jiwa' => 200,
                'pengungsi_kk' => 50,
            ],
            'dampak_rumah' => [
                'berat' => 2,
                'sedang' => 5,
                'ringan' => 15,
            ],
            'dampak_fasum' => [
                'sanitas' => 1,
                'pendidikan' => 1,
                'kesehatan' => 0,
                'ibadah' => 2,
            ],
            'dampak_vital' => [
                'air' => 50,
                'listrik' => 100,
                'telkom' => 0,
                'irigasi' => 0.5,
                'jalan' => 0.1,
            ],
            'dampak_lingkungan' => [
                'sawah' => 10.5,
                'hutan' => 0,
                'unggas' => 50,
                'kaki_empat' => 5,
                'perikanan_kolam' => 2.5,
                'perikanan_nelayan' => 3
            ],
            'dampak_ekonomi' => [
                'persentase' => '25% - 50%',
                'sektor_1' => 'Pertanian',
                'kontribusi_1' => 60,
                'status_1' => 'sementara',
                'sektor_2' => 'Perdagangan',
                'kontribusi_2' => 30,
                'status_2' => 'tidak_terdampak',
                'distribusi' => 'rusak_sebagian',
                'fasilitas' => 'berfungsi',
            ],
        ];

        // 1. Store
        $response = $this->postJson("/api/insiden/{$insiden->uuid_insiden}/assessment", $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('data.narasi.kondisi_mutakhir', 'Banjir setinggi 1 meter');

        $assessmentId = $response->json('data.id');
        $utama = AssessmentUtama::where('id_assessment_utama', $assessmentId)->first();

        $this->assertDatabaseHas('assessment_lokasi_detail', [
            'id_assessment' => $assessmentId,
            'id_kec' => null,
            'alamat_spesifik' => 'RT 02 RW 03, Kampung Baru'
        ]);

        $this->assertDatabaseHas('assessment_narasi_detail', [
            'id_assessment' => $assessmentId,
            'kondisi_umum' => 'Banjir setinggi 1 meter',
            'upaya_penanganan' => 'Evakuasi warga menggunakan perahu karet'
        ]);

        $utama = AssessmentUtama::where('id_assessment_utama', $assessmentId)->first();
        if (!$utama) dd("AssessmentUtama is null!");
        
        $this->assertDatabaseHas('assessment_kebutuhan_lanjutan', [
            'id_assessment' => $assessmentId,
            'kebutuhan_relawan' => 'Relawan medis 10 orang',
        ]);

        $this->assertDatabaseHas('assessment_dampak_manusia_v2', [
            'id_assessment' => $assessmentId,
            'luka_berat' => 1,
            'terdampak_jiwa' => 450
        ]);

        $this->assertDatabaseHas('assessment_dampak_lingkungan', [
            'id_assessment' => $assessmentId,
            'lahan_pertanian_rusak_ha' => 10.50,
            'ternak_unggas_ekor' => 50,
            'ternak_kaki_empat_ekor' => 5,
            'perikanan_kolam_ha' => 2.50,
            'perikanan_nelayan_unit' => 3
        ]);

        $this->assertDatabaseHas('assessment_dampak_ekonomi', [
            'id_assessment' => $assessmentId,
            'persentase_ekonomi_terdampak' => '25% - 50%',
            'sektor_pencaharian_1' => 'Pertanian',
            'kontribusi_1' => 60.00,
            'status_terdampak_1' => 'sementara',
            'distribusi_hasil_panen' => 'rusak_sebagian',
            'fasilitas_pengolahan_kolektif' => 'berfungsi'
        ]);

        // 2. Show
        $showResponse = $this->getJson("/api/insiden/{$insiden->uuid_insiden}/assessment/{$assessmentId}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonStructure([
            'data',
            'form_data'
        ]);
        $showResponse->assertJsonPath('form_data.needs_numeric.sembako', '150.00');

        // 3. Update
        $payload['kondisi_mutakhir'] = 'Air mulai surut';
        $payload['needs_numeric']['sembako'] = 100;
        $payload['needs_numeric']['selimut'] = 0; // should delete/remove

        $updateResponse = $this->putJson("/api/insiden/{$insiden->uuid_insiden}/assessment/{$assessmentId}", $payload);
        $updateResponse->assertStatus(200);

        $this->assertDatabaseHas('assessment_narasi_detail', [
            'id_assessment' => $assessmentId,
            'kondisi_umum' => 'Air mulai surut'
        ]);

        $this->assertDatabaseHas('assessment_kebutuhan_numerik', [
            'id_assessment' => $assessmentId,
            'jumlah_dibutuhkan' => 100.00
        ]);

        $sembakoMaster = AssessmentKebutuhanNumerikMaster::where('kode_item', 'selimut')->first();
        $this->assertDatabaseMissing('assessment_kebutuhan_numerik', [
            'id_assessment' => $assessmentId,
            'id_item' => $sembakoMaster->id_item
        ]);
    }
}
