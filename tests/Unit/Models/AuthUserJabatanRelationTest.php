<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\AuthUser;
use App\Models\PenggunaJabatan;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class AuthUserJabatanRelationTest extends TestCase
{
    use DatabaseTransactions;



    public function test_jabatan_posisi_relation_on_auth_user_returns_has_many(): void
    {
        $user = new AuthUser();
        $relation = $user->jabatanPosisi();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('id_pengguna', $relation->getForeignKeyName());
    }

    public function test_jabatan_aktif_relation_only_returns_active_jobs(): void
    {
        $user = AuthUser::factory()->create();

        $activeJob = PenggunaJabatan::factory()->create([
            'id_pengguna' => $user->id_pengguna,
            'status_aktif' => 1,
            'berakhir_pada' => null,
        ]);

        $inactiveJob = PenggunaJabatan::factory()->create([
            'id_pengguna' => $user->id_pengguna,
            'status_aktif' => 0,
            'berakhir_pada' => null,
        ]);

        $results = $user->jabatanAktif()->get();

        $this->assertTrue($results->contains($activeJob));
        $this->assertFalse($results->contains($inactiveJob));
    }

    public function test_jabatan_aktif_excludes_jobs_with_past_expiration_date(): void
    {
        $user = AuthUser::factory()->create();

        $activeFutureJob = PenggunaJabatan::factory()->create([
            'id_pengguna' => $user->id_pengguna,
            'status_aktif' => 1,
            'berakhir_pada' => now()->addDays(5),
        ]);

        $expiredJob = PenggunaJabatan::factory()->create([
            'id_pengguna' => $user->id_pengguna,
            'status_aktif' => 1,
            'berakhir_pada' => now()->subDays(1),
        ]);

        $results = $user->jabatanAktif()->get();

        $this->assertTrue($results->contains($activeFutureJob));
        $this->assertFalse($results->contains($expiredJob));
    }
}
