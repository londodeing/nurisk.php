<?php

namespace Tests\Feature\Operasi;

use App\Models\AuthUser;
use App\Models\AuthRole;
use App\Models\OperasiInsiden;
use App\Models\AssessmentUtama;
use App\Models\OperasiPenugasan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class AssessmentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_trigger_single_latest_assessment()
    {
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'terverifikasi']);

        $ass1 = AssessmentUtama::create([
            'id_insiden' => $insiden->id_insiden,
            'id_petugas_assessment' => 1, 'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Area 1',
            'is_latest' => true,
            'waktu_assesment' => now()->subHours(2)
        ]);

        $this->assertTrue($ass1->fresh()->is_latest);

        $ass2 = AssessmentUtama::create([
            'id_insiden' => $insiden->id_insiden,
            'id_petugas_assessment' => 1,
            'jenis_laporan' => 'pendataan_lanjutan',
            'cakupan_wilayah_deskripsi' => 'Area 1',
            'is_latest' => true,
            'waktu_assesment' => now()->subHour(1)
        ]);

        $this->assertFalse($ass1->fresh()->is_latest);
        $this->assertTrue($ass2->fresh()->is_latest);
        
        $ass3 = AssessmentUtama::create([
            'id_insiden' => $insiden->id_insiden,
            'id_petugas_assessment' => 1,
            'jenis_laporan' => 'pendataan_lanjutan',
            'cakupan_wilayah_deskripsi' => 'Area 1',
            'is_latest' => true,
            'waktu_assesment' => now()
        ]);

        $this->assertFalse($ass1->fresh()->is_latest);
        $this->assertFalse($ass2->fresh()->is_latest);
        $this->assertTrue($ass3->fresh()->is_latest);
    }

    private function createAuthUserWithRole($roleName)
    {
        $user = AuthUser::factory()->aktif()->create();
        $role = AuthRole::firstOrCreate(['nama_peran' => $roleName], ['deskripsi' => 'Role', 'level_otoritas' => 1]);
        $user->id_peran = $role->id_peran;
        $user->save();
        return $user;
    }

    public function test_api_store_assessment_flat_endpoint_with_dampak_and_kebutuhan()
    {
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon', 'no_spk_assesment' => 'SPK-123'])->refresh();
        $user = $this->createAuthUserWithRole('super_admin');
        
        $this->actingAs($user, 'sanctum');

        $payload = [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_petugas_assessment' => 1, 'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Desa XYZ Testing',
            'waktu_assesment' => now()->format('Y-m-d H:i:s'),
            'dampak_manusia' => [
                'meninggal' => 5,
                'hilang' => 2
            ],
            'kebutuhan_mendesak' => [
                ['nama_kebutuhan' => 'Tenda', 'jumlah' => 10, 'satuan' => 'Unit'],
                ['nama_kebutuhan' => 'Air', 'jumlah' => 100, 'satuan' => 'Liter']
            ]
        ];

        $response = $this->postJson("/api/v1/assessment", $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid_assessment',
                'uuid_insiden',
                'dampak_manusia',
                'kebutuhan_mendesak'
            ]
        ]);
        
        $this->assertDatabaseHas('assessment_utama', [
            'id_insiden' => $insiden->id_insiden,
            'cakupan_wilayah_deskripsi' => 'Desa XYZ Testing'
        ]);

        $this->assertDatabaseHas('assessment_dampak_manusia_v2', [
            'id_assessment' => AssessmentUtama::first()->id_assessment_utama,
            'meninggal' => 5,
            'hilang' => 2
        ]);

        $this->assertDatabaseHas('assessment_kebutuhan_mendesak', [
            'nama_kebutuhan' => 'Tenda',
            'jumlah' => 10
        ]);
    }

    public function test_br_assessment_001_only_terverifikasi_or_respon_allowed()
    {
        $user = $this->createAuthUserWithRole('super_admin');
        $this->actingAs($user, 'sanctum');

        // Draft
        $insidenDraft = OperasiInsiden::factory()->create(['status_insiden' => 'draft']);
        $response1 = $this->postJson("/api/v1/assessment", [
            'uuid_insiden' => $insidenDraft->uuid_insiden,
            'id_petugas_assessment' => 1, 'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Desa XYZ Testing',
            'waktu_assesment' => now()->format('Y-m-d H:i:s'),
            'dampak_manusia' => ['meninggal' => 0]
        ]);
        $response1->assertStatus(403);

        // Selesai
        $insidenSelesai = OperasiInsiden::factory()->create(['status_insiden' => 'selesai']);
        $response2 = $this->postJson("/api/v1/assessment", [
            'uuid_insiden' => $insidenSelesai->uuid_insiden,
            'id_petugas_assessment' => 1, 'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Desa XYZ Testing',
            'waktu_assesment' => now()->format('Y-m-d H:i:s'),
            'dampak_manusia' => ['meninggal' => 0]
        ]);
        $response2->assertStatus(403);
    }

    public function test_lapis_4_auth_relawan_with_trc_assignment_allowed()
    {
        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'respon', 'no_spk_assesment' => 'SPK-123'])->refresh();
        $relawan = $this->createAuthUserWithRole('relawan');
        $this->actingAs($relawan, 'sanctum');

        // 1. Without assignment -> Forbidden
        $response = $this->postJson("/api/v1/assessment", [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_petugas_assessment' => 1, 'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Desa XYZ Testing',
            'waktu_assesment' => now()->format('Y-m-d H:i:s'),
            'dampak_manusia' => ['meninggal' => 0]
        ]);
        $response->assertStatus(403);

        // 2. With TRC Assignment -> Allowed
        OperasiPenugasan::create([
            'id_insiden' => $insiden->id_insiden,
            'id_pengguna' => $relawan->id_pengguna,
            'peran_otoritas' => 'trc',
            'waktu_mulai' => now(),
            'waktu_selesai' => null,
            'ditugaskan_oleh' => $relawan->id_pengguna // Fix for NOT NULL constraint
        ]);

        $response2 = $this->postJson("/api/v1/assessment", [
            'uuid_insiden' => $insiden->uuid_insiden,
            'id_petugas_assessment' => 1, 'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Desa XYZ Testing',
            'waktu_assesment' => now()->format('Y-m-d H:i:s'),
            'dampak_manusia' => ['meninggal' => 0]
        ]);
        $response2->assertStatus(201);
    }

    public function test_br_assessment_008_cannot_delete_assessment_used_by_sitrep()
    {
        if (!Schema::hasTable('operasi_sitrep')) {
            Schema::create('operasi_sitrep', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_assessment_basis');
                $table->timestamp('dihapus_pada')->nullable();
            });
        }

        $insiden = OperasiInsiden::factory()->create(['status_insiden' => 'terverifikasi']);
        $ass = AssessmentUtama::create([
            'id_insiden' => $insiden->id_insiden,
            'id_petugas_assessment' => 1, 'jenis_laporan' => 'kaji_cepat',
            'cakupan_wilayah_deskripsi' => 'Area 1',
            'is_latest' => true,
            'waktu_assesment' => now()
        ]);

        $user = $this->createAuthUserWithRole('super_admin');
        
        \Illuminate\Support\Facades\DB::table('operasi_sitrep')->insert([
            'uuid_sitrep' => \Illuminate\Support\Str::uuid(),
            'id_insiden' => $insiden->id_insiden,
            'nomor_sitrep' => 1,
            'id_assessment_basis' => $ass->id_assessment_utama,
            'waktu_sitrep' => now(),
            'id_pembuat' => $user->id_pengguna,
            'dibuat_pada' => now()
        ]);
        
        $policy = app(\App\Policies\AssessmentPolicy::class);
        $this->assertFalse($policy->delete($user, $ass));
    }
}
