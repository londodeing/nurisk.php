<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AuthRoleSeeder::class,
            AuthUserSeeder::class,
            JabatanPosisiSeeder::class,
            WilayahSeeder::class,
            BencanaMasterJenisSeeder::class,
            KeahlianMasterSeeder::class,
            MasterKlasterSeeder::class,
            DemoBanjirKudusSeeder::class,
            \Database\Seeders\Operasi\PosAjuSeeder::class,
        ]);
    }
}
