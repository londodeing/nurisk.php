<?php

namespace Database\Seeders;

use App\Models\AuthUser;
use App\Models\AuthPenggunaProfil;
use App\Models\AuthRole;
use App\Models\AuthRoleApplication;
use App\Models\WilayahDesa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PreProductionUatSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $password = Hash::make('password');

        // Roles
        $roleRelawan = AuthRole::where('nama_peran', 'relawan')->first()->id_peran;
        $roleTrc = AuthRole::where('nama_peran', 'trc')->first()->id_peran;
        $roleKanPcnu = AuthRole::where('nama_peran', 'kandidat_admin_pcnu')->first()->id_peran;
        $roleKanPwnu = AuthRole::where('nama_peran', 'kandidat_admin_pwnu')->first()->id_peran;
        $rolePcnu = AuthRole::where('nama_peran', 'pcnu')->first()->id_peran;
        $rolePwnu = AuthRole::where('nama_peran', 'pwnu')->first()->id_peran;

        // Base Data
        $desa = WilayahDesa::first();
        $id_desa = $desa ? $desa->id_desa : '3301012001';

        DB::transaction(function () use ($faker, $password, $roleRelawan, $roleTrc, $roleKanPcnu, $roleKanPwnu, $rolePcnu, $rolePwnu, $id_desa) {
            
            // Helper to create user
            $createUser = function($prefix, $i, $roleId, $statusAkun, $statusKetersediaan) use ($faker, $password, $id_desa) {
                $hp = "08120000" . str_pad($prefix, 2, '0', STR_PAD_LEFT) . str_pad($i, 2, '0', STR_PAD_LEFT);
                
                $user = AuthUser::create([
                    'id_peran' => $roleId,
                    'no_hp' => $hp,
                    'kata_sandi' => $password,
                    'status_akun' => $statusAkun,
                    'status_ketersediaan' => $statusKetersediaan,
                    'is_tersedia' => ($statusKetersediaan === AuthUser::READINESS_READY),
                    'default_scope_type' => 'pcnu',
                    'default_scope_id' => 1,
                ]);

                AuthPenggunaProfil::create([
                    'id_pengguna' => $user->id_pengguna,
                    'nama_lengkap' => "UAT $prefix User $i",
                    'nik' => $faker->nik,
                    'email' => "uat_{$prefix}_{$i}@example.com",
                    'tempat_lahir' => $faker->city,
                    'tanggal_lahir' => $faker->date('Y-m-d', '2000-01-01'),
                    'jenis_kelamin' => $i % 2 == 0 ? 'P' : 'L',
                    'alamat' => $faker->address,
                    'id_desa_domisili' => $id_desa,
                    'profesi' => $faker->jobTitle,
                    'pengalaman_kebencanaan' => 'Pengalaman simulasi UAT',
                ]);

                return $user;
            };

            // 1. 5 Relawan Baru (registered / not_ready)
            for($i=1; $i<=5; $i++) {
                $createUser('REL_NEW', $i, $roleRelawan, AuthUser::STATUS_REGISTERED, AuthUser::READINESS_NOT_READY);
            }

            // 2. 5 Relawan Aktif (active / ready)
            for($i=1; $i<=5; $i++) {
                $createUser('REL_ACT', $i, $roleRelawan, AuthUser::STATUS_ACTIVE, AuthUser::READINESS_READY);
            }

            // Ensure dummy Keahlian & Sertifikasi exist
            $keahlian = \App\Models\AuthKeahlianMaster::firstOrCreate(
                ['nama_keahlian' => 'SAR Darat'],
                ['deskripsi' => 'Kemampuan evakuasi darat']
            );

            // 3. 5 TRC (3 ready, 2 not_ready)
            for($i=1; $i<=5; $i++) {
                $statusReady = ($i <= 3) ? AuthUser::READINESS_READY : AuthUser::READINESS_NOT_READY;
                $trc = $createUser('TRC', $i, $roleTrc, AuthUser::STATUS_ACTIVE, $statusReady);
                
                // Beri skill fiktif jika ready
                if ($statusReady === AuthUser::READINESS_READY) {
                    $trc->keahlian()->attach([$keahlian->id_keahlian]);
                }
            }

            // 4. 5 Kandidat Admin (registered/pending)
            for($i=1; $i<=5; $i++) {
                $roleId = ($i <= 3) ? $roleKanPcnu : $roleKanPwnu;
                $user = $createUser('KAN_ADM', $i, $roleId, AuthUser::STATUS_REGISTERED, AuthUser::READINESS_NOT_READY);
                
                AuthRoleApplication::create([
                    'id_pengguna' => $user->id_pengguna,
                    'id_peran_diminta' => ($i <= 3) ? $rolePcnu : $rolePwnu,
                    'status_aplikasi' => 'pending',
                    'waktu_pengajuan' => now(),
                    'catatan' => 'UAT Kandidat',
                ]);
            }

            // 5. 5 Admin Aktif PCNU & PWNU
            for($i=1; $i<=5; $i++) {
                $roleId = ($i <= 3) ? $rolePcnu : $rolePwnu;
                $createUser('ADM_ACT', $i, $roleId, AuthUser::STATUS_ACTIVE, AuthUser::READINESS_NOT_READY);
            }

            // 6. 5 Akun Multi-Role (Relawan on_mission, Spatie: koordinator_klaster)
            for($i=1; $i<=5; $i++) {
                $user = $createUser('MULTI', $i, $roleRelawan, AuthUser::STATUS_ACTIVE, AuthUser::READINESS_ON_MISSION);
                
                // Tambahkan role spatie jika package terinstall
                if (class_exists(\Spatie\Permission\Models\Role::class)) {
                    $spatieRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'koordinator_klaster']);
                    
                    \Illuminate\Support\Facades\DB::table('model_has_roles')->insertOrIgnore([
                        'role_id' => $spatieRole->id,
                        'model_type' => AuthUser::class,
                        'model_id' => $user->id_pengguna,
                    ]);
                }
            }

        });
    }
}
