<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunKudusSimulation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulation:kudus';

    protected $description = 'Run an interactive simulation of the Kudus Flood incident';

    public function handle()
    {
        $this->info('Starting Kudus Flood Simulation...');
        
        // Fase 1
        $this->info('Fase 1: Seeding Initial Report (DRAFT)');
        $this->call('db:seed', ['--class' => 'DemoBanjirKudusSeeder']);
        $this->ask('Tekan Enter untuk memvalidasi laporan (VERIFIED)...');
        
        // Fase 2
        $this->info('Fase 2: Laporan Divalidasi');
        $incident = \App\Models\OperationalObject::where('id', 'KUDUS-001')->first();
        if ($incident) {
            $incident->status = 'VERIFIED';
            $incident->color = '#F97316'; // Orange
            $popup = $incident->popup_json;
            $popup['status'] = 'VERIFIED';
            $popup['summary'] = 'Kaji cepat diperlukan.';
            $incident->popup_json = $popup;
            
            $timeline = $incident->timeline_json;
            $timeline[] = [
                'time' => now()->toIso8601String(),
                'status' => 'VERIFIED',
                'title' => 'Insiden Divalidasi oleh Pusdalops',
            ];
            $incident->timeline_json = $timeline;
            $incident->save();
        }
        
        $this->ask('Tekan Enter untuk menerjunkan tim (RESPONSE)...');
        
        // Fase 3
        $this->info('Fase 3: Tim Tanggap Darurat Diterjunkan');
        if ($incident) {
            $incident->status = 'RESPONSE';
            $incident->color = '#EF4444'; // Red
            $popup = $incident->popup_json;
            $popup['status'] = 'RESPONSE';
            $popup['summary'] = 'Tim SAR dan Ambulans sedang beroperasi.';
            $incident->popup_json = $popup;
            
            $timeline = $incident->timeline_json;
            $timeline[] = [
                'time' => now()->toIso8601String(),
                'status' => 'RESPONSE',
                'title' => 'Ambulans NU diberangkatkan',
            ];
            $incident->timeline_json = $timeline;
            $incident->save();
            
            // Tambahkan Ambulans
            \App\Models\OperationalObject::create([
                'id' => 'ASSET-AMB-01',
                'object_type' => 'asset',
                'status' => 'IN_USE',
                'title' => 'Ambulans PCNU Kudus',
                'summary' => 'Sedang menuju lokasi.',
                'latitude' => -6.8150,
                'longitude' => 110.8400,
                'icon' => 'local_shipping',
                'color' => '#EAB308', // Yellow
                'priority' => 100, // Top priority
                'popup_json' => [
                    'header' => 'Ambulans PCNU Kudus',
                    'summary' => 'Sedang menuju lokasi.',
                    'status' => 'IN_USE'
                ],
                'timeline_json' => [],
            ]);
        }
        
        $this->info('Simulasi Selesai. Silakan cek Flutter Map.');
    }
}
