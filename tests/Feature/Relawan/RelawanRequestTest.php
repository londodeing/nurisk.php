<?php

namespace Tests\Feature\Relawan;

use Tests\TestCase;
use App\Http\Requests\Relawan\StoreRelawanKebutuhanRequest;
use App\Http\Requests\Relawan\DaftarRelawanRequest;
use App\Http\Requests\Relawan\RejectRelawanRequest;
use App\Http\Requests\Relawan\AssignRelawanRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\CreatesRelawanSchema;
use Illuminate\Support\Facades\Schema;

class RelawanRequestTest extends TestCase
{
    use DatabaseTransactions;
    use CreatesRelawanSchema, \Tests\Support\CreatesOperasiSchema;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createOperasiSchema();
        
        Schema::disableForeignKeyConstraints();






        $this->createRelawanSchema();
        
        Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\DB::table('bencana_master_jenis')->insertOrIgnore(['id_jenis' => 1, 'nama_bencana' => 'Bencana', 'slug' => 'bencana']);
        \App\Models\OrganisasiUnit::create(['nama_unit' => 'Unit Test', 'tipe_unit' => 'pcnu']);
        \App\Models\OrganisasiPcnu::create(['id_pcnu' => 1, 'id_unit' => 1, 'nama_pcnu' => 'PCNU Test']);
        $insiden = \App\Models\OperasiInsiden::create(['kode_kejadian' => 'KODE-1', 'id_jenis_bencana' => 1, 'id_pcnu' => 1, 'waktu_mulai' => now()]);
        \App\Models\RelawanKebutuhan::create(['deskripsi_tugas' => 'Deskripsi Default', 'judul_posisi' => 'Default Posisi', 'id_insiden' => $insiden->id_insiden,
            'jumlah_dibutuhkan' => 10,
            'deskripsi_tugas' => 'Test',
            'status_rekrutmen' => 'dibuka'
        ]);
        Schema::enableForeignKeyConstraints();
    }

    public function test_store_kebutuhan_request_rules()
    {
        $request = new StoreRelawanKebutuhanRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('uuid_insiden', $rules);
        $this->assertArrayHasKey('jumlah_dibutuhkan', $rules);
        $this->assertArrayHasKey('deskripsi_tugas', $rules);

        $insiden_uuid = \App\Models\OperasiInsiden::first()->uuid_insiden;

        $validator = Validator::make([
            'uuid_insiden' => $insiden_uuid,
            'jumlah_dibutuhkan' => 10,
            'deskripsi_tugas' => 'Dapur Umum',
            'tgl_mulai_tugas' => now()->toDateString(),
            'tgl_selesai_tugas' => now()->addDays(2)->toDateString(),
        ], $rules);

        $this->assertTrue($validator->passes());

        $validatorFail = Validator::make([
            'uuid_insiden' => '', // required
            'jumlah_dibutuhkan' => 0, // min 1
            'tgl_selesai_tugas' => now()->subDays(2)->toDateString(), // before tgl_mulai
        ], $rules);

        $this->assertFalse($validatorFail->passes());
    }

    public function test_daftar_relawan_request_rules()
    {
        $request = new DaftarRelawanRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('id_relawan_kebutuhan', $rules);

        $kebutuhan = \App\Models\RelawanKebutuhan::first();
        
        $validator = Validator::make([
            'id_relawan_kebutuhan' => $kebutuhan->id_relawan_kebutuhan,
            'motivasi_singkat' => 'Saya mau bantu',
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_reject_relawan_request_rules()
    {
        $request = new RejectRelawanRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('catatan_verifikator', $rules);

        $validator = Validator::make([
            'catatan_verifikator' => 'Kapasitas sudah penuh',
        ], $rules);

        $this->assertTrue($validator->passes());

        $validatorFail = Validator::make([], $rules);
        $this->assertFalse($validatorFail->passes());
    }

    public function test_assign_relawan_request_rules()
    {
        \App\Models\AuthRole::insertOrIgnore([
            ['id_peran' => 1, 'nama_peran' => 'super_admin', 'level_otoritas' => 1],
        ]);

        $user = \App\Models\AuthUser::create([
            'no_hp' => '08' . rand(100000000, 999999999), 'kata_sandi' => 'hash', 'id_peran' => 1,
            'no_hp' => '081211112222',
            'kata_sandi' => bcrypt('password'),
            'status_akun' => 'aktif',
        ]);

        $insiden = \App\Models\OperasiInsiden::first();

        $posaju = \App\Models\OperasiPosaju::create([
            'uuid_insiden' => $insiden ? $insiden->id_insiden : null,
            'nama_posaju' => 'Pos Aju Test',
            'pj_posaju' => $user->id_pengguna,
            'status_alur' => 'aktif',
        ]);

        $request = new AssignRelawanRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'id_posaju' => $posaju->id_posaju,
            'peran_lapangan' => 'Koordinator',
        ], $rules);

        $this->assertTrue($validator->passes());
    }
}
