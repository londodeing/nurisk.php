<?php

namespace App\Services;

class OperatorWorkQueueService
{
    public function getPendingQueue()
    {
        $queue = [];

        $sitreps = \App\Models\OperasiSitrep::latest('dibuat_pada')->take(3)->get();
        foreach ($sitreps as $s) {
            $age = $s->dibuat_pada ? \Carbon\Carbon::parse($s->dibuat_pada)->diffInHours() : 0;
            $queue[] = [
                'type' => 'Review Sitrep',
                'number' => 'SIT-' . $s->id_sitrep,
                'author' => 'Operator',
                'age_hours' => $age,
                'status' => 'Pending Review'
            ];
        }

        $assessments = \App\Models\AssessmentUtama::latest('dibuat_pada')->take(3)->get();
        foreach ($assessments as $a) {
            $age = $a->dibuat_pada ? \Carbon\Carbon::parse($a->dibuat_pada)->diffInHours() : 0;
            $queue[] = [
                'type' => 'Review Assessment',
                'number' => 'ASM-' . $a->id_assessment_utama,
                'author' => 'Operator',
                'age_hours' => $age,
                'status' => 'Pending Review'
            ];
        }

        return collect($queue)->map(function ($item) {
            $item['badge'] = $item['age_hours'] > 6 ? 'danger' : ($item['age_hours'] > 3 ? 'warning text-dark' : 'success');
            return $item;
        })->sortByDesc('age_hours')->take(10)->values()->toArray();
    }
}
