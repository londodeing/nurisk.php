<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssetRegistryValidationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Simulasi Org Structure Levels & Institutions
        \Illuminate\Support\Facades\DB::table('org_structure_levels')->updateOrInsert(['id' => 1], ['name' => 'PWNU']);
        \Illuminate\Support\Facades\DB::table('org_structure_levels')->updateOrInsert(['id' => 2], ['name' => 'PCNU']);
        \Illuminate\Support\Facades\DB::table('org_structure_levels')->updateOrInsert(['id' => 3], ['name' => 'MWC']);
        
        \Illuminate\Support\Facades\DB::table('org_institutions')->updateOrInsert(['id' => 1], ['name' => 'Syuriah/Tanfidziyah']);
        \Illuminate\Support\Facades\DB::table('org_institutions')->updateOrInsert(['id' => 2], ['name' => 'Lazisnu']);
        \Illuminate\Support\Facades\DB::table('org_institutions')->updateOrInsert(['id' => 3], ['name' => 'GP Ansor']);

        // Simulasi Org Nodes
        \Illuminate\Support\Facades\DB::table('org_nodes')->updateOrInsert(['id' => 1], ['name' => 'PWNU Jawa Tengah', 'structure_level_id' => 1, 'institution_id' => 1, 'territory_code' => '33']);
        \Illuminate\Support\Facades\DB::table('org_nodes')->updateOrInsert(['id' => 2], ['name' => 'PCNU Demak', 'structure_level_id' => 2, 'institution_id' => 1, 'territory_code' => '33.21']);
        \Illuminate\Support\Facades\DB::table('org_nodes')->updateOrInsert(['id' => 3], ['name' => 'PCNU Jepara', 'structure_level_id' => 2, 'institution_id' => 1, 'territory_code' => '33.20']);
        \Illuminate\Support\Facades\DB::table('org_nodes')->updateOrInsert(['id' => 4], ['name' => 'MWC Welahan', 'structure_level_id' => 3, 'institution_id' => 1, 'territory_code' => '33.20.01']);
        \Illuminate\Support\Facades\DB::table('org_nodes')->updateOrInsert(['id' => 5], ['name' => 'Lazisnu PWNU', 'structure_level_id' => 1, 'institution_id' => 2, 'territory_code' => '33']);
        \Illuminate\Support\Facades\DB::table('org_nodes')->updateOrInsert(['id' => 6], ['name' => 'GP Ansor Jepara', 'structure_level_id' => 2, 'institution_id' => 3, 'territory_code' => '33.20']);

        $assets = [
            // FACILITY
            ['name' => 'Kantor PWNU Jateng', 'cat' => 'FACILITY', 'owner' => 1, 'cust' => 1, 'legal' => 'Yayasan PWNU', 'affil' => null, 'status' => 'VALIDATED'],
            ['name' => 'Gedung PCNU Demak', 'cat' => 'FACILITY', 'owner' => 2, 'cust' => 2, 'legal' => 'Yayasan PCNU', 'affil' => null, 'status' => 'VERIFIED'],
            ['name' => 'RSNU Demak', 'cat' => 'FACILITY', 'owner' => 2, 'cust' => null, 'legal' => 'PT RSNU Demak', 'affil' => null, 'status' => 'VALIDATED'],
            ['name' => 'RSNU Kudus', 'cat' => 'FACILITY', 'owner' => null, 'cust' => null, 'legal' => 'Yayasan RSNU', 'affil' => null, 'status' => 'UNVERIFIED'],
            ['name' => 'Klinik MWC Welahan', 'cat' => 'FACILITY', 'owner' => 4, 'cust' => 4, 'legal' => 'H. Ahmad', 'affil' => null, 'status' => 'VERIFIED'],
            ['name' => 'Pesantren Al-Hikmah Jepara', 'cat' => 'FACILITY', 'owner' => 3, 'cust' => null, 'legal' => 'Yayasan Al-Hikmah', 'affil' => null, 'status' => 'VERIFIED'],
            ['name' => 'Pesantren Tahfidz Demak', 'cat' => 'FACILITY', 'owner' => 2, 'cust' => null, 'legal' => 'Kyai Hasan', 'affil' => null, 'status' => 'UNVERIFIED'],
            ['name' => 'Sekolah MA NU Kudus', 'cat' => 'FACILITY', 'owner' => null, 'cust' => null, 'legal' => 'LP Ma\'arif', 'affil' => null, 'status' => 'VERIFIED'],
            ['name' => 'Masjid Agung Demak (Afiliasi)', 'cat' => 'FACILITY', 'owner' => null, 'cust' => null, 'legal' => 'Takmir Masjid', 'affil' => 2, 'status' => 'UNVERIFIED'],
            ['name' => 'Gudang Logistik NU Peduli', 'cat' => 'FACILITY', 'owner' => 3, 'cust' => 5, 'legal' => 'Sewa H. Budi', 'affil' => null, 'status' => 'VERIFIED'],
            ['name' => 'Tanah Wakaf Ranting Bawu', 'cat' => 'FACILITY', 'owner' => null, 'cust' => null, 'legal' => 'Belum Sertifikat', 'affil' => 3, 'status' => 'UNVERIFIED'],
            ['name' => 'Aula Ponpes X', 'cat' => 'FACILITY', 'owner' => null, 'cust' => null, 'legal' => 'Ponpes X', 'affil' => 2, 'status' => 'UNVERIFIED'],

            // FLEET
            ['name' => 'Ambulans Lazisnu PWNU', 'cat' => 'FLEET', 'owner' => 1, 'cust' => 5, 'legal' => 'Yayasan Lazisnu', 'affil' => null, 'status' => 'VALIDATED'],
            ['name' => 'Ambulans NU Peduli Jepara', 'cat' => 'FLEET', 'owner' => 3, 'cust' => null, 'legal' => 'PCNU Jepara', 'affil' => null, 'status' => 'VERIFIED'],
            ['name' => 'Ambulans Klinik MWC', 'cat' => 'FLEET', 'owner' => 4, 'cust' => 4, 'legal' => 'Klinik MWC', 'affil' => null, 'status' => 'VERIFIED'],
            ['name' => 'Ambulans Banser Demak', 'cat' => 'FLEET', 'owner' => null, 'cust' => null, 'legal' => 'PC GP Ansor', 'affil' => 2, 'status' => 'VERIFIED'],
            ['name' => 'Mobil Operasional PCNU Kudus', 'cat' => 'FLEET', 'owner' => null, 'cust' => null, 'legal' => 'H. Syukur', 'affil' => null, 'status' => 'UNVERIFIED'],
            ['name' => 'Mobil Rescue LPBI', 'cat' => 'FLEET', 'owner' => 1, 'cust' => null, 'legal' => 'LPBI NU', 'affil' => null, 'status' => 'VERIFIED'],
            ['name' => 'Truk Logistik PWNU', 'cat' => 'FLEET', 'owner' => 1, 'cust' => 1, 'legal' => 'PWNU Jateng', 'affil' => null, 'status' => 'VALIDATED'],
            ['name' => 'Truk Tangki Air Bersih Lazisnu', 'cat' => 'FLEET', 'owner' => 5, 'cust' => 5, 'legal' => 'Lazisnu Pusat', 'affil' => 1, 'status' => 'VERIFIED'],
            ['name' => 'Perahu Karet LPBI Jepara', 'cat' => 'FLEET', 'owner' => 3, 'cust' => null, 'legal' => 'Bantuan BNPB', 'affil' => null, 'status' => 'VALIDATED'],
            ['name' => 'Perahu Fiber Banser Bahari', 'cat' => 'FLEET', 'owner' => 2, 'cust' => null, 'legal' => 'Swadaya Nelayan', 'affil' => 2, 'status' => 'UNVERIFIED'],
            ['name' => 'Motor Trail TRC Banser', 'cat' => 'FLEET', 'owner' => null, 'cust' => 6, 'legal' => 'Relawan A', 'affil' => 6, 'status' => 'UNVERIFIED'],

            // EQUIPMENT
            ['name' => 'Genset 10.000 Watt PCNU Demak', 'cat' => 'EQUIPMENT', 'owner' => 2, 'cust' => 2, 'legal' => 'Sumbangan', 'affil' => null, 'status' => 'VERIFIED'],
            ['name' => 'Genset Portable LPBI', 'cat' => 'EQUIPMENT', 'owner' => 1, 'cust' => null, 'legal' => 'Inventaris LPBI', 'affil' => null, 'status' => 'VERIFIED'],
            ['name' => 'Pompa Air Alkon Banser', 'cat' => 'EQUIPMENT', 'owner' => 6, 'cust' => 6, 'legal' => 'Banser', 'affil' => 3, 'status' => 'VERIFIED'],
            ['name' => 'Chainsaw (Gergaji Mesin) TRC', 'cat' => 'EQUIPMENT', 'owner' => null, 'cust' => null, 'legal' => 'Relawan', 'affil' => 2, 'status' => 'UNVERIFIED'],
            ['name' => 'Repeater Radio Komunikasi', 'cat' => 'EQUIPMENT', 'owner' => 1, 'cust' => 1, 'legal' => 'PWNU Jateng', 'affil' => null, 'status' => 'VALIDATED'],
            ['name' => 'Tenda Peleton Pengungsi', 'cat' => 'EQUIPMENT', 'owner' => 3, 'cust' => null, 'legal' => 'BNPB', 'affil' => 3, 'status' => 'VERIFIED'],
            ['name' => 'Drone Thermal Pemantau', 'cat' => 'EQUIPMENT', 'owner' => 1, 'cust' => null, 'legal' => 'LPBI', 'affil' => null, 'status' => 'VALIDATED'],
        ];

        foreach ($assets as $idx => $asset) {
            \App\Models\OrgAsset::create([
                'asset_code' => 'AST-'.str_pad($idx+1, 4, '0', STR_PAD_LEFT),
                'name' => $asset['name'],
                'legal_owner_name' => $asset['legal'],
                'category' => $asset['cat'],
                'owner_node_id' => $asset['owner'] ?? 1,
                'custodian_node_id' => $asset['cust'],
                'affiliated_node_id' => $asset['affil'],
                'home_territory_code' => '33.20',
                'status' => 'AKTIF',
                'readiness' => 'AVAILABLE',
                'verification_status' => $asset['status'],
                'metadata' => ['seeder' => true]
            ]);
        }
    }
}
