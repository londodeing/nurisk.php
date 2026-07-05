<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationCoreSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Structure Levels
        $pwnuId = DB::table('org_structure_levels')->insertGetId(['name' => 'PWNU', 'weight' => 1]);
        $pcnuId = DB::table('org_structure_levels')->insertGetId(['name' => 'PCNU', 'weight' => 2]);
        $mwcId = DB::table('org_structure_levels')->insertGetId(['name' => 'MWCNU', 'weight' => 3]);

        // 2. Institutions
        $nupId = DB::table('org_institutions')->insertGetId(['name' => 'NU Peduli', 'domain' => 'command']);
        $lpbiId = DB::table('org_institutions')->insertGetId(['name' => 'LPBI NU', 'domain' => 'operations']);
        $lazisnuId = DB::table('org_institutions')->insertGetId(['name' => 'LAZISNU', 'domain' => 'finance']);

        // 3. Nodes (Concrete Unit)
        $nodeLpbiJeparaId = DB::table('org_nodes')->insertGetId([
            'institution_id' => $lpbiId,
            'structure_level_id' => $pcnuId,
            'territory_code' => '33.20', // Jepara
            'name' => 'LPBI PCNU Kabupaten Jepara',
            'status' => 'active'
        ]);

        $nodeLazisnuJeparaId = DB::table('org_nodes')->insertGetId([
            'institution_id' => $lazisnuId,
            'structure_level_id' => $pcnuId,
            'territory_code' => '33.20',
            'name' => 'LAZISNU PCNU Kabupaten Jepara',
            'status' => 'active'
        ]);

        // 4. Positions
        $ketuaId = DB::table('org_positions')->insertGetId(['name' => 'Ketua', 'level' => 1]);
        $sekretarisId = DB::table('org_positions')->insertGetId(['name' => 'Sekretaris', 'level' => 2]);
        $bendaharaId = DB::table('org_positions')->insertGetId(['name' => 'Bendahara', 'level' => 3]);

        // 5. Node Positions
        $ketuaLpbiJeparaId = DB::table('org_node_positions')->insertGetId([
            'node_id' => $nodeLpbiJeparaId,
            'position_id' => $ketuaId
        ]);
        $ketuaLazisnuJeparaId = DB::table('org_node_positions')->insertGetId([
            'node_id' => $nodeLazisnuJeparaId,
            'position_id' => $ketuaId
        ]);

        // 6. Governance Functions
        $opsCommanderId = DB::table('org_governance_functions')->insertGetId([
            'name' => 'Operational Commander',
            'description' => 'Kendali Teknis & Relawan'
        ]);
        $finControllerId = DB::table('org_governance_functions')->insertGetId([
            'name' => 'Financial Controller',
            'description' => 'Kendali Keuangan & Audit'
        ]);

        // 7. Position Functions Mapping
        DB::table('org_position_functions')->insert([
            ['node_position_id' => $ketuaLpbiJeparaId, 'function_id' => $opsCommanderId],
            ['node_position_id' => $ketuaLazisnuJeparaId, 'function_id' => $finControllerId],
        ]);

        // 8. Authorities
        $publishSitrepId = DB::table('org_authorities')->insertGetId([
            'code' => 'publish_sitrep',
            'domain' => 'operational',
            'description' => 'Rilis Situational Report Resmi'
        ]);
        $approveBudgetId = DB::table('org_authorities')->insertGetId([
            'code' => 'approve_budget',
            'domain' => 'financial',
            'description' => 'Pencairan Dana Umat'
        ]);

        // 9. Function Authorities Mapping
        DB::table('org_function_authorities')->insert([
            ['function_id' => $opsCommanderId, 'authority_id' => $publishSitrepId],
            ['function_id' => $finControllerId, 'authority_id' => $approveBudgetId],
        ]);

        // 10. Sample SK
        $skJeparaId = DB::table('org_sks')->insertGetId([
            'nomor_sk' => '01/PCNU-JPR/SK/2026',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2031-01-01',
            'status' => 'active'
        ]);

        // Catatan: Seeder Mandat dapat dilakukan nanti bersama User Seeder
    }
}
